<?php
// app/controllers/NotificationsController.php
class NotificationsController extends Controller
{
    private $burialModel;
    private $notifModel;
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) { header('Location: ' . URLROOT . '/auth/login'); exit; }

        $this->burialModel = $this->model('Burial');
        $this->notifModel  = $this->model('Notification');
        $this->userModel   = $this->model('User');

        // make sure EmailHelper class is available (safe even if already loaded)
        if (!class_exists('EmailHelper')) {
            $helperPath = APPROOT . '/helpers/Email.php';
            if (is_file($helperPath)) require_once $helperPath;
        }
    }

    /* ---------------------------------------------------------
     * LIGHT endpoints used by the UI
     * --------------------------------------------------------- */

    // GET /notifications/unreadCount
    public function unreadCount()
    {
        header('Content-Type: application/json');
        try {
            $uid = (int)($_SESSION['user']['id'] ?? 0);
            $cnt = $uid ? $this->notifModel->countUnread($uid) : 0;
            echo json_encode(['ok'=>true, 'unread'=>$cnt]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'unread'=>0]);
        }
    }

    // GET /notifications/feed/today   or  /notifications/feed/history
    public function feed($scope = 'today')
    {
        header('Content-Type: application/json');
        try {
            $uid   = (int)($_SESSION['user']['id'] ?? 0);
            $today = ($scope === 'today');
            $rows  = $uid ? $this->notifModel->fetchFeed($uid, $today) : [];
            $cnt   = $uid ? $this->notifModel->countUnread($uid) : 0;
            echo json_encode(['ok'=>true, 'unread'=>$cnt, 'rows'=>$rows]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'unread'=>0, 'rows'=>[]]);
        }
    }

    // POST /notifications/markRead
    public function markRead()
    {
        header('Content-Type: application/json');
        try {
            $uid = (int)($_SESSION['user']['id'] ?? 0);
            if ($uid) $this->notifModel->markAllRead($uid);
            echo json_encode(['ok'=>true]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false]);
        }
    }

    /* ---------------------------------------------------------
     * Poll-based seeding (no cron, no DB event)
     * GET /notifications/poll
     * - Seeds "30 days" & "expired today" (WHOLE-DAY logic)
     * - Idempotent notif rows + fanout once
     * - Email queue/status per recipient (notification_email_status)
     * - PLUS: kahit walang bagong na-seed, magse-send pa rin tayo
     *         ng mga PENDING emails mula sa status table.
     * --------------------------------------------------------- */
    public function poll()
    {
        header('Content-Type: application/json');

        $emailStats = [
            'admin_sent'       => 0,
            'admin_errors'     => [],
            'staff_sent'       => 0,
            'staff_errors'     => [],
            'interment_sent'   => 0,
            'interment_errors' => [],
        ];

        try {
            $created = 0;

            // ===== 30 days before expiry — ACTIVE only =====
            $soon = method_exists($this->burialModel, 'getExpiringWithinDays')
                ? $this->burialModel->getExpiringWithinDays(30)
                : [];

            foreach ($soon as $r) {
                $block = $r->block_title ?? '';
                $plot  = $r->plot_number ?? '';
                $grave = trim($block . ($plot !== '' ? " ({$plot})" : ''));
                $title = 'Rental expires in 30 days';
                $msg   = $r->expiry_date
                    ? "Grave {$grave} rental expires on " . date('M d, Y', strtotime($r->expiry_date)) . '.'
                    : "Grave {$grave} rental will expire soon.";

                $notifId = $this->notifModel->upsertNotification([
                    'kind'      => 'expiry_30',
                    'burial_id' => $r->burial_id,
                    'title'     => $title,
                    'message'   => $msg,
                    'severity'  => 'warning',
                    'due_date'  => substr((string)$r->expiry_date, 0, 10),
                ]);

                $needsFanout = $notifId ? ($this->notifModel->needsFanout($notifId) ?? true) : false;
                if ($notifId && $needsFanout) {
                    $this->fanoutUsers($notifId);
                    $created++;
                }

                if ($notifId) {
                    $this->queueEmailRows($notifId, $r, 'expiry_30', $title, $msg);
                    $st = $this->sendPendingEmails($notifId, $r, 'expiry_30', $title, $msg);
                    $this->mergeStats($emailStats, $st);
                }
            }

            // ===== Expired today — ACTIVE only =====
            $todayRows = method_exists($this->burialModel, 'getExpiredToday')
                ? $this->burialModel->getExpiredToday()
                : [];

            foreach ($todayRows as $r) {
                $block = $r->block_title ?? '';
                $plot  = $r->plot_number ?? '';
                $grave = trim($block . ($plot !== '' ? " ({$plot})" : ''));
                $title = 'Rental expired today';
                $msg   = "Grave {$grave} rental expired today.";

                $notifId = $this->notifModel->upsertNotification([
                    'kind'      => 'expiry_today',
                    'burial_id' => $r->burial_id,
                    'title'     => $title,
                    'message'   => $msg,
                    'severity'  => 'danger',
                    'due_date'  => substr((string)$r->expiry_date, 0, 10),
                ]);

                $needsFanout = $notifId ? ($this->notifModel->needsFanout($notifId) ?? true) : false;
                if ($notifId && $needsFanout) {
                    $this->fanoutUsers($notifId);
                    $created++;
                }

                if ($notifId) {
                    $this->queueEmailRows($notifId, $r, 'expired_today', $title, $msg);
                    $st = $this->sendPendingEmails($notifId, $r, 'expired_today', $title, $msg);
                    $this->mergeStats($emailStats, $st);
                }
            }

            /* Send pending emails even if walang na-loop sa itaas */
            $pendingNotifIds = $this->notifModel->getNotifIdsWithPendingEmails(['expiry_30','expired_today']);
            if (!empty($pendingNotifIds)) {
                foreach ($pendingNotifIds as $nid) {
                    $ctx = $this->notifModel->getNotificationContext((int)$nid);
                    if (!$ctx) continue;

                    $subject = $ctx->title ?? 'Notification';
                    $line    = $ctx->message ?? '';
                    $kind    = $ctx->kind ?? 'expiry_30';

                    $st = $this->sendPendingEmails((int)$nid, $ctx, $kind, $subject, $line);
                    $this->mergeStats($emailStats, $st);
                }
            }

            // unread for current user (update badge)
            $uid = (int)($_SESSION['user']['id'] ?? 0);
            $cnt = $uid ? $this->notifModel->countUnread($uid) : 0;

            echo json_encode(['ok'=>true, 'created'=>$created, 'unread'=>$cnt, 'email_stats'=>$emailStats]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'created'=>0, 'unread'=>0, 'err'=>$e->getMessage(), 'email_stats'=>$emailStats]);
        }
    }

    /* ---------------------------------------------------------
     * Helpers (fanout + email queue/send)
     * --------------------------------------------------------- */

    private function fanoutUsers(int $notifId): void
    {
        try {
            if ($notifId > 0) {
                $userIds = $this->userModel->getAllActiveUserIds(); // int[]
                if (is_array($userIds) && $userIds) {
                    $this->notifModel->fanoutToUsers($notifId, $userIds); // INSERT IGNORE avoids dupes
                }
            }
        } catch (Throwable $e) { /* ignore */ }
    }

    private function queueEmailRows(int $notifId, $r, string $kind, string $subject, string $line): void
    {
        $admins = method_exists($this->userModel, 'getAdminsEmails')
            ? $this->userModel->getAdminsEmails()
            : [];

        // staff emails (optional method on your User model)
        $staffs = method_exists($this->userModel, 'getStaffEmails')
            ? $this->userModel->getStaffEmails()
            : []; // if empty, model will fallback to users table

        $intermentEmail = !empty($r->interment_email) ? trim((string)$r->interment_email) : null;

        $this->notifModel->ensureEmailStatusRows($notifId, $admins, $intermentEmail, $staffs);
    }

    private function sendPendingEmails(int $notifId, $r, string $kind, string $subject, string $line): array
    {
        $stats = [
            'admin_sent'=>0,'admin_errors'=>[],
            'staff_sent'=>0,'staff_errors'=>[],
            'interment_sent'=>0,'interment_errors'=>[]
        ];

        try {
            $email = new EmailHelper(); // your Gmail SMTP

            $cemName    = 'Plaridel Public Cemetery';
            $graveLabel = trim(($r->block_title ?? '') . (($r->plot_number ?? '') !== '' ? ' ('.$r->plot_number.')' : ''));
            $expiryDate = !empty($r->expiry_date) ? date('F d, Y', strtotime($r->expiry_date)) : null;
            $manageUrl  = URLROOT . '/admin/burialRecords';

            $vars = [
                'subject'     => $subject,
                'cemName'     => $cemName,
                'graveLabel'  => $graveLabel,
                'expiryDate'  => $expiryDate,
                'messageLine' => $line,
                'manageUrl'   => $manageUrl,
            ];

            $pending = $this->notifModel->getPendingEmailStatuses($notifId);
            if (!$pending) return $stats;

            foreach ($pending as $row) {
                $recipientType  = $row->recipient_type;     // 'admin' | 'staff' | 'interment'
                $recipientEmail = $row->recipient_email;

                // ======== NEW: resolve full name for Admin/Staff by email ========
                if ($recipientType === 'interment') {
                    $recipientName = ($r->interment_full_name ?: 'Interment Right Holder');
                } else {
                    $recipientName = $this->resolveUserNameByEmail($recipientEmail);
                    if (!$recipientName) {
                        $recipientName = ($recipientType === 'admin') ? 'Admin' : 'Staff';
                    }
                }
                // =================================================================

                $body = $this->renderEmailTemplate(
                    $kind === 'expiry_30' ? 'expiry_30' : 'expired_today',
                    array_merge($vars, [
                        'recipientName' => $recipientName,
                        'isAdmin'       => ($recipientType !== 'interment'),
                    ])
                );

                $ok = $email->sendEmail($recipientEmail, $recipientName, $subject, $body);

                // Mark attempt
                $this->notifModel->markEmailAttempt((int)$row->id, $ok === true, is_string($ok) ? $ok : null);

                // Tally
                if ($recipientType === 'admin') {
                    if ($ok === true) $stats['admin_sent']++;
                    else $stats['admin_errors'][] = 'admin:' . $recipientEmail;
                } elseif ($recipientType === 'staff') {
                    if ($ok === true) $stats['staff_sent']++;
                    else $stats['staff_errors'][] = 'staff:' . $recipientEmail;
                } else {
                    if ($ok === true) $stats['interment_sent']++;
                    else $stats['interment_errors'][] = 'interment:' . $recipientEmail;
                }
            }
        } catch (Throwable $e) {
            // swallow
        }

        return $stats;
    }

    private function mergeStats(array &$agg, array $inc): void
    {
        $agg['admin_sent']       += (int)($inc['admin_sent'] ?? 0);
        $agg['interment_sent']   += (int)($inc['interment_sent'] ?? 0);
        $agg['staff_sent']       += (int)($inc['staff_sent'] ?? 0);

        if (!empty($inc['admin_errors']))     $agg['admin_errors']     = array_merge($agg['admin_errors'], (array)$inc['admin_errors']);
        if (!empty($inc['interment_errors'])) $agg['interment_errors'] = array_merge($agg['interment_errors'], (array)$inc['interment_errors']);
        if (!empty($inc['staff_errors']))     $agg['staff_errors']     = array_merge($agg['staff_errors'], (array)$inc['staff_errors']);
    }

    private function renderEmailTemplate(string $view, array $vars = []): string
    {
        $file = APPROOT . '/views/emails/' . $view . '.php';
        if (!is_file($file)) {
            return nl2br((string)($vars['messageLine'] ?? ''));
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /* ---------------------------------------------------------
     * Name resolver for Admin/Staff by email (no DB schema change)
     * Tries multiple common User model methods/fields, falls back to email local-part.
     * --------------------------------------------------------- */
    private function resolveUserNameByEmail(string $email): ?string
    {
        $email = trim((string)$email);
        if ($email === '') return null;

        $candidates = [
            'getUserByEmail',
            'findByEmail',
            'findActiveByEmail',
            'getByEmail',
            'findUserByEmail',
        ];

        foreach ($candidates as $m) {
            if (method_exists($this->userModel, $m)) {
                try {
                    $u = $this->userModel->{$m}($email);
                    if ($u) {
                        // support array or object
                        $fn = is_array($u) ? ($u['first_name'] ?? $u['firstname'] ?? null)
                                           : ($u->first_name  ?? $u->firstname  ?? null);
                        $ln = is_array($u) ? ($u['last_name']  ?? $u['lastname']  ?? null)
                                           : ($u->last_name   ?? $u->lastname   ?? null);
                        $nm = is_array($u) ? ($u['name'] ?? $u['full_name'] ?? null)
                                           : ($u->name  ?? $u->full_name  ?? null);

                        if ($fn || $ln) {
                            return trim(($fn ?: '') . ' ' . ($ln ?: '')) ?: null;
                        }
                        if ($nm) return trim((string)$nm) ?: null;
                    }
                } catch (Throwable $e) { /* ignore and try next */ }
            }
        }

        // last resort: format local-part of email
        $local = strtolower(substr($email, 0, strpos($email, '@') ?: strlen($email)));
        $local = preg_replace('/[._]+/', ' ', $local);
        $local = ucwords($local);
        return $local ?: null;
    }
}
