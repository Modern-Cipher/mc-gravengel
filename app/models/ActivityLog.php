<?php
// app/models/ActivityLog.php

class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Naglalagay ng bagong log entry sa activity_log table.
     */
    public function log($userId, $username, $action, $details = '') {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $this->db->query('INSERT INTO activity_log (user_id, username, action_type, details, ip_address) 
                         VALUES (:uid, :uname, :action, :details, :ip)');
        $this->db->bind(':uid', $userId);
        $this->db->bind(':uname', $username);
        $this->db->bind(':action', $action);
        $this->db->bind(':details', $details);
        $this->db->bind(':ip', $ip);
        return $this->db->execute();
    }

    /**
     * Kinukuha ang mga logs para sa logs & reports page.
     */
    public function getLogs($filters = []) {
        $from = $filters['from'] ?? '';
        $to   = $filters['to']   ?? '';
        $q    = trim($filters['q'] ?? '');

        $sql = "SELECT * FROM activity_log WHERE 1=1 ";
        
        $binds = [];

        if ($from !== '') { $sql .= " AND DATE(timestamp) >= :dfrom"; $binds[':dfrom'] = $from; }
        if ($to   !== '') { $sql .= " AND DATE(timestamp) <= :dto";   $binds[':dto']   = $to;   }

        if ($q !== '') {
            $sql .= " AND (username LIKE :q OR action_type LIKE :q OR details LIKE :q)";
            $binds[':q'] = "%{$q}%";
        }

        $sql .= " ORDER BY timestamp DESC";

        $this->db->query($sql);
        foreach ($binds as $key => $value) {
            $this->db->bind($key, $value);
        }
        return $this->db->resultSet();
    }
}