<?php
class AuthController extends Controller
{
    private $userModel;
    private $activityLogModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->activityLogModel = $this->model('ActivityLog');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Remember-me cookie constants
        if (!defined('REMEMBER_COOKIE_NAME')) define('REMEMBER_COOKIE_NAME', 'grv_remember');
        if (!defined('REMEMBER_COOKIE_DAYS')) define('REMEMBER_COOKIE_DAYS', 30);
    }

    public function index() { return $this->login(); }

    public function login() {
        // Already logged in? Go to role dashboard
        if (!empty($_SESSION['user'])) {
            header('Location: ' . $this->roleUrl($_SESSION['user']['role']));
            exit;
        }

        // Try cookie auto-login (selector|validator)
        if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $cookie = $_COOKIE[REMEMBER_COOKIE_NAME];
            $parts  = explode(':', $cookie, 2);
            if (count($parts) === 2) {
                [$selector, $validator] = $parts;

                // 1) Find token row
                $tokenRow = $this->userModel->findRememberTokenBySelector($selector);
                // 2) Validate token
                if ($tokenRow
                    && hash_equals($tokenRow->validator_hash ?? '', hash('sha256', $validator))
                    && strtotime($tokenRow->expires_at ?? '1970-01-01') > time()) {

                    // 3) Load the user referenced by token
                    $u = $this->userModel->getUserById((int)$tokenRow->user_id);
                    if ($u && (int)$u->is_active === 1) {
                        // 4) Bootstrap session
                        session_regenerate_id(true);
                        $_SESSION['user'] = [
                            'id' => (int)$u->id,
                            'role' => $u->role,
                            'name' => trim($u->first_name . ' ' . $u->last_name),
                            'must_change_pwd' => (int)$u->must_change_pwd,
                            'session_id' => session_id()
                        ];
                        $this->userModel->openSession(
                            (int)$u->id,
                            session_id(),
                            $_SERVER['REMOTE_ADDR'] ?? '',
                            $_SERVER['HTTP_USER_AGENT'] ?? ''
                        );

                        header('Location: ' . $this->roleUrl($u->role));
                        exit;
                    }
                }
            }
            // invalid/expired cookie -> clear
            $this->clearRememberMeCookie();
        }

        $this->view('users/login', ['title' => 'Login · Gravengel']);
    }

