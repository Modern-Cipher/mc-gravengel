<?php
class AuthController extends Controller
{
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() { return $this->login(); }

    public function login() {
        if (!empty($_SESSION['user'])) {
            header('Location: ' . $this->roleUrl($_SESSION['user']['role']));
            exit;
        }
        $this->view('users/login', ['title' => 'Login · Gravengel']);
    }

    public function doLogin() {
        header('Content-Type: application/json');
        $id = trim($_POST['identifier'] ?? '');
        $pw = (string)($_POST['password'] ?? '');

        if (empty($id) || empty($pw)) {
            echo json_encode(['ok' => false, 'msg' => 'Username and password are required.']); return;
        }

        $user = $this->userModel->findByUsernameOrEmail($id);

        if ($user && password_verify($pw, $user->password_hash) && (int)$user->is_active === 1) {
            session_regenerate_id(true);
            $currentSessionId = session_id();

            $_SESSION['user'] = [
                'id' => (int)$user->id, 'role' => $user->role,
                'name' => trim($user->first_name . ' ' . $user->last_name),
                'must_change_pwd' => (int)$user->must_change_pwd,
                'session_id' => $currentSessionId
            ];

            $this->userModel->openSession((int)$user->id, $currentSessionId, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '');

            echo json_encode(['ok' => true, 'redirect' => $this->roleUrl($user->role)]);
        } else {
            http_response_code(401);
            echo json_encode(['ok' => false, 'msg' => 'Invalid credentials.']);
        }
    }

    public function logout() {
        if (!empty($_SESSION['user']['session_id'])) {
            $this->userModel->closeSession($_SESSION['user']['session_id']);
        }
        $_SESSION = [];
        session_destroy();
        header('Location: ' . URLROOT . '/auth/login');
        exit;
    }
    
    public function forgot() {
        $this->view('users/forgot', ['title' => 'Forgot Password · Gravengel']);
    }

    // NEW: Handle forgot password form submission
    public function requestReset() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'Invalid request method.']); return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $user = $this->userModel->getUserByEmail($email);

        if (!$user) {
            echo json_encode(['ok' => true, 'msg' => 'If an account exists with that email, a password reset link has been sent.']);
            return;
        }

        $token = $this->userModel->createPasswordResetToken($user->id);
        $reset_link = URLROOT . '/auth/resetPassword?token=' . $token;

        $emailHelper = new EmailHelper();
        $email_data = ['full_name' => $user->first_name . ' ' . $user->last_name, 'reset_link' => $reset_link];
        $email_body = $this->view('emails/reset_password', $email_data, true);

        if ($emailHelper->sendEmail($user->email, $email_data['full_name'], 'Password Reset Request', $email_body)) {
             echo json_encode(['ok' => true, 'msg' => 'Password reset link has been sent to your email.']);
        } else {
             echo json_encode(['ok' => false, 'msg' => 'Failed to send reset link. Please try again later.']);
        }
    }
    
    // NEW: Handles the reset password form
    public function resetPassword() {
        $token = $_GET['token'] ?? '';
        $user = $this->userModel->validatePasswordResetToken($token);

        if (!$user) {
            die('Invalid or expired password reset token.');
        }

        $data = ['title' => 'Reset Password · Gravengel', 'user' => $user, 'token' => $token];
        $this->view('users/reset_password', $data);
    }
    
    // NEW: Handles the new password submission
    public function doResetPassword() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'msg' => 'Invalid request.']); return;
        }

        $token = $_POST['token'] ?? '';
        $user_id = $_POST['user_id'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['ok' => false, 'msg' => 'Passwords do not match.']); return;
        }
        
        $user = $this->userModel->validatePasswordResetToken($token, $user_id);
        if (!$user) {
            echo json_encode(['ok' => false, 'msg' => 'Invalid or expired token.']); return;
        }
        
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        if ($this->userModel->resetPassword($user_id, $new_password_hash)) {
            $this->userModel->markTokenAsUsed($token);
            echo json_encode(['ok' => true, 'msg' => 'Password reset successfully! You can now log in.']);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Failed to reset password.']);
        }
    }

    public function checkSession() {
        header('Content-Type: application/json');
        if (empty($_SESSION['user']['id'])) {
            echo json_encode(['is_valid' => false]); die();
        }
        $dbSession = $this->userModel->getActiveSessionId($_SESSION['user']['id']);
        if ($dbSession !== $_SESSION['user']['session_id']) {
            echo json_encode(['is_valid' => false]);
        } else {
            echo json_encode(['is_valid' => true]);
        }
        die();
    }
    
    public function force_change() {
        if (empty($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/auth/login'); exit;
        }
        $this->view('users/force_change', ['title' => 'Change Password · Gravengel']);
    }

    private function roleUrl($role) {
        if ($role === 'admin') return URLROOT . '/admin/dashboard';
        if ($role === 'staff') return URLROOT . '/staff/dashboard';
        return URLROOT . '/auth/login';
    }
}