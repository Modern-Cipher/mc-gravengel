<?php
// app/controllers/StaffController.php
// Full clone of AdminController, but accessible to role 'staff' (and 'admin').
// Routes example: /staff/dashboard, /staff/burialRecords, etc.
// Views reused from admin/* so you don't need to duplicate blades.

class StaffController extends Controller
{
    private $userModel;
    private $mapModel;
    private $burialModel;
    private $renewalModel;
    
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/auth/login'); exit;
        }

        // Allow both admin and staff to access this controller (full features).
        $role = $_SESSION['user']['role'] ?? '';
        if (!in_array($role, ['staff'], true)) {
            header('Location: ' . URLROOT . '/auth/login'); exit;
        }

        $this->userModel    = $this->model('User');
        $this->mapModel     = $this->model('Map');
        $this->burialModel  = $this->model('Burial');
        $this->renewalModel = $this->model('Renewal');

        // IMPORTANT: Unlike AdminController, walang block dito.
        // Staff can call everything; visibility via menu na lang sa UI.
    }

    /* =================== NAV / LANDING =================== */
    public function index() { redirect('staff/burialRecords'); }

    public function dashboard()
    {
        // NOTE: these model helpers must exist — countActive(), countExpired(), countTodayTransactions()
        $this->view('staff/index', [ // reuse admin view
            'title'           => 'Dashboard',
            'name'            => $_SESSION['user']['name'] ?? 'Staff',
            'must_change_pwd' => (int)($_SESSION['user']['must_change_pwd'] ?? 0),
            'metrics'         => [
                'active'   => method_exists($this->burialModel,'countActive')             ? (int)$this->burialModel->countActive()             : 0,
                'expired'  => method_exists($this->burialModel,'countExpired')            ? (int)$this->burialModel->countExpired()            : 0,
                'todayTx'  => method_exists($this->burialModel,'countTodayTransactions')  ? (int)$this->burialModel->countTodayTransactions()  : 0,
                'staff'    => method_exists($this->userModel,'countStaffUsers')           ? (int)$this->userModel->countStaffUsers()           : 0,
            ],
        ]);
    }

    /* =================== PAGES =================== */
    public function cemeteryMap()
    {
        $blocks = $this->mapModel->getAllBlocks();
        $this->view('staff/cemetery_map', ['title'=>'Cemetery Map','blocks'=>$blocks]);
    }

    public function userAccounts()
    {
        $uid   = $_SESSION['user']['id'];
        $users = $this->userModel->getAllUsersExcluding($uid);
        $this->view('staff/user_accounts', ['title'=>'User Accounts','users'=>$users]);
    }

    public function profile()
    {
        $uid  = $_SESSION['user']['id'];
        $user = $this->userModel->findById($uid);
        $this->view('staff/profile', ['title'=>'My Profile','user'=>$user]);
    }

    public function contact_us()
    {
        $this->view('staff/contact_us', ['title'=>'Contact Us']);
    }

    /* =================== MAP =================== */
    public function updateBlock()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){ redirect('staff/cemeteryMap'); return; }

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
            $_SESSION['flash_message']='Block details saved successfully!';
            $_SESSION['flash_type']='success';
            redirect('staff/cemeteryMap');
        } else {
            die('Something went wrong');
        }
    }

    /* =================== USERS =================== */
    public function addStaff()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
            echo json_encode(['success'=>false,'message'=>'Invalid request method.']); return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $data = [
            'first_name'  => trim($_POST['first_name'] ?? ''),
            'last_name'   => trim($_POST['last_name'] ?? ''),
            'username'    => trim($_POST['username'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'phone'       => trim($_POST['phone'] ?? ''),
            'staff_id'    => trim($_POST['staff_id'] ?? ''),
            'designation' => trim($_POST['designation'] ?? ''),
        ];

        if (in_array('', [$data['first_name'],$data['last_name'],$data['username'],$data['email'],$data['staff_id'],$data['designation']], true)){
            echo json_encode(['success'=>false,'message'=>'Please fill in all required fields.']); return;
        }
        if ($this->userModel->findByUsernameOrEmail($data['username'])) {
            echo json_encode(['success'=>false,'message'=>'Username or email is already taken.']); return;
        }

        $temp_password           = substr(bin2hex(random_bytes(8)), 0, 16);
        $data['password_hash']   = password_hash($temp_password, PASSWORD_DEFAULT);
        $data['must_change_pwd'] = 1;

        $user_id = $this->userModel->addStaffUser($data);
        if ($user_id) {
            $emailHelper = new EmailHelper();
            $email_data  = ['full_name'=>$data['first_name'].' '.$data['last_name'], 'temp_password'=>$temp_password];
            $body        = $this->view('emails/welcome_staff', $email_data, true);
            $ok          = $emailHelper->sendEmail($data['email'], $email_data['full_name'], 'Welcome to Plaridel Public Cemetery System!', $body);

            echo json_encode($ok===true
                ? ['success'=>true,'message'=>'Staff account created and welcome email sent!','temp_password'=>$temp_password,'user'=>$email_data]
                : ['success'=>false,'message'=>'Account created, but failed to send welcome email. '.$ok]
            );
        } else {
            echo json_encode(['success'=>false,'message'=>'Failed to create user account.']);
        }
    }

    // JSON diff update; no-op kung walang nabago
    public function updateStaff()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success'=>false,'message'=>'Invalid request method.']); return;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id    = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Missing user id.']); return; }

        $current = $this->userModel->findById($id);
        if (!$current) { echo json_encode(['success'=>false,'message'=>'User not found.']); return; }

        $incoming = [
            'first_name'  => trim($_POST['first_name']  ?? ''),
            'last_name'   => trim($_POST['last_name']   ?? ''),
            'username'    => trim($_POST['username']    ?? ''),
            'email'       => trim($_POST['email']       ?? ''),
            'phone'       => trim($_POST['phone']       ?? ''),
            'staff_id'    => trim($_POST['staff_id']    ?? ''),
            'designation' => trim($_POST['designation'] ?? ''),
        ];
        $payload = [];
        foreach ($incoming as $k => $v) if ($v !== '') $payload[$k] = $v;

        $changes = [];
        foreach ($payload as $k => $v) {
            $old = isset($current->$k) ? (string)$current->$k : '';
            if ($old !== $v) $changes[$k] = $v;
        }
        if (empty($changes)) { echo json_encode(['success'=>true,'message'=>'No changes detected.']); return; }

        if (isset($changes['username']) || isset($changes['email'])) {
            $check = $changes['username'] ?? $current->username;
            $exists = $this->userModel->findByUsernameOrEmail($check);
            if ($exists && (int)$exists->id !== $id) {
                echo json_encode(['success'=>false,'message'=>'Username or email is already taken.']); return;
            }
        }

        $ok = false;
        if (method_exists($this->userModel, 'updateStaffUser')) {
            $ok = (bool)$this->userModel->updateStaffUser($id, $changes);
        } elseif (method_exists($this->userModel, 'update')) {
            $changes['id'] = $id;
            $ok = (bool)$this->userModel->update($changes);
        } elseif (method_exists($this->userModel, 'updateById')) {
            $ok = (bool)$this->userModel->updateById($id, $changes);
        } else {
            echo json_encode(['success'=>false,'message'=>'Update method not available in User model.']); return;
        }

        echo json_encode($ok ? ['success'=>true,'message'=>'User details updated.']
                             : ['success'=>false,'message'=>'Failed to update user.']);
    }

    /* =================== BURIALS =================== */
    public function burialRecords()
    {
        $records = $this->burialModel->getAllBurialRecords(); // is_active = 1
        $this->view('staff/burial_records', ['title'=>'Burial Records','records'=>$records]);
    }

    public function addBurial()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $plots = $this->burialModel->getVacantPlotsFromPlots();
            $this->view('staff/add_burial', ['plots'=>$plots]);
            return;
        }

        header('Content-Type: application/json');
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $burialIdIncoming = trim($_POST['burial_id'] ?? '');
        $interment_email  = trim($_POST['interment_email'] ?? '');

        // Basic optional email validation (format + max length 150)
        if ($interment_email !== '') {
            if (strlen($interment_email) > 150) {
                echo json_encode(['ok'=>false,'message'=>'Interment email must be at most 150 characters.']); return;
            }
            if (!filter_var($interment_email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['ok'=>false,'message'=>'Please enter a valid interment email address.']); return;
            }
        }

        $data = [
            'plot_id'                  => (int)($_POST['plot_id'] ?? 0),
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
            'interment_email'          => ($interment_email === '') ? null : $interment_email, // NEW
            'payment_amount'           => (float)($_POST['payment_amount'] ?? 0),
            'rental_date'              => $_POST['rental_date'] ?: null,
            'expiry_date'              => $_POST['expiry_date'] ?: null,
            'requirements'             => $_POST['requirements'] ?? '',
        ];

        foreach (['plot_id','deceased_first_name','deceased_last_name','date_died','interment_full_name','interment_relationship'] as $k){
            if (empty($data[$k])) { echo json_encode(['ok'=>false,'message'=>'Missing required field: '.$k]); return; }
        }

        $uid = $_SESSION['user']['id'] ?? null;

        if ($burialIdIncoming === '') {
            $data['created_by_user_id'] = $uid;

            $res = $this->burialModel->create($data);
            if (!$res) { echo json_encode(['ok'=>false,'message'=>'Failed to save']); return; }

            $transactionId = $res['transaction_id'] ?? ($this->makeTransactionId($res['insert_id'] ?? 0));
            echo json_encode([
                'ok' => true,
                'message' => 'Saved',
                'burial_id' => $res['burial_id'] ?? null,
                'transaction_id' => $transactionId
            ]);
        } else {
            $data['burial_id']          = $burialIdIncoming;
            $data['updated_by_user_id'] = $uid;

            $ok = $this->burialModel->updateBurial($data);
            if (!$ok) { echo json_encode(['ok'=>false,'message'=>'Failed to update']); return; }

            $r = $this->burialModel->findAnyByBurialId($burialIdIncoming);
            $transactionId = $r->transaction_id ?? $this->makeTransactionId($r->id ?? 0);

            echo json_encode([
                'ok' => true,
                'message' => 'Updated',
                'burial_id' => $burialIdIncoming,
                'transaction_id' => $transactionId
            ]);
        }
    }

    /**
     * Handles updating a burial record from the edit modal.
     * It fetches the current record and merges submitted form data over it
     * to prevent accidental data loss for fields that were not submitted.
     */
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
    
        // 1. Fetch the existing record to serve as a base, preventing data loss
        $existingRecord = $this->burialModel->findAnyByBurialId($burialId);
        if (!$existingRecord) {
            echo json_encode(['ok' => false, 'message' => 'Burial record not found.']);
            return;
        }
    
        // 2. Validate incoming email, if provided
        $interment_email  = trim($_POST['interment_email'] ?? '');
        if ($interment_email !== '' && !filter_var($interment_email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
            return;
        }
    
        // 3. Prepare an array of the submitted data for merging
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
            // The frontend JS sends the requirements as a comma-separated string from a hidden input.
            // If empty, it's an empty string, which is correct.
            'requirements'             => trim($_POST['requirements'] ?? ''),
            'updated_by_user_id'       => $_SESSION['user']['id'] ?? null,
        ];
    
        // 4. Merge the submitted data over the existing record.
        // This ensures any fields not submitted by the form retain their original values.
        $finalData = array_merge((array)$existingRecord, $submittedData);
    
        // 5. Pass the complete, final data to the model for updating.
        if ($this->burialModel->updateBurial($finalData)) {
            echo json_encode(['ok' => true, 'message' => 'Record updated successfully!']);
        } else {
            // The model's execute() returns true on success, false on failure.
            // It can also indicate no rows were affected if the data was identical.
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
        if (!$burial_id) { echo json_encode(['ok'=>false,'message'=>'No id']); return; }
        $r = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$r) { echo json_encode(['ok'=>false,'message'=>'Not found']); return; }
        echo json_encode(['ok'=>true,'data'=>$r]);
    }

    public function printBurialForm($burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) { echo '<h3 style="padding:16px">Burial record not found.</h3>'; return; }
        $this->view('staff/print_burial_form', ['r' => $rec]);
    }
    public function printContract($burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) { echo '<h3 style="padding:16px">Burial record not found.</h3>'; return; }
        $this->view('staff/print_contract', ['r' => $rec]);
    }
    public function printQrTicket($burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) { echo '<h3 style="padding:16px">Burial record not found.</h3>'; return; }
        $this->view('staff/print_qr_ticket', ['r' => $rec]);
    }

    /* ---- Details JSON ---- */
    public function getBurialDetails($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['error'=>'No burial ID']); return; }
        $r = $this->burialModel->findAnyByBurialId($burial_id);
        echo json_encode($r);
    }

    /* ---- Delete / Archive / Restore ----
       * IMPORTANT: Archive/Restore DO NOT change plot status.
       * Only DELETE frees the plot (if you still want that).
    */
    public function deleteBurial($burial_id)
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok'=>false,'message'=>'Invalid method']); return;
        }
        $burial_id = trim($burial_id ?? '');
        if ($burial_id === '') { echo json_encode(['ok'=>false,'message'=>'Missing burial id']); return; }

        $ok = $this->burialModel->deleteByBurialId($burial_id);
        echo json_encode(['ok'=>$ok, 'message'=>$ok?'Deleted':'Failed to delete']);
    }

    public function archivedBurials()
    {
        $records = $this->burialModel->getAllBurialRecordsArchived(); // is_active = 0
        $this->view('staff/archived_burials', ['title'=>'Archived Burials','records'=>$records]);
    }

    public function archiveBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['ok'=>false,'message'=>'No burial ID']); return; }
        $ok = $this->burialModel->archiveByBurialId($burial_id);
        echo json_encode(['ok'=>(bool)$ok, 'message'=>$ok?'Archived':'Archive failed']);
    }

    public function restoreBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['ok'=>false,'message'=>'No burial ID']); return; }
        $ok = $this->burialModel->restoreByBurialId($burial_id);
        echo json_encode(['ok'=>(bool)$ok, 'message'=>$ok?'Restored':'Restore failed']);
    }

    /* =================== LOGS & REPORTS =================== */
    public function logsReports()
    {
        $this->view('staff/logs_reports', ['title' => 'Logs & Reports']);
    }

    public function fetchActivityLogs()
    {
        header('Content-Type: application/json');

        $from = $_GET['from'] ?? '';
        $to   = $_GET['to']   ?? '';
        $q    = trim($_GET['q'] ?? '');

        $db = new Database();
        $parts = [];

        // Create
        $parts[] = "
          SELECT
            sd.staff_id AS staff_id,
            u.username  AS username,
            b.created_at AS ts,
            CONCAT('Added Burial Record ', b.burial_id, ' for ', mb.title, ' — ', p.plot_number) AS action_text,
            'create_burial' AS kind
          FROM burials b
          JOIN plots p        ON p.id = b.plot_id
          JOIN map_blocks mb  ON mb.id = p.map_block_id
          LEFT JOIN users u   ON u.id = b.created_by_user_id
          LEFT JOIN staff_details sd ON sd.user_id = u.id
          WHERE 1=1
        ";

        // Update (no updated_at in schema)
        $parts[] = "
          SELECT
            sd.staff_id AS staff_id,
            u.username  AS username,
            NULL AS ts,
            CONCAT('Updated Burial Record ', b.burial_id, ' (', mb.title, ' — ', p.plot_number, ') — timestamp not available') AS action_text,
            'update_burial' AS kind
          FROM burials b
          JOIN plots p        ON p.id = b.plot_id
          JOIN map_blocks mb  ON mb.id = p.map_block_id
          LEFT JOIN users u   ON u.id = b.updated_by_user_id
          LEFT JOIN staff_details sd ON sd.user_id = u.id
          WHERE b.updated_by_user_id IS NOT NULL
        ";

        // Login
        $parts[] = "
          SELECT
            sd.staff_id AS staff_id,
            u.username  AS username,
            us.login_at AS ts,
            'User login' AS action_text,
            'login' AS kind
          FROM user_sessions us
          JOIN users u        ON u.id = us.user_id
          LEFT JOIN staff_details sd ON sd.user_id = u.id
          WHERE us.login_at IS NOT NULL
        ";

        // Logout
        $parts[] = "
          SELECT
            sd.staff_id AS staff_id,
            u.username  AS username,
            us.logout_at AS ts,
            'User logout' AS action_text,
            'logout' AS kind
          FROM user_sessions us
          JOIN users u        ON u.id = us.user_id
          LEFT JOIN staff_details sd ON sd.user_id = u.id
          WHERE us.logout_at IS NOT NULL
        ";

        $sql  = "SELECT * FROM (".implode(" UNION ALL ", $parts).") X WHERE 1=1";
        $bind = [];

        if ($from !== '') { $sql .= " AND (ts IS NULL OR DATE(ts) >= :dfrom)"; $bind[':dfrom'] = $from; }
        if ($to   !== '') { $sql .= " AND (ts IS NULL OR DATE(ts) <= :dto)";   $bind[':dto']   = $to;   }

        if ($q !== '') {
            $sql .= " AND (COALESCE(staff_id,'') LIKE :qq OR COALESCE(username,'') LIKE :qq OR COALESCE(action_text,'') LIKE :qq)";
            $bind[':qq'] = "%{$q}%";
        }

        $sql .= " ORDER BY (ts IS NULL), ts DESC";

        $db->query($sql);
        foreach ($bind as $k=>$v) $db->bind($k,$v);
        $rows = $db->resultSet();

        echo json_encode(['ok'=>true,'rows'=>$rows]);
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
        if ($from !== '') { $sql .= " AND DATE(b.rental_date) >= :rfrom"; $bind[':rfrom'] = $from; }
        if ($to   !== '') { $sql .= " AND DATE(b.rental_date) <= :rto";   $bind[':rto']   = $to;   }

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
        foreach ($bind as $k=>$v) $db->bind($k,$v);
        $rows = $db->resultSet();

        echo json_encode(['ok'=>true,'rows'=>$rows]);
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

            echo json_encode(['ok'=>true, 'active'=>$active, 'expired'=>$expired, 'today'=>$today, 'staff'=>$staff]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false, 'message'=>'Failed to load cards']);
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

    public function renewals() {
        $all_active_burials = $this->renewalModel->getBurialsForRenewal(); 
        $history = $this->renewalModel->getRenewalHistory();
        
        $data = [
            'title' => 'Renewals',
            'all_burials' => $all_active_burials, 
            'history' => $history
        ];
        
        $this->view('staff/renewals', $data);
    }

    public function processRenewal() {
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

                $email_status = ($email_ok === true) ? 'Sent successfully.' : 'Failed: '.$email_ok;
                $this->renewalModel->updateEmailStatus($result['transaction_id'], $email_status);
            }

            echo json_encode([
                'ok' => true, 
                'message' => 'Renewal successful! Rental period updated.', 
                'email_status' => $email_status,
                'burial_id' => $burial->burial_id
            ]);

        } else {
            echo json_encode(['ok' => false, 'message' => 'Failed to process renewal in database.']);
        }
    }

    public function processVacate() {
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
            echo json_encode(['success'=>false,'message'=>'Invalid method']); return;
        }

        $id        = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);

        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Missing user id']); return; }

        $ok = $this->userModel->toggleUserActiveStatus($id, $is_active);

        echo json_encode($ok
            ? ['success'=>true,'message'=>'User status updated.']
            : ['success'=>false,'message'=>'Failed to update user status.']
        );
    }

    public function resetPassword()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']); return;
        }
    
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $userId = $_POST['user_id'] ?? null;
        $email  = $_POST['email'] ?? null;
    
        if (!$userId || !$email) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']); return;
        }
    
        try {
            $db = new Database(); 
    
            // 1. Fetch user to get name and ID
            $db->query("SELECT id, email, first_name, last_name, role FROM users WHERE id = :id LIMIT 1");
            $db->bind(':id', $userId);
            $user = $db->single();
    
            // Validation: Check user, email, and ensure the role is 'staff'
            if (!$user || strcasecmp($user->email, $email) !== 0 || $user->role !== 'staff') {
                echo json_encode(['success' => false, 'message' => 'User not authorized for password reset.']); return;
            }
    
            // 2. TOKEN GENERATION (RAW token)
            $rawToken = bin2hex(random_bytes(32)); 
            $expires  = (new DateTime('+2 hours', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');
            
            // Delete old tokens
            $db->query('DELETE FROM password_resets WHERE user_id = :user_id OR expires_at < NOW()');
            $db->bind(':user_id', $userId);
            $db->execute();
            
            // Insert the RAW token
            $db->query('INSERT INTO password_resets (user_id, token, expires_at) VALUES (:u, :t, :e)');
            $db->bind(':u', $userId);
            $db->bind(':t', $rawToken); // RAW token
            $db->bind(':e', $expires);
            $db->execute();
    
            // 3. Set the final link
            $reset_link = URLROOT . "/auth/resetPassword?token={$rawToken}"; 
            
            // 4. Prepare data for the RE-USED TEMPLATE (emails/reset_password)
            $email_data_payload = [
                'full_name'  => $user->first_name, 
                'reset_link' => $reset_link,       
            ];
            
            if (!class_exists('EmailHelper')) { 
                require_once APPROOT . '/helpers/Email.php'; 
            }
            
            $body = $this->view('emails/reset_password', ['data' => $email_data_payload], true); 
            $subject = 'Password Reset Request - Plaridel Public Cemetery System';
    
            $emailHelper = new EmailHelper();
            $recipient_name = $user->first_name . ' ' . $user->last_name; 
            $sent = $emailHelper->sendEmail($user->email, $recipient_name, $subject, $body);
    
            if ($sent !== true) {
                error_log("Failed to send reset email: " . $sent);
                echo json_encode(['success' => false, 'message' => 'Failed to send email. Mailer Error: ' . $sent]); return;
            }
    
            echo json_encode(['success' => true, 'message' => 'A password reset link was emailed.']);
    
        } catch (Throwable $e) {
            error_log("Unexpected resetPassword error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Unexpected error. Please check server logs.']);
        }
    }



    // Pang-kuha ng recent activity ng staff
    public function myActivity() {
        header('Content-Type: application/json');
        $user_id = (int)$_SESSION['user']['id'];

        $db = new Database();
        $parts = [];

        // Magdagdag dito ng mga queries para sa activity ng staff, katulad ng sa AdminController
        // Halimbawa:
        $parts[] = "
          SELECT
            us.login_at AS ts,
            'Logged in to the system' AS action_text,
            'login' AS kind
          FROM user_sessions us
          WHERE us.user_id = :user_id AND us.login_at IS NOT NULL
        ";

        // Maaari kang magdagdag ng iba pang activities tulad ng pag-add ng records, etc.

        $sql  = "SELECT * FROM (".implode(" UNION ALL ", $parts).") X WHERE DATE(X.ts) = CURDATE() ORDER BY X.ts DESC LIMIT 50";

        $db->query($sql);
        $db->bind(':user_id', $user_id);

        try {
            $rows = $db->resultSet();
            echo json_encode(['ok'=>true, 'rows'=>$rows]);
        } catch (Exception $e) {
            error_log("Staff myActivity Error: " . $e->getMessage());
            echo json_encode(['ok'=>false, 'rows' => [], 'message' => 'A database error occurred.']);
        }
    }


    // Idagdag itong function sa loob ng StaffController class

public function printRenewalHistory()
{
    $history = $this->renewalModel->getRenewalHistory();
    $data = [
        'title' => 'Renewal History Report',
        'history' => $history
    ];
    $this->view('staff/print_renewals', $data);
}


// app/controllers/StaffController.php

// Idagdag itong dalawang functions sa loob ng "class StaffController"

    /**
     * Endpoint para sa data ng rental status chart.
     */
    public function getDashboardChartData() {
        header('Content-Type: application/json');
        
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

    /**
     * Endpoint para sa data ng financial chart.
     */
    public function getFinancialChartData() {
        header('Content-Type: application/json');

        if (!method_exists($this->burialModel, 'getDailyTransactionTotals')) {
            echo json_encode(['ok' => false, 'message' => 'Required model method is missing.']);
            return;
        }

        try {
            $dailyData = $this->burialModel->getDailyTransactionTotals(7);
            
            $labels = array_map(function($date) {
                return date('M d', strtotime($date));
            }, array_keys($dailyData));

            $data = array_values($dailyData);

            echo json_encode(['ok' => true, 'labels' => $labels, 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'Failed to fetch financial data.']);
        }
    }


}
