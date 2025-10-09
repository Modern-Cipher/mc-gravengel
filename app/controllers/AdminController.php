<?php
// app/controllers/AdminController.php

class AdminController extends Controller
{
    private $userModel;
    private $mapModel;
    private $burialModel;
    private $renewalModel;
    private $auditModel; // IDAGDAG ITO
    private $activityLogModel; // IDAGDAG ITO



    public function __construct($params = [])
    {
        // [BAGO] Tinatawag ang parent constructor
        parent::__construct($params);

        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            // Kung AJAX request, mag-send ng JSON error imbes na redirect
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                http_response_code(401); // Unauthorized
                echo json_encode(['ok' => false, 'message' => 'Session expired. Please log in again.']);
                exit;
            }
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }

        $this->userModel   = $this->model('User');
        $this->mapModel    = $this->model('Map');
        $this->burialModel = $this->model('Burial');
        $this->renewalModel = $this->model('Renewal');
        $this->auditModel   = $this->model('Audit');
        $this->activityLogModel = $this->model('ActivityLog');

        // Ngayon, gagana na ito nang tama dahil may laman na ang $this->params
        $currentMethod = $this->params[0] ?? 'dashboard';
        if (($_SESSION['user']['role'] ?? '') !== 'admin' &&
            in_array($currentMethod, ['userAccounts', 'updateBlock', 'addStaff', 'updateStaff'], true)
        ) {
            redirect('admin/dashboard');
        }
    }

    /* =================== NAV / LANDING =================== */
    public function index()
    {
        redirect('admin/burialRecords');
    }

    public function dashboard()
    {
        // NOTE: these model helpers must exist — countActive(), countExpired(), countTodayTransactions()
        $this->view('admin/index', [
            'title'           => 'Dashboard',
            'name'            => $_SESSION['user']['name'] ?? 'Staff',
            'must_change_pwd' => (int)($_SESSION['user']['must_change_pwd'] ?? 0),
            'metrics'         => [
                'active'   => method_exists($this->burialModel, 'countActive')             ? (int)$this->burialModel->countActive()             : 0,
                'expired'  => method_exists($this->burialModel, 'countExpired')            ? (int)$this->burialModel->countExpired()            : 0,
                'todayTx'  => method_exists($this->burialModel, 'countTodayTransactions')  ? (int)$this->burialModel->countTodayTransactions()  : 0,
                'staff'    => method_exists($this->userModel, 'countStaffUsers')           ? (int)$this->userModel->countStaffUsers()           : 0,
            ],
        ]);
    }

    /* =================== PAGES =================== */
    public function cemeteryMap()
    {
        $blocks = $this->mapModel->getAllBlocks();
        $this->view('admin/cemetery_map', ['title' => 'Cemetery Map', 'blocks' => $blocks]);
    }

    public function userAccounts()
    {
        $uid   = $_SESSION['user']['id'];
        $users = $this->userModel->getAllUsersExcluding($uid);
        $this->view('admin/user_accounts', ['title' => 'User Accounts', 'users' => $users]);
    }

    public function profile()
    {
        $uid  = $_SESSION['user']['id'];
        $user = $this->userModel->findById($uid);
        $this->view('admin/profile', ['title' => 'My Profile', 'user' => $user]);
    }

    public function contact_us()
    {
        $this->view('admin/contact_us', ['title' => 'Contact Us']);
    }

    /* =================== MAP =================== */
    public function updateBlock()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/cemeteryMap');
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $data = [
            'id'         => $_POST['id'],
            'title'      => trim($_POST['title']),
            'offset_x'   => (int)$_POST['offset_x'],
            'offset_y'   => (int)$_POST['offset_y'],
            'modal_rows' => (int)$_POST['modal_rows'],
            'modal_cols' => (int)$_POST['modal_cols'],
        ];
        if ($this->mapModel->updateBlock($data)) {
            $_SESSION['flash_message'] = 'Block details saved successfully!';
            $_SESSION['flash_type'] = 'success';
            redirect('admin/cemeteryMap');
        } else {
            die('Something went wrong');
        }
    }

    /* =================== USERS =================== */
