<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }
    
    public function findByUsernameOrEmail($identifier) {
        $this->db->query('SELECT * FROM users WHERE username = :identifier OR email = :identifier LIMIT 1');
        $this->db->bind(':identifier', $identifier);
        return $this->db->single();
    }

    public function findById($id) {
        $this->db->query('SELECT u.*, sd.staff_id, sd.designation 
                         FROM users u 
                         LEFT JOIN staff_details sd ON u.id = sd.user_id 
                         WHERE u.id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function getAllUsersExcluding($userId) {
        $this->db->query('SELECT u.*, sd.staff_id, sd.designation FROM users AS u LEFT JOIN staff_details AS sd ON u.id = sd.user_id WHERE u.id != :id ORDER BY u.created_at DESC');
        $this->db->bind(':id', $userId);
        return $this->db->resultSet();
    }
    
    public function addStaffUser($data) {
        try {
            $this->db->query('INSERT INTO users (first_name, last_name, username, email, password_hash, role, phone, must_change_pwd) VALUES (:first_name, :last_name, :username, :email, :password_hash, "staff", :phone, :must_change_pwd)');
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password_hash', $data['password_hash']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':must_change_pwd', $data['must_change_pwd']);

            if (!$this->db->execute()) {
                return false;
            }

            $user_id = $this->db->lastInsertId();

            $this->db->query('INSERT INTO staff_details (user_id, staff_id, designation) VALUES (:user_id, :staff_id, :designation)');
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':staff_id', $data['staff_id']);
            $this->db->bind(':designation', $data['designation']);

            if (!$this->db->execute()) {
                return false;
            }
            
            return $user_id;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updateProfile($data) {
        try {
            $this->db->query('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address WHERE id = :id');
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':address', $data['address']);
            $this->db->execute();
            
            $this->db->query('UPDATE staff_details SET staff_id = :staff_id, designation = :designation WHERE user_id = :id');
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':staff_id', $data['staff_id']);
            $this->db->bind(':designation', $data['designation']);
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePassword($data) {
        $this->db->query('SELECT password_hash FROM users WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $row = $this->db->single();

        if ($row && password_verify($data['current_password'], $row->password_hash)) {
            $this->db->query('UPDATE users SET password_hash = :password_hash, must_change_pwd = 0 WHERE id = :id');
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':password_hash', $data['new_password_hash']);
            return $this->db->execute();
        } else {
            return false;
        }
    }
    
    public function updateProfileImage($id, $imagePath) {
        $this->db->query('UPDATE users SET profile_image = :profile_image WHERE id = :id');
        $this->db->bind(':id', $id);
        $this->db->bind(':profile_image', $imagePath);
        return $this->db->execute();
    }
    
    public function openSession($userId, $sessionId, $ip, $userAgent) {
        $this->db->query("UPDATE user_sessions SET is_active = 0 WHERE user_id = :user_id AND is_active = 1");
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
        $this->db->query("INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) VALUES (:user_id, :session_id, :ip_address, :user_agent)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':session_id', $sessionId);
        $this->db->bind(':ip_address', $ip);
        $this->db->bind(':user_agent', $userAgent);
        $this->db->execute();
        $this->db->query("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }

    public function closeSession($sessionId) {
        $this->db->query("UPDATE user_sessions SET logout_at = NOW(), is_active = 0 WHERE session_id = :session_id AND is_active = 1");
        $this->db->bind(':session_id', $sessionId);
        return $this->db->execute();
    }
    
    public function getActiveSessionId($userId) {
        $this->db->query("SELECT session_id FROM user_sessions WHERE user_id = :user_id AND is_active = 1 ORDER BY login_at DESC LIMIT 1");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        return $row ? $row->session_id : null;
    }
    
    // NEW: Get user by email
    public function getUserByEmail($email) {
        $this->db->query('SELECT id, first_name, last_name, email FROM users WHERE email = :email LIMIT 1');
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    // NEW: Create password reset token
    public function createPasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 day'));
        $this->db->query('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':token', $token);
        $this->db->bind(':expires_at', $expires);
        if ($this->db->execute()) {
            return $token;
        }
        return false;
    }

    // NEW: Validate password reset token
    public function validatePasswordResetToken($token, $userId = null) {
        $query = 'SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL AND expires_at >= NOW()';
        if ($userId) {
            $query .= ' AND user_id = :user_id';
        }
        $this->db->query($query);
        $this->db->bind(':token', $token);
        if ($userId) {
            $this->db->bind(':user_id', $userId);
        }
        $row = $this->db->single();
        if ($row) {
            $this->db->query('SELECT * FROM users WHERE id = :id');
            $this->db->bind(':id', $row->user_id);
            return $this->db->single();
        }
        return false;
    }

    // NEW: Reset password
    public function resetPassword($userId, $passwordHash) {
        $this->db->query('UPDATE users SET password_hash = :password_hash, must_change_pwd = 0 WHERE id = :user_id');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':password_hash', $passwordHash);
        return $this->db->execute();
    }

    // NEW: Mark token as used
    public function markTokenAsUsed($token) {
        $this->db->query('UPDATE password_resets SET used_at = NOW() WHERE token = :token');
        $this->db->bind(':token', $token);
        return $this->db->execute();
    }

    /**
 * Partially update a staff user.
 * - $changes may contain any of:
 *   first_name, last_name, username, email, phone, staff_id, designation
 * - Validates username/email/phone (same regex as front-end)
 * - Checks duplicate username/email (excluding this $id)
 * - Updates `users` table and upserts `staff_details`
 * Returns: bool
 */
public function updateStaffUser(int $id, array $changes): bool {
    if ($id <= 0) return false;

    // ---- Normalize incoming values (trim only for provided keys) ----
    $norm = function($v){ return trim((string)$v); };
    $fields = ['first_name','last_name','username','email','phone','staff_id','designation'];
    $c = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $changes)) {
            $c[$f] = $norm($changes[$f]);
        }
    }
    if (empty($c)) return true; // nothing to update

    // ---- Server-side validation (mirror ng front-end rules) ----
    if (isset($c['username']) && $c['username'] !== '' && !preg_match('/^[a-zA-Z0-9]+$/', $c['username'])) {
        return false;
    }
    if (isset($c['email']) && $c['email'] !== '' && !preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $c['email'])) {
        return false;
    }
    if (isset($c['phone']) && $c['phone'] !== '' && !preg_match('/^09\d{2} \d{3} \d{4}$/', $c['phone'])) {
        return false;
    }

    // ---- Duplicate checks (exclude current user id) ----
    if (isset($c['username']) && $c['username'] !== '') {
        $this->db->query('SELECT id FROM users WHERE username = :u AND id <> :id LIMIT 1');
        $this->db->bind(':u', $c['username']);
        $this->db->bind(':id', $id);
        if ($this->db->single()) return false;
    }
    if (isset($c['email']) && $c['email'] !== '') {
        $this->db->query('SELECT id FROM users WHERE email = :e AND id <> :id LIMIT 1');
        $this->db->bind(':e', $c['email']);
        $this->db->bind(':id', $id);
        if ($this->db->single()) return false;
    }

    // ---- Split to users vs staff_details fields ----
    $userCols  = ['first_name','last_name','username','email','phone'];
    $staffCols = ['staff_id','designation'];

    // Build dynamic UPDATE for users
    $userSets = [];
    $binds    = [':id' => $id];
    foreach ($userCols as $col) {
        if (array_key_exists($col, $c)) {
            $userSets[] = "$col = :$col";
            $binds[":$col"] = $c[$col];
        }
    }

    if (!empty($userSets)) {
        $sql = 'UPDATE users SET '.implode(', ', $userSets).' WHERE id = :id';
        $this->db->query($sql);
        foreach ($binds as $k => $v) $this->db->bind($k, $v);
        if (!$this->db->execute()) return false;
    }

    // Upsert staff_details only if at least one staff field is provided
    $needStaff = false;
    $staffData = [];
    foreach ($staffCols as $col) {
        if (array_key_exists($col, $c)) { $needStaff = true; $staffData[$col] = $c[$col]; }
    }

    if ($needStaff) {
        // Check if staff_details row exists
        $this->db->query('SELECT id FROM staff_details WHERE user_id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        $row = $this->db->single();

        if ($row) {
            // UPDATE only provided fields
            $sets = [];
            foreach ($staffData as $k => $v) { $sets[] = "$k = :$k"; }
            $this->db->query('UPDATE staff_details SET '.implode(', ', $sets).' WHERE user_id = :id');
            foreach ($staffData as $k => $v) { $this->db->bind(":$k", $v); }
            $this->db->bind(':id', $id);
            if (!$this->db->execute()) return false;
        } else {
            // INSERT minimal row with provided fields
            // Ensure both columns exist in bind even if one is missing
            $this->db->query('INSERT INTO staff_details (user_id, staff_id, designation) VALUES (:user_id, :staff_id, :designation)');
            $this->db->bind(':user_id', $id);
            $this->db->bind(':staff_id',    $staffData['staff_id']    ?? null);
            $this->db->bind(':designation', $staffData['designation'] ?? null);
            if (!$this->db->execute()) return false;
        }
    }

    return true;
}

    public function countStaffUsers(): int {
        $this->db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'staff'");
        $r = $this->db->single();
        return (int)($r->c ?? 0);
    }

    // app/models/User.php (add these methods)

public function getAllActiveUserIds(): array {
    $this->db->query("SELECT id FROM users WHERE is_active = 1");
    $rows = $this->db->resultSet();
    return array_map(fn($r) => (int)$r->id, $rows ?: []);
}

public function getAdminsEmails(): array {
    $this->db->query("SELECT first_name AS name, email FROM users WHERE is_active = 1 AND role IN ('admin')");
    return $this->db->resultSet() ?: [];
}

// app/models/User.php (Add this method)
    public function toggleUserActiveStatus(int $id, int $is_active): bool
    {
        if ($id <= 0) return false;
        
        $this->db->query('UPDATE users SET is_active = :is_active WHERE id = :id');
        $this->db->bind(':id', $id);
        // Ensure the value is 0 or 1
        $this->db->bind(':is_active', $is_active > 0 ? 1 : 0); 
        
        return $this->db->execute();
    }

}