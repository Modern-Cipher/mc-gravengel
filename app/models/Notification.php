<?php
/**
 * app/models/Notification.php
 */
class Notification
{
    private $db;

    // Max email retries per recipient to avoid DB spam
    private int $maxEmailAttempts = 5;

    public function __construct()
    {
        $this->db = new Database;
    }

    /** Idempotent insert/update keyed by (kind, burial_id, due_date). */
    public function upsertNotification(array $n): int
    {
        try {
            $this->db->query("
                INSERT INTO notifications (burial_id, title, message, severity, due_date, kind)
                VALUES (:burial_id, :title, :message, :severity, :due_date, :kind)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    message = VALUES(message),
                    severity = VALUES(severity)
            ");
            $this->db->bind(':burial_id', $n['burial_id']);
            $this->db->bind(':title',     $n['title']);
            $this->db->bind(':message',   $n['message']);
            $this->db->bind(':severity',  $n['severity']);
            $this->db->bind(':due_date',  $n['due_date']); // YYYY-MM-DD
            $this->db->bind(':kind',      $n['kind']);
            $this->db->execute();

            $this->db->query("
                SELECT id
                FROM notifications
                WHERE kind = :kind AND burial_id = :burial_id AND due_date = :due
                LIMIT 1
            ");
            $this->db->bind(':kind',      $n['kind']);
            $this->db->bind(':burial_id', $n['burial_id']);
            $this->db->bind(':due',       $n['due_date']);
            $row = $this->db->single();
            return (int)($row->id ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /** Only fanout if no rows exist yet in notification_user for this notification. */
    public function needsFanout(int $notifId): bool
    {
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM notification_user WHERE notification_id = :nid");
            $this->db->bind(':nid', $notifId);
            $row = $this->db->single();
            return ((int)($row->c ?? 0)) === 0;
        } catch (Throwable $e) {
            return true;
        }
    }

    /** Fanout to many users. Uses INSERT IGNORE to avoid duplicates. */
    public function fanoutToUsers(int $notifId, array $userIds): void
    {
        if ($notifId <= 0 || empty($userIds)) return;

        // Filter to ints and unique
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        if (empty($userIds)) return;

        $values = [];
        foreach ($userIds as $uid) {
            if ($uid > 0) $values[] = '(' . $notifId . ',' . $uid . ')';
        }
        if (empty($values)) return;

        try {
            $sql = "INSERT IGNORE INTO notification_user (notification_id, user_id) VALUES " . implode(',', $values);
            $this->db->query($sql);
            $this->db->execute();
        } catch (Throwable $e) {
            // swallow
        }
    }

    /* ===================== EMAIL STATUS LAYER ===================== */

    /**
     * Ensure rows exist in notification_email_status for admins + staff + interment.
     * $admins / $staffs: array of ['email'=>..,'name'=>..] OR stdClass with ->email, ->name
     * INSERT IGNORE para walang dupes kahit paulit-ulit ang poll.
     */
    public function ensureEmailStatusRows(int $notifId, array $admins, ?string $intermentEmail, array $staffs = []): void
    {
        if ($notifId <= 0) return;

        // Helper to normalize array/object to email string
        $norm = function($x): string {
            if (is_array($x))    return trim((string)($x['email'] ?? ''));
            if (is_object($x))   return trim((string)($x->email ?? ''));
            return '';
        };

        try {
            // Admins
            if ($admins) {
                foreach ($admins as $a) {
                    $email = $norm($a);
                    if ($email === '') continue;
                    $this->db->query("
                        INSERT IGNORE INTO notification_email_status (notification_id, recipient_type, recipient_email)
                        VALUES (:nid, 'admin', :em)
                    ");
                    $this->db->bind(':nid', $notifId);
                    $this->db->bind(':em',  $email);
                    $this->db->execute();
                }
            }

            // Staffs (explicit list if provided)
            if (!empty($staffs)) {
                foreach ($staffs as $s) {
                    $email = $norm($s);
                    if ($email === '') continue;
                    $this->db->query("
                        INSERT IGNORE INTO notification_email_status (notification_id, recipient_type, recipient_email)
                        VALUES (:nid, 'staff', :em)
                    ");
                    $this->db->bind(':nid', $notifId);
                    $this->db->bind(':em',  $email);
                    $this->db->execute();
                }
            } else {
                // Fallback: auto-pull active staff emails from users table
                $this->db->query("
                    INSERT IGNORE INTO notification_email_status (notification_id, recipient_type, recipient_email)
                    SELECT :nid AS notification_id, 'staff' AS recipient_type, u.email
                    FROM users u
                    WHERE u.role = 'staff' AND u.is_active = 1 AND u.email <> ''
                ");
                $this->db->bind(':nid', $notifId);
                $this->db->execute();
            }

            // Interment
            if (!empty($intermentEmail)) {
                $this->db->query("
                    INSERT IGNORE INTO notification_email_status (notification_id, recipient_type, recipient_email)
                    VALUES (:nid, 'interment', :em)
                ");
                $this->db->bind(':nid', $notifId);
                $this->db->bind(':em',  trim((string)$intermentEmail));
                $this->db->execute();
            }
        } catch (Throwable $e) {
            // swallow
        }
    }

    /**
     * Return recipients with sent=0 and attempts < max (throttled).
     */
    public function getPendingEmailStatuses(int $notifId): array
    {
        try {
            $this->db->query("
                SELECT id, recipient_type, recipient_email, sent, attempts, last_attempt_at, last_error
                FROM notification_email_status
                WHERE notification_id = :nid
                  AND sent = 0
                  AND attempts < :maxa
                  AND (last_attempt_at IS NULL OR last_attempt_at < (NOW() - INTERVAL 60 SECOND))
                ORDER BY id ASC
                LIMIT 100
            ");
            $this->db->bind(':nid',  $notifId);
            $this->db->bind(':maxa', $this->maxEmailAttempts);
            return $this->db->resultSet();
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Update status (attempt + sent flag / last_error). */
    public function markEmailAttempt(int $statusId, bool $ok, ?string $err = null): void
    {
        try {
            if ($ok) {
                $this->db->query("
                    UPDATE notification_email_status
                    SET sent = 1,
                        attempts = attempts + 1,
                        last_attempt_at = NOW(),
                        last_error = NULL
                    WHERE id = :id
                ");
                $this->db->bind(':id', $statusId);
                $this->db->execute();
            } else {
                $this->db->query("
                    UPDATE notification_email_status
                    SET attempts = attempts + 1,
                        last_attempt_at = NOW(),
                        last_error = :err
                    WHERE id = :id
                ");
                $this->db->bind(':id',  $statusId);
                $this->db->bind(':err', (string)$err);
                $this->db->execute();
            }
        } catch (Throwable $e) {
            // swallow
        }
    }

    /**
     * For fallback sending (even if no new seeds on this poll):
     * get notif IDs that still have pending email rows.
     * $kinds e.g. ['expiry_30','expired_today']
     */
    public function getNotifIdsWithPendingEmails(array $kinds): array
    {
        if (empty($kinds)) return [];
        try {
            // build IN (:k0,:k1,...)
            $ins = [];
            foreach ($kinds as $i => $k) $ins[] = ':k' . $i;

            $sql = "
                SELECT DISTINCT nes.notification_id AS nid
                FROM notification_email_status nes
                JOIN notifications n ON n.id = nes.notification_id
                WHERE nes.sent = 0
                  AND nes.attempts < :maxa
                  AND n.kind IN (" . implode(',', $ins) . ")
                ORDER BY nid ASC
                LIMIT 200
            ";
            $this->db->query($sql);
            $this->db->bind(':maxa', $this->maxEmailAttempts);
            foreach ($kinds as $i => $k) {
                $this->db->bind(':k'.$i, $k);
            }
            $rows = $this->db->resultSet();
            if (!$rows) return [];
            return array_map(fn($r) => (int)$r->nid, $rows);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Pull full context for email templates:
     * - notif fields (title, message, kind, due_date)
     * - burial + plot + block to render grave label and recipient email/name
     */
    public function getNotificationContext(int $notifId)
    {
        try {
            $this->db->query("
                SELECT
                    n.id            AS notif_id,
                    n.title,
                    n.message,
                    n.kind,
                    n.due_date,
                    b.burial_id,
                    b.interment_full_name,
                    b.interment_email,
                    b.expiry_date,
                    p.plot_number,
                    mb.title       AS block_title
                FROM notifications n
                LEFT JOIN burials b     ON b.burial_id = n.burial_id
                LEFT JOIN plots p       ON p.id = b.plot_id
                LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
                WHERE n.id = :nid
                LIMIT 1
            ");
            $this->db->bind(':nid', $notifId);
            return $this->db->single();
        } catch (Throwable $e) {
            return null;
        }
    }

    /* =================== FEED / BADGE HELPERS =================== */

    public function fetchFeed(int $userId, bool $todayOnly): array
    {
        try {
            $filter = $todayOnly ? "DATE(n.created_at) = CURDATE()" : "1=1";
            $this->db->query("
                SELECT
                    nu.id AS link_id,
                    nu.is_read,
                    n.*
                FROM notification_user nu
                JOIN notifications n ON n.id = nu.notification_id
                WHERE nu.user_id = :uid AND $filter
                ORDER BY n.created_at DESC
                LIMIT 200
            ");
            $this->db->bind(':uid', $userId);
            return $this->db->resultSet();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function countUnread(int $userId): int
    {
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM notification_user WHERE user_id = :uid AND is_read = 0");
            $this->db->bind(':uid', $userId);
            $row = $this->db->single();
            return (int)($row->c ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }

    public function markAllRead(int $userId): void
    {
        try {
            $this->db->query("
                UPDATE notification_user
                SET is_read = 1, read_at = NOW()
                WHERE user_id = :uid AND is_read = 0
            ");
            $this->db->bind(':uid', $userId);
            $this->db->execute();
        } catch (Throwable $e) {
            // swallow
        }
    }
}