public function doLogin() {
    header('Content-Type: application/json');

    // 0) Method check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['ok' => false, 'msg' => 'Invalid request.']); return;
    }

    // 1) Inputs
    $id = trim($_POST['identifier'] ?? '');
    $pw = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']) && $_POST['remember_me'] === '1';

    if ($id === '' || $pw === '') {
        echo json_encode(['ok' => false, 'msg' => 'Username and password are required.']); return;
    }

    // 2) Lookup
    $user = $this->userModel->findByUsernameOrEmail($id);
    if (!$user) {
        $this->activityLogModel->log(null, 'unknown', 'login_failed', "Attempted login with unknown user: " . htmlspecialchars($id));
        http_response_code(401);
        echo json_encode(['ok' => false, 'msg' => 'Invalid credentials or account is inactive.']);
        return;
    }

    // 3) Verify creds + active
    if (!password_verify($pw, $user->password_hash) || (int)$user->is_active !== 1) {
        $this->activityLogModel->log($user->id, $user->username, 'login_failed', 'Invalid password or inactive account.');
        http_response_code(401);
        echo json_encode(['ok' => false, 'msg' => 'Invalid credentials or account is inactive.']);
        return;
    }

    /* 3.5) STRICT SINGLE-SESSION GUARD
       - If may existing active session si user, i-block ang bagong login.
       - Mag-send ng email notification sa owner na may nag-attempt habang active.
       NOTE: Requires User::countActiveSessions($userId). */
    $activeCount = 0;
    if (method_exists($this->userModel, 'countActiveSessions')) {
        $activeCount = (int)$this->userModel->countActiveSessions((int)$user->id);
    }
    if ($activeCount > 0) {
        // Log the blocked attempt
        $this->activityLogModel->log($user->id, $user->username, 'login_blocked_active_session', 'Blocked due to an existing active session.');

        // Notify user via email WITH RESET LINK (non-fatal if fails)
        try {
            $ip  = $_SERVER['REMOTE_ADDR']     ?? 'unknown';
            $ua  = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $now = date('Y-m-d H:i:s');

            // Create a one-time password reset token & clickable link
            $token      = $this->userModel->createPasswordResetToken((int)$user->id);
            $reset_link = URLROOT . '/auth/resetPassword?token=' . $token;

            $emailHelper = new EmailHelper();
            $email_data = [
                'full_name'  => trim($user->first_name . ' ' . $user->last_name),
                'reset_link' => $reset_link, // IMPORTANT
                'message'    => "We blocked a login attempt to your account because there is already an active session.\n\n"
                              . "Time: {$now}\nIP: {$ip}\nDevice: {$ua}\n\n"
                              . "If this wasn't you, please change your password immediately using the button below.\n\n"
                              . "If the button doesn't work, copy and paste this link into your browser:\n{$reset_link}"
            ];
            $email_body = $this->view('emails/universal_email', $email_data, true);
            $emailHelper->sendEmail($user->email, $email_data['full_name'], 'Login Attempt Blocked (Active Session)', $email_body);
        } catch (\Throwable $e) {
            // ignore email errors
        }

        http_response_code(423); // Locked
        echo json_encode([
            'ok'  => false,
            'msg' => 'Login blocked. This account is already active on another device.'
        ]);
        return;
    }

    // 4) Success: start session (no other active sessions found)
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'              => (int)$user->id,
        'role'            => $user->role,
        'name'            => trim($user->first_name . ' ' . $user->last_name),
        'must_change_pwd' => (int)$user->must_change_pwd,
        'session_id'      => session_id()
    ];
    $this->userModel->openSession(
        (int)$user->id,
        session_id(),
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    );

    // 5) Remember-me (only on success)
    if ($remember) {
        $this->setRememberMeCookie((int)$user->id);
    } else {
        $this->clearRememberMeCookie();
    }

    // 6) Activity log
    $this->activityLogModel->log($user->id, $user->username, 'login');

    // 7) (Optional) Close other sessions (kept for safety; should be 0 because we blocked above)
    $closed = 0;
    if (method_exists($this->userModel, 'closeOtherSessions')) {
        $closed = (int)$this->userModel->closeOtherSessions((int)$user->id, session_id());
    }

    // 8) Build response flags (frontend SweetAlerts handle prompts)
    echo json_encode([
        'ok'                  => true,
        'prompt_change_pwd'   => ((int)$user->must_change_pwd === 1),
        'profile_redirect'    => ($user->role === 'admin') ? URLROOT . '/admin/profile' : URLROOT . '/staff/profile',
        'redirect_dashboard'  => $this->roleUrl($user->role),
        'closed_sessions'     => $closed
    ]);
    return;
}


    public function logout() {
        if (!empty($_SESSION['user'])) {
            $this->activityLogModel->log($_SESSION['user']['id'], $_SESSION['user']['name'], 'logout');
            $this->userModel->closeSession($_SESSION['user']['session_id']);
        }
        $this->clearRememberMeCookie();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . URLROOT . '/auth/login');
        exit;
    }

    public function forgot() {
        $this->view('users/forgot', ['title' => 'Forgot Password · Gravengel']);
    }

    public function requestReset() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'Invalid request method.']); return;
        }

        $email = trim($_POST['email'] ?? '');
        $user = $this->userModel->getUserByEmail($email);

        // same generic response to prevent user enumeration
        if (!$user) {
            echo json_encode(['ok' => true, 'msg' => 'If an account with that email exists, a password reset link has been sent.']);
            return;
        }

        $token = $this->userModel->createPasswordResetToken($user->id);
        $reset_link = URLROOT . '/auth/resetPassword?token=' . $token;

        $emailHelper = new EmailHelper();
        $email_data = [
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'reset_link' => $reset_link,
            'message' => 'We received a request to reset the password for your account. Click the button below to continue.'
        ];

        $email_body = $this->view('emails/universal_email', $email_data, true);

        if ($emailHelper->sendEmail($user->email, $email_data['full_name'], 'Password Reset Request', $email_body)) {
            echo json_encode(['ok' => true, 'msg' => 'A password reset link has been sent to your email.']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Failed to send reset link. Please try again later.']);
        }
    }

    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        if (empty($token)) { die('Invalid password reset link: Token is missing.'); }

        $user = $this->userModel->validatePasswordResetToken($token);
        if (!$user) { die('Invalid or expired password reset token. Please request a new one.'); }

        $data = [
            'title' => 'Reset Your Password',
            'token' => $token,
            'user' => $user,
            'full_name' => trim($user->first_name . ' ' . $user->last_name)
        ];

        $this->view('users/universal_reset_form', $data);
    }

 public function doResetPassword() {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['ok' => false, 'msg' => 'Invalid request.']); return;
    }

    $token = $_POST['token'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        echo json_encode(['ok' => false, 'msg' => 'Passwords do not match.']); return;
    }
    if (strlen($new_password) < 6) {
        echo json_encode(['ok' => false, 'msg' => 'Password must be at least 6 characters.']); return;
    }

    $user = $this->userModel->validatePasswordResetToken($token, $user_id);
    if (!$user) {
        echo json_encode(['ok' => false, 'msg' => 'Invalid or expired token.']); return;
    }

    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // --- Apply the new password ---
    if ($this->userModel->resetPassword($user_id, $new_password_hash)) {

        // (A) mark token used
        $this->userModel->markTokenAsUsed($token);

        // (B) invalidate ALL other sessions for this user (so strict guard won’t block you next login)
        if (method_exists($this->userModel, 'closeAllSessions')) {
            $this->userModel->closeAllSessions($user_id);
        }

        // (C) delete any remember-me tokens for safety
        if (method_exists($this->userModel, 'deleteRememberTokensForUser')) {
            $this->userModel->deleteRememberTokensForUser($user_id);
        }

        // (D) optional: user no longer “must change pwd”
        if (property_exists($user, 'must_change_pwd') && (int)$user->must_change_pwd === 1) {
            if (method_exists($this->userModel, 'clearMustChangePwd')) {
                $this->userModel->clearMustChangePwd($user_id);
            }
        }

        echo json_encode(['ok' => true, 'msg' => 'Password reset successfully! You can now log in.']);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Failed to reset password.']);
    }
}


    /* ------------------------ Helpers ------------------------ */

    private function roleUrl($role) {
        if ($role === 'admin') return URLROOT . '/admin/dashboard';
        if ($role === 'staff') return URLROOT . '/staff/dashboard';
        return URLROOT . '/auth/login';
    }

    private function profileUrl($role) {
        if ($role === 'admin') return URLROOT . '/admin/profile';
        if ($role === 'staff') return URLROOT . '/staff/profile';
        return URLROOT . '/auth/login';
    }

    private function setRememberMeCookie(int $userId): void {
        // Optionally prune old tokens
        if (method_exists($this->userModel, 'deleteExpiredRememberTokens')) {
            $this->userModel->deleteExpiredRememberTokens();
        }

        // Create selector + validator
        $selector  = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
        $validator = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $expiresAt = (new DateTime())->modify('+' . REMEMBER_COOKIE_DAYS . ' days')->format('Y-m-d H:i:s');

        // Persist hashed validator
        $this->userModel->createRememberToken([
            'user_id'        => $userId,
            'selector'       => $selector,
            'validator_hash' => hash('sha256', $validator),
            'expires_at'     => $expiresAt,
            'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        // Cookie: selector|validator (raw validator only in cookie)
        $value = $selector . ':' . $validator;

        setcookie(
            REMEMBER_COOKIE_NAME,
            $value,
            [
                'expires'  => time() + (60 * 60 * 24 * REMEMBER_COOKIE_DAYS),
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function clearRememberMeCookie(): void {
        if (!empty($_COOKIE[REMEMBER_COOKIE_NAME])) {
            $parts = explode(':', $_COOKIE[REMEMBER_COOKIE_NAME], 2);
            if (count($parts) === 2) {
                // try to also delete from DB (no user scope here)
                if (method_exists($this->userModel, 'deleteRememberTokenBySelector')) {
                    $this->userModel->deleteRememberTokenBySelector($parts[0], null);
                }
            }
        }
        setcookie(REMEMBER_COOKIE_NAME, '', time() - 3600, '/');
        unset($_COOKIE[REMEMBER_COOKIE_NAME]);
    }
}
