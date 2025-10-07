<?php
// app/models/Audit.php
class Audit {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Nagla-log ng isang action sa audit_log table.
     */
    public function logAction($userId, $username, $actionType, $status, $details = null) {
        $this->db->query(
            'INSERT INTO audit_log (user_id, username, action_type, status, details, ip_address) 
             VALUES (:user_id, :username, :action_type, :status, :details, :ip_address)'
        );
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':username', $username);
        $this->db->bind(':action_type', $actionType);
        $this->db->bind(':status', $status);
        $this->db->bind(':details', $details);
        $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        
        return $this->db->execute();
    }

    /**
     * Kinukuha ang lahat ng logs na may kaugnayan sa backup at restore.
     */
    public function getBackupRestoreLogs() {
        $this->db->query(
            "SELECT * FROM audit_log 
             WHERE action_type IN ('backup_created', 'restore_attempted') 
             ORDER BY timestamp DESC"
        );
        return $this->db->resultSet();
    }
}