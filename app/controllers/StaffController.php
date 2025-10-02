<?php
// app/controllers/StaffController.php
class StaffController extends Controller
{
    private $burialModel;
    private $userModel;
    private $db; // fallback when model helpers are missing

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) { header('Location: ' . URLROOT . '/auth/login'); exit; }

        // strictly allow staff (and optionally admin)
        $role = $_SESSION['user']['role'] ?? '';
        if ($role !== 'staff' && $role !== 'admin') {
            header('Location: ' . URLROOT . '/errors/403'); exit;
        }

        $this->burialModel = $this->model('Burial');
        $this->userModel   = $this->model('User');

        if (class_exists('Database')) $this->db = new Database;
    }

    /* =======================================================
     * HOME (dashboard)
     * ======================================================= */
    // GET /staff or /staff/index
    public function index()
    {
        $data = ['title' => 'Dashboard'];
        $this->view('staff/index', $data);
    }

    // GET /staff/dashboardCards  (counts for the 4 cards)
    public function dashboardCards()
    {
        header('Content-Type: application/json');
        try {
            // Active burials
            $active = method_exists($this->burialModel, 'countActive')
                ? (int)$this->burialModel->countActive()
                : $this->scalar("SELECT COUNT(*) c FROM burials WHERE is_active=1");

            // Expired rental (still marked active)
            $expired = method_exists($this->burialModel, 'countExpired')
                ? (int)$this->burialModel->countExpired()
                : $this->scalar("SELECT COUNT(*) c
                                 FROM burials
                                 WHERE is_active=1 AND expiry_date IS NOT NULL AND expiry_date < NOW()");

            // Today’s transactions (by rental_date date = today)
            $today = method_exists($this->burialModel, 'countTransactionsToday')
                ? (int)$this->burialModel->countTransactionsToday()
                : $this->scalar("SELECT COUNT(*) c FROM burials WHERE DATE(rental_date) = CURDATE()");

            // Staff accounts (active staff only)
            $staff = method_exists($this->userModel, 'countActiveStaff')
                ? (int)$this->userModel->countActiveStaff()
                : $this->scalar("SELECT COUNT(*) c FROM users WHERE role='staff' AND is_active=1");

            echo json_encode(['ok'=>true, 'active'=>$active, 'expired'=>$expired, 'today'=>$today, 'staff'=>$staff]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'active'=>0, 'expired'=>0, 'today'=>0, 'staff'=>0, 'err'=>$e->getMessage()]);
        }
    }

    // GET /staff/expiryEvents?from=YYYY-MM-DD&to=YYYY-MM-DD  (calendar)
    public function expiryEvents()
    {
        header('Content-Type: application/json');
        try {
            $from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
            $to   = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-t');

            if (method_exists($this->burialModel, 'getExpiryEventsRange')) {
                $rows = $this->burialModel->getExpiryEventsRange($from, $to);
            } else {
                $sql = "
                    SELECT 
                        b.burial_id,
                        b.interment_full_name,
                        b.expiry_date,
                        p.plot_number,
                        mb.title AS block_title
                    FROM burials b
                    LEFT JOIN plots p       ON p.id = b.plot_id
                    LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
                    WHERE b.is_active = 1
                      AND b.expiry_date IS NOT NULL
                      AND DATE(b.expiry_date) BETWEEN :f AND :t
                    ORDER BY b.expiry_date ASC
                ";
                $this->db->query($sql);
                $this->db->bind(':f', $from);
                $this->db->bind(':t', $to);
                $rows = $this->db->resultSet();
            }

            $events = [];
            if ($rows) {
                foreach ($rows as $r) {
                    $start  = date('c', strtotime($r->expiry_date));
                    $holder = trim((string)($r->interment_full_name ?? ''));
                    $block  = (string)($r->block_title ?? '');
                    $plot   = (string)($r->plot_number ?? '');
                    $grave  = trim($block . ($plot !== '' ? ' — ' . $plot : ''));

                    $events[] = [
                        'title' => 'Expiry',
                        'start' => $start,
                        'extendedProps' => [
                            'holder'    => $holder,
                            'burial_id' => (string)($r->burial_id ?? ''),
                            'block'     => $block,
                            'plot'      => $plot,
                            'grave'     => $grave,
                            'expiry'    => $start
                        ]
                    ];
                }
            }

            echo json_encode(['ok'=>true, 'events'=>$events]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'events'=>[], 'err'=>$e->getMessage()]);
        }
    }

    /* =======================================================
     * BURIAL RECORDS (list / archived)
     * ======================================================= */

    // GET /staff/burialRecords  (server-rendered table like admin)
    public function burialRecords()
    {
        // load same dataset admin uses
        if (method_exists($this->burialModel, 'getAllActiveWithPlot')) {
            $records = $this->burialModel->getAllActiveWithPlot();
        } else {
            $sql = "
                SELECT
                    b.*,
                    p.plot_number,
                    mb.title AS block_title
                FROM burials b
                LEFT JOIN plots p       ON p.id = b.plot_id
                LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
                WHERE b.is_active = 1
                ORDER BY b.created_at DESC
                LIMIT 2000
            ";
            $this->db->query($sql);
            $records = $this->db->resultSet();
        }

        $data = ['title' => 'Burial Records', 'records' => $records ?: []];
        $this->view('staff/burial_records', $data);
    }

    // GET /staff/archivedBurials
    public function archivedBurials()
    {
        if (method_exists($this->burialModel, 'getAllArchivedWithPlot')) {
            $records = $this->burialModel->getAllArchivedWithPlot();
        } else {
            $sql = "
                SELECT
                    b.*,
                    p.plot_number,
                    mb.title AS block_title
                FROM burials b
                LEFT JOIN plots p       ON p.id = b.plot_id
                LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
                WHERE b.is_active = 0
                ORDER BY b.archived_at DESC, b.updated_at DESC
                LIMIT 2000
            ";
            $this->db->query($sql);
            $records = $this->db->resultSet();
        }

        $data = ['title' => 'Archived Burials', 'records' => $records ?: []];
        $this->view('staff/archived_burials', $data);
    }

    /* =======================================================
     * ADD / EDIT / VIEW / ACTIONS (staff paths)
     * ======================================================= */

    // GET or POST /staff/addBurial
    // - GET  -> show modal-wizard page (needs $data['plots'])
    // - POST -> create/update; returns JSON (same payload as admin)
    public function addBurial()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET: render the page
            if (method_exists($this->burialModel, 'getVacantPlots')) {
                $plots = $this->burialModel->getVacantPlots();
            } else {
                $sql = "SELECT id, plot_number FROM plots WHERE is_vacant = 1 ORDER BY plot_number ASC";
                $this->db->query($sql);
                $plots = $this->db->resultSet();
            }
            $this->view('staff/add_burial', ['title'=>'Add Burial', 'plots'=>$plots ?: []]);
            return;
        }

        // POST: create or update (same as Admin, but stamp actor)
        header('Content-Type: application/json');

        try {
            $uid  = (int)($_SESSION['user']['id'] ?? 0);
            $role = (string)($_SESSION['user']['role'] ?? 'staff');
            if (!$uid) throw new Exception('Unauthorized');

            // Collect expected payload (align with admin JS)
            $payload = [
                'burial_id'               => trim($_POST['burial_id'] ?? ''),
                'plot_id'                 => (int)($_POST['plot_id'] ?? 0),
                'deceased_first_name'     => trim($_POST['deceased_first_name'] ?? ''),
                'deceased_middle_name'    => trim($_POST['deceased_middle_name'] ?? ''),
                'deceased_last_name'      => trim($_POST['deceased_last_name'] ?? ''),
                'deceased_suffix'         => trim($_POST['deceased_suffix'] ?? ''),
                'age'                     => trim($_POST['age'] ?? ''),
                'sex'                     => trim($_POST['sex'] ?? ''),
                'date_born'               => trim($_POST['date_born'] ?? ''),
                'date_died'               => trim($_POST['date_died'] ?? ''),
                'cause_of_death'          => trim($_POST['cause_of_death'] ?? ''),
                'grave_level'             => trim($_POST['grave_level'] ?? ''),
                'grave_type'              => trim($_POST['grave_type'] ?? ''),
                'interment_full_name'     => trim($_POST['interment_full_name'] ?? ''),
                'interment_relationship'  => trim($_POST['interment_relationship'] ?? ''),
                'interment_contact_number'=> trim($_POST['interment_contact_number'] ?? ''),
                'interment_address'       => trim($_POST['interment_address'] ?? ''),
                'interment_email'         => trim($_POST['interment_email'] ?? ''),
                'payment_amount'          => trim($_POST['payment_amount'] ?? ''),
                'rental_date'             => trim($_POST['rental_date'] ?? ''), // Y-m-d H:i:S (may be empty)
                'expiry_date'             => trim($_POST['expiry_date'] ?? ''), // Y-m-d H:i:S (may be empty)
                'requirements'            => trim($_POST['requirements'] ?? ''),
                'created_by'              => $uid,
                'updated_by'              => $uid,
                'actor_role'              => $role,
            ];

            // Minimal validation to match admin
            if ($payload['plot_id'] <= 0)                       throw new Exception('Plot is required.');
            if ($payload['deceased_first_name'] === '')         throw new Exception('First name is required.');
            if ($payload['deceased_last_name'] === '')          throw new Exception('Last name is required.');
            if ($payload['date_died'] === '')                   throw new Exception('Date died is required.');
            if ($payload['interment_full_name'] === '')         throw new Exception('IRH name is required.');
            if ($payload['interment_relationship'] === '')      throw new Exception('IRH relationship is required.');
            if ($payload['payment_amount'] === '')              throw new Exception('Payment amount is required.');

            // UPDATE if burial_id present
            if ($payload['burial_id'] !== '') {
                if (method_exists($this->burialModel, 'updateBurial')) {
                    $ok = $this->burialModel->updateBurial($payload);
                } else {
                    // Fallback simple UPDATE (adjust columns to your schema)
                    $sql = "
                        UPDATE burials SET
                          plot_id = :plot_id,
                          deceased_first_name = :dfn,
                          deceased_middle_name = :dmn,
                          deceased_last_name = :dln,
                          deceased_suffix = :suf,
                          age = :age,
                          sex = :sex,
                          date_born = :born,
                          date_died = :died,
                          cause_of_death = :cod,
                          grave_level = :glvl,
                          grave_type = :gtyp,
                          interment_full_name = :irh,
                          interment_relationship = :rel,
                          interment_contact_number = :contact,
                          interment_address = :addr,
                          interment_email = :email,
                          payment_amount = :amt,
                          rental_date = :rental,
                          expiry_date = :expiry,
                          requirements = :reqs,
                          updated_by = :uid,
                          updated_at = NOW()
                        WHERE burial_id = :bid
                        LIMIT 1
                    ";
                    $this->db->query($sql);
                    $this->db->bind(':plot_id',  $payload['plot_id']);
                    $this->db->bind(':dfn',      $payload['deceased_first_name']);
                    $this->db->bind(':dmn',      $payload['deceased_middle_name']);
                    $this->db->bind(':dln',      $payload['deceased_last_name']);
                    $this->db->bind(':suf',      $payload['deceased_suffix']);
                    $this->db->bind(':age',      $payload['age']);
                    $this->db->bind(':sex',      $payload['sex']);
                    $this->db->bind(':born',     $payload['date_born'] ?: null);
                    $this->db->bind(':died',     $payload['date_died']);
                    $this->db->bind(':cod',      $payload['cause_of_death']);
                    $this->db->bind(':glvl',     $payload['grave_level']);
                    $this->db->bind(':gtyp',     $payload['grave_type']);
                    $this->db->bind(':irh',      $payload['interment_full_name']);
                    $this->db->bind(':rel',      $payload['interment_relationship']);
                    $this->db->bind(':contact',  $payload['interment_contact_number']);
                    $this->db->bind(':addr',     $payload['interment_address']);
                    $this->db->bind(':email',    $payload['interment_email']);
                    $this->db->bind(':amt',      $payload['payment_amount']);
                    $this->db->bind(':rental',   $payload['rental_date'] ?: null);
                    $this->db->bind(':expiry',   $payload['expiry_date'] ?: null);
                    $this->db->bind(':reqs',     $payload['requirements']);
                    $this->db->bind(':uid',      $uid);
                    $this->db->bind(':bid',      $payload['burial_id']);
                    $ok = $this->db->execute();
                }

                if (!$ok) throw new Exception('Update failed.');
                echo json_encode(['ok'=>true, 'burial_id'=>$payload['burial_id']]);
                return;
            }

            // CREATE (no burial_id)
            if (!method_exists($this->burialModel, 'createBurial')) {
                // Fallback INSERT (adjust columns to your schema)
                $sql = "
                    INSERT INTO burials (
                      plot_id,
                      deceased_first_name, deceased_middle_name, deceased_last_name, deceased_suffix,
                      age, sex, date_born, date_died, cause_of_death,
                      grave_level, grave_type,
                      interment_full_name, interment_relationship, interment_contact_number, interment_address, interment_email,
                      payment_amount, rental_date, expiry_date, requirements,
                      is_active, created_by, updated_by, created_at, updated_at
                    ) VALUES (
                      :plot_id,
                      :dfn, :dmn, :dln, :suf,
                      :age, :sex, :born, :died, :cod,
                      :glvl, :gtyp,
                      :irh, :rel, :contact, :addr, :email,
                      :amt, :rental, :expiry, :reqs,
                      1, :uid, :uid, NOW(), NOW()
                    )
                ";
                $this->db->query($sql);
                $this->db->bind(':plot_id',  $payload['plot_id']);
                $this->db->bind(':dfn',      $payload['deceased_first_name']);
                $this->db->bind(':dmn',      $payload['deceased_middle_name']);
                $this->db->bind(':dln',      $payload['deceased_last_name']);
                $this->db->bind(':suf',      $payload['deceased_suffix']);
                $this->db->bind(':age',      $payload['age']);
                $this->db->bind(':sex',      $payload['sex']);
                $this->db->bind(':born',     $payload['date_born'] ?: null);
                $this->db->bind(':died',     $payload['date_died']);
                $this->db->bind(':cod',      $payload['cause_of_death']);
                $this->db->bind(':glvl',     $payload['grave_level']);
                $this->db->bind(':gtyp',     $payload['grave_type']);
                $this->db->bind(':irh',      $payload['interment_full_name']);
                $this->db->bind(':rel',      $payload['interment_relationship']);
                $this->db->bind(':contact',  $payload['interment_contact_number']);
                $this->db->bind(':addr',     $payload['interment_address']);
                $this->db->bind(':email',    $payload['interment_email']);
                $this->db->bind(':amt',      $payload['payment_amount']);
                $this->db->bind(':rental',   $payload['rental_date'] ?: null);
                $this->db->bind(':expiry',   $payload['expiry_date'] ?: null);
                $this->db->bind(':reqs',     $payload['requirements']);
                $this->db->bind(':uid',      $uid);
                $ok = $this->db->execute();

                if (!$ok) throw new Exception('Save failed.');

                // Grab last id + generate a displayable burial_id if your schema uses AUTO IDs
                $burialId = (int)($this->db->lastInsertId() ?? 0);
                // If you have a separate burial_id format, compute it here; otherwise return numeric.
                echo json_encode(['ok'=>true, 'burial_id'=>$burialId]);
                return;
            }

            // Use model createBurial (preferred)
            $res = $this->burialModel->createBurial($payload);
            if (empty($res['burial_id'])) throw new Exception('Save failed.');
            echo json_encode(['ok'=>true] + $res);

        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        }
    }

    // GET /staff/getBurialDetails/{id}  (for View/Edit modals)
    public function getBurialDetails($id = null)
    {
        header('Content-Type: application/json');
        try {
            $bid = $id ?? ($_GET['id'] ?? '');
            if ($bid === '') throw new Exception('Missing id');

            if (method_exists($this->burialModel, 'getDetails')) {
                $row = $this->burialModel->getDetails($bid);
            } else {
                $sql = "
                    SELECT b.*, p.plot_number, mb.title AS block_title
                    FROM burials b
                    LEFT JOIN plots p       ON p.id = b.plot_id
                    LEFT JOIN map_blocks mb ON mb.id = p.map_block_id
                    WHERE b.burial_id = :bid
                    LIMIT 1
                ";
                $this->db->query($sql);
                $this->db->bind(':bid', $bid);
                $row = $this->db->single();
            }
            if (!$row) throw new Exception('Not found');

            echo json_encode($row);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        }
    }

    // POST /staff/archiveBurial/{id} or with body burial_id
    public function archiveBurial($id = null)
    {
        header('Content-Type: application/json');
        try {
            $burialId = (int)($id ?? ($_POST['burial_id'] ?? 0));
            if ($burialId <= 0) throw new Exception('Missing id');

            if (method_exists($this->burialModel, 'archiveBurial')) {
                $ok = $this->burialModel->archiveBurial($burialId, (int)($_SESSION['user']['id'] ?? 0));
            } else {
                $sql = "
                    UPDATE burials
                    SET is_active = 0, archived_at = NOW(), updated_by = :uid
                    WHERE burial_id = :bid
                    LIMIT 1
                ";
                $this->db->query($sql);
                $this->db->bind(':uid', (int)($_SESSION['user']['id'] ?? 0));
                $this->db->bind(':bid', $burialId);
                $ok = $this->db->execute();
            }

            if (!$ok) throw new Exception('Archive failed');
            echo json_encode(['ok'=>true]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        }
    }

    // POST /staff/restoreBurial/{id}
    public function restoreBurial($id = null)
    {
        header('Content-Type: application/json');
        try {
            $burialId = (int)($id ?? ($_POST['burial_id'] ?? 0));
            if ($burialId <= 0) throw new Exception('Missing id');

            if (method_exists($this->burialModel, 'restoreBurial')) {
                $ok = $this->burialModel->restoreBurial($burialId, (int)($_SESSION['user']['id'] ?? 0));
            } else {
                $sql = "
                    UPDATE burials
                    SET is_active = 1, archived_at = NULL, updated_by = :uid
                    WHERE burial_id = :bid
                    LIMIT 1
                ";
                $this->db->query($sql);
                $this->db->bind(':uid', (int)($_SESSION['user']['id'] ?? 0));
                $this->db->bind(':bid', $burialId);
                $ok = $this->db->execute();
            }

            if (!$ok) throw new Exception('Restore failed');
            echo json_encode(['ok'=>true]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        }
    }

    // POST /staff/deleteBurial/{id}  (if staff is allowed to delete)
    public function deleteBurial($id = null)
    {
        header('Content-Type: application/json');
        try {
            $burialId = (int)($id ?? ($_POST['burial_id'] ?? 0));
            if ($burialId <= 0) throw new Exception('Missing id');

            if (method_exists($this->burialModel, 'deleteBurial')) {
                $ok = $this->burialModel->deleteBurial($burialId);
            } else {
                $sql = "DELETE FROM burials WHERE burial_id = :bid LIMIT 1";
                $this->db->query($sql);
                $this->db->bind(':bid', $burialId);
                $ok = $this->db->execute();
            }

            if (!$ok) throw new Exception('Delete failed');
            echo json_encode(['ok'=>true]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>$e->getMessage()]);
        }
    }

    /* =======================================================
     * PRINT ROUTES (optional; keep staff path, reuse admin views)
     * Point these to the same print views if you have them.
     * ======================================================= */
    public function printBurialForm($id)
    {
        // If you already have an admin print view, you can reuse it:
        $_GET['autoprint'] = ($_GET['autoprint'] ?? null);
        $this->view('admin/print_burial_form', ['burial_id'=>$id]); // or staff equivalent if you have
    }

    public function printContract($id)
    {
        $this->view('admin/print_contract', ['burial_id'=>$id]); // or staff equivalent
    }

    public function printQrTicket($id)
    {
        $this->view('admin/print_qr_ticket', ['burial_id'=>$id]); // or staff equivalent
    }

    /* ----------------- helpers ----------------- */
    private function scalar(string $sql)
    {
        if (!$this->db) return 0;
        $this->db->query($sql);
        $row = $this->db->single();
        return (int)($row->c ?? 0);
    }
}