// Sa loob ng app/controllers/AdminController.php

   public function addStaff()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // Sequential Staff ID generation logic from previous update
        $latestIdNumber = $this->userModel->getLatestStaffIdNumber();
        $newIdNumber = $latestIdNumber + 1;
        $staff_id = 'S-' . str_pad($newIdNumber, 3, '0', STR_PAD_LEFT);

        $data = [
            'first_name'  => trim($_POST['first_name'] ?? ''),
            'last_name'   => trim($_POST['last_name'] ?? ''),
            'username'    => trim($_POST['username'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'staff_id'    => $staff_id,
            'designation' => trim($_POST['designation'] ?? ''),
        ];

        // --- VALIDATION CHECKS ---

        // 1. Check for empty required fields
        if (in_array('', [$data['first_name'], $data['last_name'], $data['username'], $data['email']], true)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            return;
        }
        
        // 2. [BAGO] Server-side validation for email and phone format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }
        
        // Regex to match the format '09xx xxx xxxx' (consistent with JS)
        if (!preg_match('/^09\d{2} \d{3} \d{4}$/', $data['phone'])) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number in the format 0912 345 6789.']);
            return;
        }

        // 3. Check for duplicate username or email
        if ($this->userModel->findByUsernameOrEmail($data['username'])) {
            echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
            return;
        }
        if ($this->userModel->findByUsernameOrEmail($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
            return;
        }

        // --- END OF VALIDATION ---

        $temp_password           = substr(bin2hex(random_bytes(8)), 0, 12);
        $data['password_hash']   = password_hash($temp_password, PASSWORD_DEFAULT);
        $data['must_change_pwd'] = 1;

        $user_id = $this->userModel->addStaffUser($data);
        if ($user_id) {
            $this->logActivity('add_staff', "Created new staff account for {$data['username']}"); // LOG SUCCESS
            echo json_encode([
                'success' => true,
                'message' => 'Staff account created successfully!',
                'user' => ['full_name' => $data['first_name'] . ' ' . $data['last_name']],
                'temp_password' => $temp_password
            ]);
        } else {
            $this->logActivity('add_staff_failed', "Attempted to create account for {$data['username']}"); // LOG FAILURE
            echo json_encode(['success' => false, 'message' => 'Failed to create user account.']);
        }
    }

    // JSON diff update; no-op kung walang nabago
   public function updateStaff()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id    = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing user id.']);
            return;
        }

        $current = $this->userModel->findById($id);
        if (!$current) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            return;
        }

        $changes = [
            'first_name'  => trim($_POST['first_name'] ?? $current->first_name),
            'last_name'   => trim($_POST['last_name'] ?? $current->last_name),
            'email'       => trim($_POST['email'] ?? $current->email),
            'phone'       => trim($_POST['phone'] ?? $current->phone),
            'designation' => trim($_POST['designation'] ?? $current->designation),
        ];

        // --- [BAGO] SERVER-SIDE VALIDATION FOR UPDATE ---
        if (!filter_var($changes['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
            return;
        }
        if (!preg_match('/^09\d{2} \d{3} \d{4}$/', $changes['phone'])) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid phone number in the format 0912 345 6789.']);
            return;
        }
        // --- END OF VALIDATION ---

        if ($changes['email'] !== $current->email) {
            $existingUser = $this->userModel->findByUsernameOrEmail($changes['email']);
            if ($existingUser && (int)$existingUser->id !== $id) {
                echo json_encode(['success' => false, 'message' => 'Email is already in use by another account.']);
                return;
            }
        }

        $ok = $this->userModel->updateStaffUser($id, $changes);

        echo json_encode(
            $ok
                ? ['success' => true, 'message' => 'User details updated successfully.']
                : ['success' => false, 'message' => 'No changes were detected or the update failed.']
        );
    }
    

    /* =================== BURIALS =================== */
    public function burialRecords()
    {
        $records = $this->burialModel->getAllBurialRecords();
        $this->view('admin/burial_records', ['title' => 'Burial Records', 'records' => $records]);
    }


    public function addBurial()
    {
        // --- POST REQUEST: I-handle ang form submission ---
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $parentBurialId = trim($_POST['parent_burial_id'] ?? '') ?: null;

            $data = [
                'plot_id'                  => (int)($_POST['plot_id'] ?? 0),
                'parent_burial_id'         => $parentBurialId, // Gagamitin para sa secondary burials
                'deceased_first_name'      => trim($_POST['deceased_first_name'] ?? ''),
                'deceased_middle_name'     => trim($_POST['deceased_middle_name'] ?? ''),
                'deceased_last_name'       => trim($_POST['deceased_last_name'] ?? ''),
                'deceased_suffix'          => trim($_POST['deceased_suffix'] ?? ''),
                'age'                      => trim($_POST['age'] ?? ''),
                'sex'                      => $_POST['sex'] ?? '',
                'date_born'                => $_POST['date_born'] ?: null,
                'date_died'                => $_POST['date_died'] ?: null,
                'cause_of_death'           => trim($_POST['cause_of_death'] ?? ''),
                'grave_level'              => $_POST['grave_level'] ?? '',
                'grave_type'               => $_POST['grave_type'] ?? '',
                'interment_full_name'      => trim($_POST['interment_full_name'] ?? ''),
                'interment_relationship'   => $_POST['interment_relationship'] ?? '',
                'interment_contact_number' => $_POST['interment_contact_number'] ?? '',
                'interment_address'        => trim($_POST['interment_address'] ?? ''),
                'interment_email'          => trim($_POST['interment_email'] ?? '') ?: null,
                'payment_amount'           => (float)($_POST['payment_amount'] ?? 0),
                'rental_date'              => $_POST['rental_date'] ?: null,
                'expiry_date'              => $_POST['expiry_date'] ?: null,
                'requirements'             => $_POST['requirements'] ?? '',
                'created_by_user_id'       => $_SESSION['user']['id'] ?? null,
            ];

            foreach (['plot_id', 'deceased_first_name', 'deceased_last_name', 'date_died', 'interment_full_name', 'interment_relationship'] as $k) {
                if (empty($data[$k])) {
                    echo json_encode(['ok' => false, 'message' => 'Missing required field: ' . $k]);
                    return;
                }
            }

            $res = $this->burialModel->create($data); // Dapat updated na ang create function sa Burial.php
            if (!$res) {
                $this->logActivity('create_burial_failed', "Plot ID: {$data['plot_id']}"); // LOG FAILURE
                echo json_encode(['ok' => false, 'message' => 'Failed to save burial record.']);
                return;
            }
            $this->logActivity('create_burial', "Created burial record {$res['burial_id']} for Plot ID: {$data['plot_id']}"); // LOG SUCCESS

            echo json_encode([
                'ok' => true,
                'message' => 'Saved',
                'burial_id' => $res['burial_id'],
                'transaction_id' => $res['transaction_id']
            ]);
            return; // Mahalaga ito para hindi mag-render ng view sa POST request
        }

        // --- GET REQUEST: I-display ang form ---
        $all_plots = $this->burialModel->getAllPlotsForDropdown();

        $plots_grouped = [
            'vacant' => [],
            'occupied' => []
        ];

        foreach ($all_plots as $plot) {
            $plots_grouped[$plot->status][] = $plot;
        }

        $data = [
            'plots_grouped' => $plots_grouped
        ];

        // Siguraduhing tama ang path ng iyong view file.
        $viewPath = file_exists(APPROOT . '/views/admin/add_burial.php') ? 'admin/add_burial' : 'staff/add_burial';
        $this->view($viewPath, $data);
    }




    public function getPrimaryBurialForPlot($plot_id = 0)
    {
        header('Content-Type: application/json');
        $plot_id = (int)$plot_id;
        if ($plot_id > 0) {
            $primary = $this->burialModel->getPrimaryBurialForPlot($plot_id);
            if ($primary) {
                echo json_encode(['ok' => true, 'parent_burial_id' => $primary->burial_id]);
                return;
            }
        }
        echo json_encode(['ok' => false, 'message' => 'No active primary burial found for this plot.']);
    }

    public function updateBurial()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $burialId = trim($_POST['burial_id'] ?? '');
        if (empty($burialId)) {
            echo json_encode(['ok' => false, 'message' => 'Missing burial ID.']);
            return;
        }

        $existingRecord = $this->burialModel->findAnyByBurialId($burialId);
        if (!$existingRecord) {
            echo json_encode(['ok' => false, 'message' => 'Burial record not found.']);
            return;
        }

        $interment_email  = trim($_POST['interment_email'] ?? '');
        if ($interment_email !== '' && !filter_var($interment_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }

        $submittedData = [
            'burial_id'                => $burialId,
            'deceased_first_name'      => trim($_POST['deceased_first_name'] ?? ''),
            'deceased_middle_name'     => trim($_POST['deceased_middle_name'] ?? ''),
            'deceased_last_name'       => trim($_POST['deceased_last_name'] ?? ''),
            'deceased_suffix'          => trim($_POST['deceased_suffix'] ?? ''),
            'age'                      => trim($_POST['age'] ?? ''),
            'sex'                      => $_POST['sex'] ?? '',
            'date_born'                => $_POST['date_born'] ?: null,
            'date_died'                => $_POST['date_died'] ?: null,
            'cause_of_death'           => trim($_POST['cause_of_death'] ?? ''),
            'grave_level'              => $_POST['grave_level'] ?? '',
            'grave_type'               => $_POST['grave_type'] ?? '',
            'interment_full_name'      => trim($_POST['interment_full_name'] ?? ''),
            'interment_relationship'   => $_POST['interment_relationship'] ?? '',
            'interment_contact_number' => $_POST['interment_contact_number'] ?? '',
            'interment_address'        => trim($_POST['interment_address'] ?? ''),
            'interment_email'          => ($interment_email === '') ? null : $interment_email,
            'payment_amount'           => (float)($_POST['payment_amount'] ?? 0),
            'rental_date'              => $_POST['rental_date'] ?: null,
            'expiry_date'              => $_POST['expiry_date'] ?: null,
            'requirements'             => trim($_POST['requirements'] ?? ''),
            'updated_by_user_id'       => $_SESSION['user']['id'] ?? null,
        ];

        $finalData = array_merge((array)$existingRecord, $submittedData);

        if ($this->burialModel->updateBurial($finalData)) {
            $this->logActivity('update_burial', "Updated burial record ID: {$burialId}"); // LOG ACTION
            echo json_encode(['ok' => true, 'message' => 'Record updated successfully!']);
        } else {
            echo json_encode(['ok' => false, 'message' => 'No changes were detected or the update failed.']);
        }
    }


    private function makeTransactionId($suffixInt): string
    {
        $suffixInt = (int)$suffixInt;
        $seq = str_pad((string)($suffixInt % 1000), 3, '0', STR_PAD_LEFT);
        return date('Ymd') . '-' . $seq;
    }

    /* ---- JSON/PRINT ---- */
    public function burialJson($burial_id)
    {
        header('Content-Type: application/json');
        if (!$burial_id) {
            echo json_encode(['ok' => false, 'message' => 'No id']);
            return;
        }
        $r = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$r) {
            echo json_encode(['ok' => false, 'message' => 'Not found']);
            return;
        }
        echo json_encode(['ok' => true, 'data' => $r]);
    }

    public function printBurialForm($burial_id)
    {
        $this->printHandler('print_burial_form', $burial_id);
    }
    public function printContract($burial_id)
    {
        $this->printHandler('print_contract', $burial_id);
    }
    public function printQrTicket($burial_id)
    {
        $this->printHandler('print_qr_ticket', $burial_id);
    }
    private function printHandler($view, $burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) {
            echo '<h3 style="padding:16px">Burial record not found.</h3>';
            return;
        }
        $this->view('admin/' . $view, ['r' => $rec]);
    }

    public function getBurialDetails($burialId = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($burialId)) {
            echo json_encode(['error' => true, 'message' => 'Invalid request']);
            return;
        }
        $row = $this->burialModel->getBurialById($burialId);
        if (!$row) {
            echo json_encode(['error' => true, 'message' => 'Not found']);
            return;
        }
        echo json_encode($row);
    }

    /** (optional) GET /admin/getPlotBurials/{plot_id} — fallback for the JS */
    public function getPlotBurials($plotId = 0)
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !is_numeric($plotId) || !$plotId) {
            echo json_encode(['error' => true, 'message' => 'Invalid plot id']);
            return;
        }
        $rows = $this->burialModel->getBurialsByPlot((int)$plotId);
        echo json_encode(['burials' => $rows]);
    }


    /* ---- Delete / Archive / Restore ----
       * IMPORTANT: Archive/Restore DO NOT change plot status.
       * Only DELETE frees the plot (if you still want that).
    */
    /* ---- Delete / Archive / Restore ---- */
    public function deleteBurial($burial_id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Invalid method']);
            return;
        }
        $burial_id = trim($burial_id ?? '');
        if ($burial_id === '') {
            echo json_encode(['ok' => false, 'message' => 'Missing burial id']);
            return;
        }

        $ok = $this->burialModel->deleteByBurialId($burial_id);
        if ($ok) {
            $this->logActivity('delete_burial', "Permanently deleted Burial ID: " . htmlspecialchars($burial_id)); // LOG ACTION
        }
        echo json_encode(['ok' => $ok, 'message' => $ok ? 'Deleted' : 'Failed to delete']);
    }


    public function archivedBurials()
    {
        $records = $this->burialModel->getAllBurialRecordsArchived();
        $this->view('admin/archived_burials', ['title' => 'Archived Burials', 'records' => $records]);
    }

    public function archiveBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) {
            echo json_encode(['ok' => false, 'message' => 'No burial ID']);
            return;
        }
        $ok = $this->burialModel->archiveByBurialId($burial_id);
        if ($ok) {
            $this->logActivity('archive_burial', "Archived Burial ID: " . htmlspecialchars($burial_id)); // LOG ACTION
        }
        echo json_encode(['ok' => (bool)$ok, 'message' => $ok ? 'Archived' : 'Archive failed']);
    }

    public function restoreBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) {
            echo json_encode(['ok' => false, 'message' => 'No burial ID']);
            return;
        }
        $ok = $this->burialModel->restoreByBurialId($burial_id);
        if ($ok) {
            $this->logActivity('restore_burial', "Restored Burial ID: " . htmlspecialchars($burial_id)); // LOG ACTION
        }
        echo json_encode(['ok' => (bool)$ok, 'message' => $ok ? 'Restored' : 'Restore failed']);
    }
    /* =================== LOGS & REPORTS =================== */
    public function logsReports()
    {
        $this->view('admin/logs_reports', ['title' => 'Logs & Reports']);
    }

    public function fetchActivityLogs()
    {
        header('Content-Type: application/json');

        $filters = [
            'from' => $_GET['from'] ?? '',
            'to'   => $_GET['to']   ?? '',
            'q'    => trim($_GET['q'] ?? '')
        ];

        // Gamitin ang bago nating ActivityLog model para kunin ang logs mula sa 'activity_log' table
        $logs = $this->activityLogModel->getLogs($filters);
        
        echo json_encode(['ok' => true, 'rows' => $logs]);
    }

    public function fetchTransactionReports()
    {
        header('Content-Type: application/json');

        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';
        $q    = trim($_GET['q'] ?? '');

        $db  = new Database();
        $sql = "
          SELECT
            b.transaction_id,
            b.burial_id,
            mb.title                     AS block_title,
            p.plot_number,
            b.interment_full_name,
            b.interment_relationship,
            b.interment_address,
            b.interment_contact_number,
            b.interment_email,               -- NEW
            b.payment_amount,
            b.rental_date,
            b.expiry_date,
            CONCAT_WS(' ',
              b.deceased_first_name,
              NULLIF(b.deceased_middle_name,''),
              b.deceased_last_name,
              NULLIF(b.deceased_suffix,'')
            ) AS deceased_full_name,
            b.sex,
            b.age,
            b.grave_level,
            b.grave_type,
            sd.staff_id                 AS created_by_staff_id,
            u.username                  AS created_by_username,
            b.created_at
          FROM burials b
          JOIN plots p        ON p.id = b.plot_id
          JOIN map_blocks mb  ON mb.id = p.map_block_id
          LEFT JOIN users u   ON u.id = b.created_by_user_id
          LEFT JOIN staff_details sd ON sd.user_id = u.id
          WHERE 1=1
        ";

        $bind = [];
        if ($from !== '') {
            $sql .= " AND DATE(b.rental_date) >= :rfrom";
            $bind[':rfrom'] = $from;
        }
        if ($to   !== '') {
            $sql .= " AND DATE(b.rental_date) <= :rto";
            $bind[':rto']   = $to;
        }

        if ($q !== '') {
            $sql .= " AND (
                b.transaction_id LIKE :qq OR
                b.burial_id LIKE :qq OR
                b.interment_full_name LIKE :qq OR
                b.interment_contact_number LIKE :qq OR
                b.interment_email LIKE :qq OR         -- NEW in search
                p.plot_number LIKE :qq OR
                mb.title LIKE :qq
            )";
            $bind[':qq'] = "%{$q}%";
        }

        $sql .= " ORDER BY b.rental_date DESC, b.created_at DESC";

        $db->query($sql);
        foreach ($bind as $k => $v) $db->bind($k, $v);
        $rows = $db->resultSet();

        echo json_encode(['ok' => true, 'rows' => $rows]);
    }

    /* ---------- JSON: dashboard cards counters ---------- */
    public function dashboardCards()
    {
        header('Content-Type: application/json');
        try {
            $active  = method_exists($this->burialModel, 'countActive') ? (int)$this->burialModel->countActive() : 0;
            $expired = method_exists($this->burialModel, 'countExpired') ? (int)$this->burialModel->countExpired() : 0;
            $today   = method_exists($this->burialModel, 'countTodayTransactions') ? (int)$this->burialModel->countTodayTransactions() : 0;
            $staff   = method_exists($this->userModel,   'countStaffUsers') ? (int)$this->userModel->countStaffUsers() : 0;

            echo json_encode(['ok' => true, 'active' => $active, 'expired' => $expired, 'today' => $today, 'staff' => $staff]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to load cards']);
        }
    }

    /* ---------- JSON: calendar rental-expiry events ---------- */
    public function expiryEvents()
    {
        // Always JSON
        header('Content-Type: application/json');

        // Accept FullCalendar's ?start=&end= OR our ?from=&to=
        $from = $_GET['from']  ?? $_GET['start'] ?? null;
        $to   = $_GET['to']    ?? $_GET['end']   ?? null;

        // Basic guard
        if (!$from || !$to) {
            echo json_encode(['ok' => true, 'events' => []]);
            return;
        }

        // Check model method exists to avoid fatals
        if (!method_exists($this->burialModel, 'getExpiryEventsInRange')) {
            echo json_encode([
                'ok' => false,
                'events' => [],
                'message' => 'Model method getExpiryEventsInRange() missing'
            ]);
            return;
        }

        try {
            $rows = $this->burialModel->getExpiryEventsInRange($from, $to);
            $events = [];

            foreach ($rows as $r) {
                $name  = trim(($r->deceased_first_name ?? '') . ' ' . ($r->deceased_last_name ?? ''));
                $title = ($name !== '' ? $name : ($r->interment_full_name ?? '')) . ' — Expiry';

                $events[] = [
                    'id'       => $r->burial_id,
                    'title'    => $title,
                    'start'    => $r->expiry_date,   // may be datetime
                    'allDay'   => false,
                    // extended props for the modal
                    'holder'    => $r->interment_full_name,
                    'burial_id' => $r->burial_id,
                    'block'     => $r->block_title,
                    'plot'      => $r->plot_number,
                    'grave'     => trim(($r->grave_level ?? '') . ' ' . ($r->grave_type ?? '')),
                    'expiry'    => $r->expiry_date,
                ];
            }

            echo json_encode(['ok' => true, 'events' => $events]);
        } catch (Throwable $e) {
            // Do NOT echo the exception text to keep response JSON-clean
            echo json_encode(['ok' => false, 'events' => []]);
        }
    }

    /* ---------- JSON: For Renewal Polling ---------- */
    public function fetchForRenewalData()
    {
        header('Content-Type: application/json');
        try {
            // This is the corrected query logic
            $this->renewalModel = $this->model('Renewal');
            $records = $this->renewalModel->getBurialsForRenewal();
            echo json_encode(['ok' => true, 'records' => $records]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to load renewal data.']);
        }
        exit;
    }

    public function renewals()
    {
        $all_active_burials = $this->renewalModel->getBurialsForRenewal();
        $history = $this->renewalModel->getRenewalHistory();

        $data = [
            'title' => 'Renewals',
            'all_burials' => $all_active_burials,
            'history' => $history
        ];

        $this->view('admin/renewals', $data);
    }


    public function processRenewal()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $burial = $this->renewalModel->getDetailedBurialForRenewal($_POST['burial_id']);

        if (!$burial) {
            echo json_encode(['ok' => false, 'message' => 'Burial record not found or is inactive.']);
            return;
        }

        $oldExpiryDate = $burial->expiry_date;
        $newRentalDate = $oldExpiryDate;

        try {
            $expiry = new DateTime($newRentalDate, new DateTimeZone('Asia/Manila'));
            $expiry->modify('+5 years');
            $newExpiryDate = $expiry->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Invalid expiry date format.']);
            return;
        }

        $data = [
            'burial_id'            => $burial->burial_id,
            'previous_expiry_date' => $oldExpiryDate,
            'new_rental_date'      => $newRentalDate,
            'new_expiry_date'      => $newExpiryDate,
            'payment_amount'       => $_POST['payment_amount'],
            'payment_date'         => $_POST['payment_date'],
            'payer_name'           => $_POST['payer_name'],
            'payer_email'          => trim($_POST['payer_email']),
            'processed_by_user_id' => $_SESSION['user']['id'],
            'receipt_email_status' => 'Not sent (no email provided).'
        ];

        $result = $this->renewalModel->createRenewal($data);

        if ($result && isset($result['ok']) && $result['ok']) {
              $this->logActivity('process_renewal', "Renewed Burial ID: {$data['burial_id']}. New Expiry: {$data['new_expiry_date']}"); // LOG ACTION
            $email_status = $data['receipt_email_status'];

            if (!empty($data['payer_email'])) {
                if (!class_exists('EmailHelper')) {
                    require_once APPROOT . '/helpers/Email.php';
                }

                $email_data_payload = [
                    'payer_name'         => $data['payer_name'],
                    'transaction_id'     => $result['transaction_id'],
                    'payment_date'       => $data['payment_date'],
                    'payment_amount'     => $data['payment_amount'],
                    'new_expiry_date'    => $data['new_expiry_date'],
                    'deceased_name'      => trim($burial->deceased_first_name . ' ' . $burial->deceased_last_name),
                    'plot_label'         => trim(($burial->block_title ?? 'N/A') . ' - ' . ($burial->plot_number ?? 'N/A')),
                ];

                $data_for_template = ['emailData' => $email_data_payload];

                $body = $this->view('emails/renewal_confirmation', $data_for_template, true);
                $subject = 'Official Receipt for Your Renewal - Plaridel Public Cemetery';

                $emailHelper = new EmailHelper();
                $email_ok = $emailHelper->sendEmail($data['payer_email'], $data['payer_name'], $subject, $body);

                $email_status = ($email_ok === true) ? 'Sent successfully.' : 'Failed: ' . $email_ok;
                $this->renewalModel->updateEmailStatus($result['transaction_id'], $email_status);
            }

            echo json_encode([
                'ok' => true,
                'message' => 'Renewal successful! Rental period updated.',
                'email_status' => $email_status,
                'burial_id' => $burial->burial_id // ITO ANG IDINAGDAG
            ]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'Failed to process renewal in database.']);
        }
    }

    public function processVacate()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // Kukunin muna natin ang kumpletong detalye para magamit sa email
        $burial = $this->renewalModel->getDetailedBurialForRenewal($_POST['burial_id']);
        if (!$burial) {
            echo json_encode(['ok' => false, 'message' => 'Burial record not found.']);
            return;
        }

        // Ituloy ang pag-vacate ng plot
        $ok = $this->renewalModel->vacatePlot($burial->burial_id, $burial->plot_id, $_SESSION['user']['id']);

        if ($ok) {
             $this->logActivity('vacate_plot', "Vacated record for Burial ID: {$burial->burial_id}"); // LOG ACTION
            $email_status = 'Not sent (no email on record).';

            // Kung successful ang pag-vacate AT may email, magpadala ng notification
            if (!empty($burial->interment_email)) {
                if (!class_exists('EmailHelper')) {
                    require_once APPROOT . '/helpers/Email.php';
                }

                $email_data_payload = [
                    'interment_name' => $burial->interment_full_name,
                    'deceased_name'  => trim($burial->deceased_first_name . ' ' . $burial->deceased_last_name),
                    'plot_label'     => trim(($burial->block_title ?? 'N/A') . ' - ' . ($burial->plot_number ?? 'N/A')),
                    'vacate_date'    => date('F d, Y') // Petsa ngayon
                ];

                $data_for_template = ['emailData' => $email_data_payload];

                $body = $this->view('emails/vacate_confirmation', $data_for_template, true);
                $subject = 'Confirmation of Plot Vacation - Plaridel Public Cemetery';

                $emailHelper = new EmailHelper();
                $email_ok = $emailHelper->sendEmail($burial->interment_email, $burial->interment_full_name, $subject, $body);

                $email_status = ($email_ok === true) ? 'Sent successfully.' : 'Failed: ' . $email_ok;
            }

            // Mag-reply sa request na may kasamang email status
            echo json_encode([
                'ok' => true,
                'message' => 'Plot has been vacated and record is archived.',
                'email_status' => $email_status
            ]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'Failed to vacate plot.']);
        }
    }

    /* ---- USER STATUS TOGGLE ---- */
    public function setUserActive()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Missing user id']);
            return;
        }

        $ok = $this->userModel->toggleUserActiveStatus($id, $is_active);

        echo json_encode(
            $ok
                ? ['success' => true, 'message' => 'User status updated.']
                : ['success' => false, 'message' => 'Failed to update user status.']
        );
    }


    // Nasa loob ng class AdminController

    public function resetPassword()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // Ensure only admins can perform this action
        if ($_SESSION['user']['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to perform this action.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $userId = (int)($_POST['user_id'] ?? 0);
        $email  = trim($_POST['email'] ?? '');

        if (!$userId || !$email) {
            echo json_encode(['success' => false, 'message' => 'Missing user ID or email.']);
            return;
        }

        $user = $this->userModel->findById($userId);

        if (!$user || strcasecmp($user->email, $email) !== 0) {
            echo json_encode(['success' => false, 'message' => 'User not found or email does not match.']);
            return;
        }

        try {
            $token = $this->userModel->createPasswordResetToken($userId);
            if (!$token) {
                throw new Exception("Could not create a reset token.");
            }

            $reset_link = URLROOT . '/auth/resetPassword?token=' . $token;

            $email_data = [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'reset_link' => $reset_link,
                'message' => 'A system administrator has initiated a password reset for your account. Click the button below to set a new password.'
            ];

            // --- ITO ANG BINAGO ---
            // Tinanggal ang ['data' => ... ] para direktang maipasa ang $email_data.
            $body = $this->view('emails/universal_email', $email_data, true);
            $subject = 'Administrator-Initiated Password Reset';

            $emailHelper = new EmailHelper();
            $recipient_name = $user->first_name . ' ' . $user->last_name;
            $sent = $emailHelper->sendEmail($user->email, $recipient_name, $subject, $body);

            if ($sent !== true) {
                error_log("Admin Reset Email Failed: " . $sent);
                echo json_encode(['success' => false, 'message' => 'Failed to send the reset email. Please check system logs.']);
            } else {
                echo json_encode(['success' => true, 'message' => 'A password reset link has been sent to the user\'s email.']);
            }
        } catch (Exception $e) {
            error_log("Admin Password Reset Error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again.']);
        }
    }


    public function myActivity()
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['user']['id'])) {
            echo json_encode(['ok' => false, 'rows' => [], 'message' => 'Not authenticated.']);
            return;
        }
        $user_id = (int)$_SESSION['user']['id'];

        $db = new Database();
        
        $sql = "
            SELECT * FROM (
                -- Mula sa regular activity log
                SELECT
                    timestamp AS ts,
                    details AS action_text,
                    action_type AS kind
                FROM activity_log
                WHERE user_id = :user_id

                UNION ALL

                -- Mula sa audit log (backup/restore)
                SELECT
                    timestamp AS ts,
                    CONCAT('Status: ', status, '. Details: ', details) AS action_text,
                    action_type AS kind
                FROM audit_log
                WHERE user_id = :user_id
            ) AS AllActivities
            ORDER BY ts DESC
            LIMIT 100 -- Limit to last 100 activities to keep the profile page fast
        ";

        $db->query($sql);
        $db->bind(':user_id', $user_id);

        try {
            $rows = $db->resultSet();
            echo json_encode(['ok' => true, 'rows' => $rows]);
        } catch (Exception $e) {
            error_log("myActivity Error: " . $e->getMessage());
            echo json_encode(['ok' => false, 'rows' => [], 'message' => 'A database error occurred.']);
        }
    }


    /**
     * [BAGO] Gumagawa ng printable report ng LAHAT ng activity ng isang user.
     */
    public function printMyActivity()
    {
        if (empty($_SESSION['user']['id'])) {
            redirect('auth/login');
        }
        $user_id = (int)$_SESSION['user']['id'];
        $user_name = $_SESSION['user']['name'];

        $db = new Database();
        
        // Same query as myActivity() but WITHOUT the LIMIT
        $sql = "
            SELECT * FROM (
                SELECT timestamp AS ts, details AS action_text, action_type AS kind FROM activity_log WHERE user_id = :user_id
                UNION ALL
                SELECT timestamp AS ts, CONCAT('Status: ', status, '. Details: ', details) AS action_text, action_type AS kind FROM audit_log WHERE user_id = :user_id
            ) AS AllActivities
            ORDER BY ts DESC
        ";

        $db->query($sql);
        $db->bind(':user_id', $user_id);
        $activities = $db->resultSet();

        $data = [
            'title' => 'User Activity Report',
            'user_name' => $user_name,
            'activities' => $activities
        ];

        // Gagamit tayo ng bagong view file para sa printing
        $this->view('admin/print_my_activity', $data);
    }

    // Idagdag itong function sa loob ng AdminController class

    public function printRenewalHistory()
    {
        $history = $this->renewalModel->getRenewalHistory();
        $data = [
            'title' => 'Renewal History Report',
            'history' => $history
        ];
        $this->view('admin/print_renewals', $data);
    }




    // app/controllers/AdminController.php

    // Idagdag mo itong bagong function sa loob ng "class AdminController"

    /**
     * [BAGONG FUNCTION] Endpoint para sa data ng dashboard chart.
     */
    public function getDashboardChartData()
    {
        header('Content-Type: application/json');

        // Gamitin ang mga bagong methods mula sa Burial model
        if (!method_exists($this->burialModel, 'countNewRentalsThisMonth')) {
            echo json_encode(['ok' => false, 'message' => 'Required model methods are missing.']);
            return;
        }

        try {
            $data = [
                'new_rentals'   => $this->burialModel->countNewRentalsThisMonth(),
                'expiring_soon' => $this->burialModel->countExpiringSoon(30),
                'total_expired' => $this->burialModel->countAllExpired()
            ];
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to fetch chart data.']);
        }
    }

    // app/controllers/AdminController.php

    // Idagdag mo itong bagong function sa loob ng "class AdminController"

    /**
     * [BAGONG FUNCTION] Endpoint para sa data ng financial chart.
     */
    public function getFinancialChartData()
    {
        header('Content-Type: application/json');

        if (!method_exists($this->burialModel, 'getDailyTransactionTotals')) {
            echo json_encode(['ok' => false, 'message' => 'Required model method is missing.']);
            return;
        }

        try {
            // Kunin ang data para sa nakaraang 7 araw
            $dailyData = $this->burialModel->getDailyTransactionTotals(7);

            // Ihanda ang data para sa Chart.js
            $labels = array_map(function ($date) {
                return date('M d', strtotime($date)); // Format: "Oct 07"
            }, array_keys($dailyData));

            $data = array_values($dailyData);

            echo json_encode(['ok' => true, 'labels' => $labels, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to fetch financial data.']);
        }
    }

    // app/controllers/AdminController.php

    // Idagdag itong tatlong functions sa loob ng "class AdminController"

    /**
     * [BAGONG FUNCTION] Ipinapakita ang Backup & Restore page.
     */
    public function backup()
    {
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('admin/dashboard');
        }

        // Kinukuha na ngayon ang logs mula sa database
        $logs = $this->auditModel->getBackupRestoreLogs();

        $this->view('admin/backup', [
            'title' => 'Backup & Restore',
            'logs' => $logs // Ipasa ang logs sa view
        ]);
    }


    /**
     * [BAGONG FUNCTION] Gumagawa at nagda-download ng SQL backup.
     */
    // app/controllers/AdminController.php

    // Palitan ang buong createBackup() function mo nito

    /**
     * [INAYOS] Gumagawa at nagda-download ng SQL backup.
     * Gumagamit na ngayon ng PDO::quote() para sa tamang pag-escape ng data.
     */
    public function createBackup()
    {
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('admin/dashboard');
        }

        try {
            $db = new Database;
            $tables = [];
            $db->query('SHOW TABLES');
            $results = $db->resultSet();
            foreach ($results as $result) {
                // Tiyaking ang 'audit_log' ay hindi kasama sa backup
                $tableName = current((array)$result);
                if ($tableName != 'audit_log') {
                    $tables[] = $tableName;
                }
            }

            $sql_content = "-- Gravengel DB Backup\n-- Generation Time: " . date('Y-m-d H:i:s') . "\n\n";
            $sql_content .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

            foreach ($tables as $table) {
                $db->query("SHOW CREATE TABLE `$table`");
                $create_table_row = $db->single();

                $sql_content .= "\n-- ----------------------------\n";
                $sql_content .= "-- Table structure for $table\n";
                $sql_content .= "-- ----------------------------\n";
                $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";
                $sql_content .= $create_table_row->{'Create Table'} . ";\n\n";

                $db->query("SELECT * FROM `$table`");
                $rows = $db->resultSet();

                if (!empty($rows)) {
                    $sql_content .= "-- ----------------------------\n";
                    $sql_content .= "-- Records of $table\n";
                    $sql_content .= "-- ----------------------------\n";
                    foreach ($rows as $row) {
                        $sql_content .= "INSERT INTO `$table` VALUES(";
                        $values = [];
                        foreach ((array)$row as $value) {
                            if (is_null($value)) {
                                $values[] = "NULL";
                            } else {
                                // ITO ANG MAHALAGANG PAGBABAGO:
                                $values[] = $db->quote($value);
                            }
                        }
                        $sql_content .= implode(', ', $values) . ");\n";
                    }
                }
            }

            $sql_content .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";

            $filename = 'gravengel_backup_' . date('Y-m-d_H-i-s') . '.sql';

            $this->auditModel->logAction(
                $_SESSION['user']['id'],
                $_SESSION['user']['name'],
                'backup_created',
                'success',
                'Successfully generated backup file: ' . $filename
            );

            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $sql_content;
            exit();
        } catch (Exception $e) {
            $this->auditModel->logAction($_SESSION['user']['id'], $_SESSION['user']['name'], 'backup_created', 'failure', 'Error: ' . $e->getMessage());
            die("Error creating backup: " . $e->getMessage());
        }
    }

    /**
     * [BAGONG FUNCTION] Nagha-handle ng pag-upload at pag-restore ng SQL file.
     */
    public function restoreBackup()
    {
        if ($_SESSION['user']['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('admin/backup');
        }

        $userId = $_SESSION['user']['id'];
        $userName = $_SESSION['user']['name'];

        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['backup_file']['tmp_name'];
            $file_name = $_FILES['backup_file']['name'];

            if (pathinfo($file_name, PATHINFO_EXTENSION) != 'sql') {
                $_SESSION['flash_message'] = 'Error: Invalid file type. Please upload a .sql file.';
                $_SESSION['flash_type'] = 'danger';
                $this->auditModel->logAction($userId, $userName, 'restore_attempted', 'failure', 'Invalid file type: ' . htmlspecialchars($file_name));
                redirect('admin/backup');
                return;
            }

            $db = new Database;
            try {
                $query_buffer = '';
                $file_handle = fopen($file_tmp_path, 'r');

                if ($file_handle) {
                    while (($line = fgets($file_handle)) !== false) {
                        // Laktawan ang mga comments at blank lines
                        if (substr(trim($line), 0, 2) == '--' || trim($line) == '') {
                            continue;
                        }

                        $query_buffer .= $line;

                        // Kung ang linya ay nagtatapos sa semicolon, ito ay isang kumpletong query
                        if (substr(trim($line), -1, 1) == ';') {
                            $db->query($query_buffer);
                            $db->execute();
                            $query_buffer = ''; // I-reset ang buffer para sa susunod na query
                        }
                    }
                    fclose($file_handle);
                }

                $_SESSION['flash_message'] = 'Database has been successfully restored from ' . htmlspecialchars($file_name) . '.';
                $_SESSION['flash_type'] = 'success';
                $this->auditModel->logAction($userId, $userName, 'restore_attempted', 'success', 'Restored from file: ' . htmlspecialchars($file_name));
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $_SESSION['flash_message'] = 'An error occurred during restore. The operation was stopped. Please check the audit log for details.';
                $_SESSION['flash_type'] = 'danger';
                $this->auditModel->logAction($userId, $userName, 'restore_attempted', 'failure', 'Error from ' . htmlspecialchars($file_name) . ': ' . $errorMessage);
            }
        } else {
            $_SESSION['flash_message'] = 'Error uploading file. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            $this->auditModel->logAction($userId, $userName, 'restore_attempted', 'failure', 'File upload error.');
        }

        redirect('admin/backup');
    }
    // app/controllers/AdminController.php

    // Idagdag itong bagong function sa loob ng "class AdminController"

    /**
     * [BAGONG FUNCTION] Gumagawa ng printable report para sa audit trail.
     */
    public function printAuditHistory()
    {
        // Siguraduhing admin lang ang makaka-access
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('admin/dashboard');
        }

        $logs = $this->auditModel->getBackupRestoreLogs();
        $data = [
            'title' => 'Audit Trail Report',
            'logs' => $logs
        ];

        $this->view('admin/print_audit', $data);
    }

    // Sa loob ng app/controllers/AdminController.php

    /**
     * [BAGO] AJAX endpoint para sa Cemetery Map.
     */
    public function getOccupantsForPlot($plot_id = 0)
    {
        header('Content-Type: application/json');
        $plot_id = (int)$plot_id;
        if ($plot_id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Invalid Plot ID.']);
            return;
        }

        $occupants = $this->burialModel->getActiveOccupantsByPlotId($plot_id);

        echo json_encode(['ok' => true, 'occupants' => $occupants]);
    }

    /**
     * [BAGO] AJAX endpoint para kunin ang Grave Level/Type para sa Add Burial form.
     */
    public function getPlotDetailsForForm($plot_id = 0)
    {
        header('Content-Type: application/json');
        $plot_id = (int)$plot_id;
        if ($plot_id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Invalid Plot ID.']);
            return;
        }

        $details = $this->burialModel->getDetailsForBurialForm($plot_id);

        if ($details) {
            echo json_encode(['ok' => true, 'details' => $details]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'Could not retrieve plot details.']);
        }
    }

    // Helper function para mas madali ang pag-log ng actions
    private function logActivity($action, $details = '')
    {
        if (isset($_SESSION['user'])) {
            $this->activityLogModel->log($_SESSION['user']['id'], $_SESSION['user']['name'], $action, $details);
        }
    }

    /* =================== DEVELOPER REPORT =================== */

    /**
     * [BAGO] Nagse-send ng report email sa developer.
     */
    public function sendDeveloperReport()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
            return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Kumuha ng kumpletong user data (kasama ang designation)
        $current_user_id = $_SESSION['user']['id'] ?? null;
        $current_user_details = null;
        if ($current_user_id) {
            // Asumihin na may method ang User model para kunin ang user at ang kanyang staff details
            // Gagamit tayo ng findById para kunin ang basic info
            $current_user_details = $this->userModel->findById($current_user_id);
        }

        $data = [
            'reporter_name'     => trim($_POST['reporter_name'] ?? ''),
            'reporter_email'    => trim($_POST['reporter_email'] ?? ''),
            'contact_number'    => trim($_POST['contact_number'] ?? ''),
            'subject'           => trim($_POST['subject'] ?? ''),
            'message'           => trim($_POST['message'] ?? ''),
            'developer_email'   => trim($_POST['developer_email'] ?? 'cano.orionseal.bsit@gmail.com'),
            
            // Logged-in User Details:
            'current_user'      => $_SESSION['user']['name'] ?? 'Guest',
            'current_user_role' => $_SESSION['user']['role'] ?? 'N/A',
            // Idagdag ang Designation:
            'current_user_designation' => $current_user_details->designation ?? 'N/A', 
            'current_user_id'          => $current_user_details->staff_id ?? 'N/A',
        ];

        if (empty($data['reporter_name']) || empty($data['reporter_email']) || empty($data['subject']) || empty($data['message'])) {
            echo json_encode(['ok' => false, 'message' => 'Please fill in all required fields.']);
            return;
        }

        if (!filter_var($data['reporter_email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'message' => 'Invalid email address format.']);
            return;
        }

        if (!class_exists('EmailHelper')) {
            require_once APPROOT . '/helpers/Email.php';
        }

        // Gamitin ang bagong email template
        $body = $this->view('emails/developer_report_template', $data, true);
        $subject = "[SYSTEM REPORT] {$data['subject']} - From {$data['reporter_name']}";
        
        $emailHelper = new EmailHelper();
        // Ipadala sa developer email, gamit ang reporter name bilang recipient name (optional)
        $email_ok = $emailHelper->sendEmail($data['developer_email'], 'System Developer', $subject, $body);

        if ($email_ok === true) {
            $this->logActivity('send_dev_report', "Sent report: {$data['subject']}");
            echo json_encode(['ok' => true, 'message' => 'Report sent successfully to the development team. Thank you!']);
        } else {
            error_log("Developer Report Email Failed: " . $email_ok);
            echo json_encode(['ok' => false, 'message' => 'Failed to send report. Mailer error.']);
        }
    }
   
}
