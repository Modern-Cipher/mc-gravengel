<?php
// app/controllers/AdminController.php
class AdminController extends Controller
{
    private $userModel;
    private $mapModel;
    private $burialModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user'])) {
            header('Location: ' . URLROOT . '/auth/login'); exit;
        }
        $this->userModel   = $this->model('User');
        $this->mapModel    = $this->model('Map');
        $this->burialModel = $this->model('Burial');

        $currentMethod = $this->params[0] ?? 'dashboard';
        if (($_SESSION['user']['role'] ?? '') !== 'admin' &&
            in_array($currentMethod, ['userAccounts','updateBlock','addStaff','updateStaff'], true)) {
            redirect('admin/dashboard');
        }
    }

    public function index(){ redirect('admin/burialRecords'); }

    /* ---------------- Dashboard / Users / Map ---------------- */
    public function dashboard(){
        $this->view('admin/index', [
            'title'           => 'Admin Dashboard',
            'name'            => $_SESSION['user']['name'] ?? 'Admin',
            'must_change_pwd' => (int)($_SESSION['user']['must_change_pwd'] ?? 0)
        ]);
    }

    public function cemeteryMap(){
        $blocks = $this->mapModel->getAllBlocks();
        $this->view('admin/cemetery_map', ['title'=>'Cemetery Map','blocks'=>$blocks]);
    }

    public function userAccounts(){
        $uid   = $_SESSION['user']['id'];
        $users = $this->userModel->getAllUsersExcluding($uid);
        $this->view('admin/user_accounts', ['title'=>'User Accounts','users'=>$users]);
    }

    public function profile(){
        $uid  = $_SESSION['user']['id'];
        $user = $this->userModel->findById($uid);
        $this->view('admin/profile', ['title'=>'My Profile','user'=>$user]);
    }

    public function updateBlock(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){ redirect('admin/cemeteryMap'); return; }
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $data = [
            'id'         => $_POST['id'],
            'title'      => trim($_POST['title']),
            'offset_x'   => (int)$_POST['offset_x'],
            'offset_y'   => (int)$_POST['offset_y'],
            'modal_rows' => (int)$_POST['modal_rows'],
            'modal_cols' => (int)$_POST['modal_cols']
        ];
        if ($this->mapModel->updateBlock($data)) {
            $_SESSION['flash_message']='Block details saved successfully!';
            $_SESSION['flash_type']='success';
            redirect('admin/cemeteryMap');
        } else {
            die('Something went wrong');
        }
    }

    public function addStaff(){
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
        $temp_password            = substr(bin2hex(random_bytes(8)), 0, 16);
        $data['password_hash']    = password_hash($temp_password, PASSWORD_DEFAULT);
        $data['must_change_pwd']  = 1;

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

    /* ---------- NEW: Update Staff (JSON, no redirect; no-op if nothing changed) ---------- */
    public function updateStaff()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success'=>false,'message'=>'Invalid request method.']); return;
        }

        // sanitize
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        $id    = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Missing user id.']); return; }

        // current
        $current = $this->userModel->findById($id);
        if (!$current) { echo json_encode(['success'=>false,'message'=>'User not found.']); return; }

        // collect fields we allow to update
        $incoming = [
            'first_name'  => trim($_POST['first_name']  ?? ''),
            'last_name'   => trim($_POST['last_name']   ?? ''),
            'username'    => trim($_POST['username']    ?? ''),
            'email'       => trim($_POST['email']       ?? ''),
            'phone'       => trim($_POST['phone']       ?? ''),
            'staff_id'    => trim($_POST['staff_id']    ?? ''),
            'designation' => trim($_POST['designation'] ?? '')
        ];

        // remove empty keys (not provided by JS diff)
        $payload = [];
        foreach ($incoming as $k => $v) {
            if ($v !== '') $payload[$k] = $v;
        }

        // compute differences vs current
        $changes = [];
        foreach ($payload as $k => $v) {
            $old = isset($current->$k) ? (string)$current->$k : '';
            if ($old !== $v) $changes[$k] = $v;
        }

        // no changes? return success without touching DB
        if (empty($changes)) {
            echo json_encode(['success'=>true,'message'=>'No changes detected.']); return;
        }

        // if username or email changed, ensure not taken
        if (isset($changes['username']) || isset($changes['email'])) {
            $checkUsername = $changes['username'] ?? $current->username;
            $existing = $this->userModel->findByUsernameOrEmail($checkUsername);
            // if model method returns same row for same user, allow it
            if ($existing && (int)$existing->id !== $id) {
                echo json_encode(['success'=>false,'message'=>'Username or email is already taken.']); return;
            }
        }

        // perform update (support multiple possible model method names)
        $ok = false;
        if (method_exists($this->userModel, 'updateStaffUser')) {
            $ok = (bool)$this->userModel->updateStaffUser($id, $changes);
        } elseif (method_exists($this->userModel, 'update')) {
            $changes['id'] = $id;
            $ok = (bool)$this->userModel->update($changes);
        } elseif (method_exists($this->userModel, 'updateById')) {
            $ok = (bool)$this->userModel->updateById($id, $changes);
        } else {
            // As a final fallback, fail gracefully so we don't redirect
            echo json_encode(['success'=>false,'message'=>'Update method not available in User model.']); return;
        }

        echo json_encode($ok ? ['success'=>true,'message'=>'User details updated.']
                             : ['success'=>false,'message'=>'Failed to update user.']);
    }

    /* ---------------- Burials (Active list) ---------------- */
    public function burialRecords(){
        $records = $this->burialModel->getAllBurialRecords(); // is_active = 1
        $this->view('admin/burial_records', ['title'=>'Burial Records','records'=>$records]);
    }

    public function addBurial(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $plots = $this->burialModel->getVacantPlotsFromPlots();
            $this->view('admin/add_burial', ['plots'=>$plots]);
            return;
        }

        header('Content-Type: application/json');

        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $burialIdIncoming = trim($_POST['burial_id'] ?? '');
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
            return;
        } else {
            $data['burial_id']          = $burialIdIncoming;
            $data['updated_by_user_id'] = $uid;

            $ok = $this->burialModel->updateBurial($data);
            if (!$ok) { echo json_encode(['ok'=>false,'message'=>'Failed to update']); return; }

            $r = $this->burialModel->findAnyByBurialId($burialIdIncoming); // allow either status
            $transactionId = $r->transaction_id ?? $this->makeTransactionId($r->id ?? 0);

            echo json_encode([
                'ok' => true,
                'message' => 'Updated',
                'burial_id' => $burialIdIncoming,
                'transaction_id' => $transactionId
            ]);
            return;
        }
    }

    private function makeTransactionId($suffixInt): string
    {
        $suffixInt = (int)$suffixInt;
        $seq = str_pad((string)($suffixInt % 1000), 3, '0', STR_PAD_LEFT);
        return date('Ymd') . '-' . $seq;
    }

    /* ---------------- JSON & Print (use findAny so archived works) ---------------- */
    public function burialJson($burial_id){
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
        $this->view('admin/print_burial_form', ['r' => $rec]);
    }
    public function printContract($burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) { echo '<h3 style="padding:16px">Burial record not found.</h3>'; return; }
        $this->view('admin/print_contract', ['r' => $rec]);
    }
    public function printQrTicket($burial_id)
    {
        $rec = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$rec) { echo '<h3 style="padding:16px">Burial record not found.</h3>'; return; }
        $this->view('admin/print_qr_ticket', ['r' => $rec]);
    }

    /* ---------------- Details (used by both pages) ---------------- */
    public function getBurialDetails($burial_id){
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['error'=>'No burial ID']); return; }
        $r = $this->burialModel->findAnyByBurialId($burial_id); // allow archived
        echo json_encode($r);
    }

    /* ---------------- Delete ---------------- */
    public function deleteBurial($burial_id){
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok'=>false,'message'=>'Invalid method']); return;
        }

        $burial_id = trim($burial_id ?? '');
        if ($burial_id === '') {
            echo json_encode(['ok'=>false,'message'=>'Missing burial id']); return;
        }

        $row = $this->burialModel->findAnyByBurialId($burial_id);
        if (!$row) {
            echo json_encode(['ok'=>false,'message'=>'Record not found']); return;
        }

        $ok = $this->burialModel->deleteByBurialId($burial_id);

        if ($ok) {
            if (!empty($row->plot_id) && method_exists($this->burialModel,'markPlotVacant')) {
                $this->burialModel->markPlotVacant((int)$row->plot_id);
            }
            echo json_encode(['ok'=>true,'message'=>'Deleted']);
        } else {
            echo json_encode(['ok'=>false,'message'=>'Failed to delete']);
        }
    }

    /* ====================== ARCHIVE / RESTORE (is_active) ====================== */

    // Archived page
    public function archivedBurials()
    {
        $records = $this->burialModel->getAllBurialRecordsArchived(); // is_active = 0
        $this->view('admin/archived_burials', ['title'=>'Archived Burials','records'=>$records]);
    }

    // Archive record (set is_active = 0)
    public function archiveBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['ok'=>false,'message'=>'No burial ID']); return; }

        $ok = $this->burialModel->archiveByBurialId($burial_id);
        echo json_encode(['ok'=>(bool)$ok, 'message'=>$ok?'Archived':'Archive failed']);
    }

    // Restore record (set is_active = 1)
    public function restoreBurial($burial_id)
    {
        header('Content-Type: application/json');
        if (empty($burial_id)) { echo json_encode(['ok'=>false,'message'=>'No burial ID']); return; }

        $ok = $this->burialModel->restoreByBurialId($burial_id);
        echo json_encode(['ok'=>(bool)$ok, 'message'=>$ok?'Restored':'Restore failed']);
    }

    // Contact Us page (admin)
    public function contact_us()
    {
        $this->view('admin/contact_us');
    }


    public function logsReports()
{
    // Renders the page with tabs & tables (Activity / Transactions)
    $this->view('admin/logs_reports', ['title' => 'Logs & Reports']);
}

public function fetchActivityLogs()
{
    header('Content-Type: application/json');

    $from = $_GET['from'] ?? '';
    $to   = $_GET['to']   ?? '';
    $q    = trim($_GET['q'] ?? '');

    $db = new Database();

    // Build a UNION of consistent "actions" so we don't depend on a non-existent updated_at.
    // a) create burial  (ts = b.created_at)
    // b) update burial  (ts = NULL -> UI shows "timestamp not available")
    // c) user login     (ts = login_at)
    // d) user logout    (ts = logout_at)
    $parts = [];

    // a) create
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

    // b) update (no updated_at column in schema; keep ts NULL to indicate untracked time)
    $parts[] = "
      SELECT
        sd.staff_id AS staff_id,
        u.username  AS username,
        NULL        AS ts,
        CONCAT('Updated Burial Record ', b.burial_id, ' (', mb.title, ' — ', p.plot_number, ') — timestamp not available') AS action_text,
        'update_burial' AS kind
      FROM burials b
      JOIN plots p        ON p.id = b.plot_id
      JOIN map_blocks mb  ON mb.id = p.map_block_id
      LEFT JOIN users u   ON u.id = b.updated_by_user_id
      LEFT JOIN staff_details sd ON sd.user_id = u.id
      WHERE b.updated_by_user_id IS NOT NULL
    ";

    // c) login
    $parts[] = "
      SELECT
        sd.staff_id AS staff_id,
        u.username  AS username,
        us.login_at AS ts,
        'User login' AS action_text,
        'login'      AS kind
      FROM user_sessions us
      JOIN users u        ON u.id = us.user_id
      LEFT JOIN staff_details sd ON sd.user_id = u.id
      WHERE us.login_at IS NOT NULL
    ";

    // d) logout
    $parts[] = "
      SELECT
        sd.staff_id AS staff_id,
        u.username  AS username,
        us.logout_at AS ts,
        'User logout' AS action_text,
        'logout'      AS kind
      FROM user_sessions us
      JOIN users u        ON u.id = us.user_id
      LEFT JOIN staff_details sd ON sd.user_id = u.id
      WHERE us.logout_at IS NOT NULL
    ";

    $sql = "SELECT * FROM (".implode(" UNION ALL ", $parts).") X WHERE 1=1";
    $bind = [];

    if ($from !== '') { $sql .= " AND (ts IS NULL OR DATE(ts) >= :dfrom)"; $bind[':dfrom'] = $from; }
    if ($to   !== '') { $sql .= " AND (ts IS NULL OR DATE(ts) <= :dto)";   $bind[':dto']   = $to;   }

    if ($q !== '') {
        $sql .= " AND (COALESCE(staff_id,'') LIKE :qq OR COALESCE(username,'') LIKE :qq OR COALESCE(action_text,'') LIKE :qq)";
        $bind[':qq'] = "%{$q}%";
    }

    $sql .= " ORDER BY (ts IS NULL), ts DESC";

    $db->query($sql);
    foreach ($bind as $k => $v) $db->bind($k, $v);
    $rows = $db->resultSet();

    echo json_encode(['ok' => true, 'rows' => $rows]);
}

public function fetchTransactionReports()
{
    header('Content-Type: application/json');

    $from = $_GET['from'] ?? '';
    $to   = $_GET['to']   ?? '';
    $q    = trim($_GET['q'] ?? '');

    $db = new Database();

    // One safe, parameterized query. We *do not* touch any model's private $db.
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
        b.payment_amount,
        b.rental_date,
        b.expiry_date,
        CONCAT_WS(' ',
          b.deceased_first_name,
          NULLIF(b.deceased_middle_name,''),
          b.deceased_last_name,
          NULLIF(b.deceased_suffix,'')
        )                           AS deceased_full_name,
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
// === DASHBOARD CARDS (counts only) =========================================
public function dashboardCards()
{
    header('Content-Type: application/json; charset=utf-8');

    try {
        $db = new Database();

        // active burials
        $db->query("SELECT COUNT(*) AS c FROM burials WHERE is_active = 1");
        $active = (int)($db->single()->c ?? 0);

        // expired rentals
        $db->query("SELECT COUNT(*) AS c FROM burials WHERE expiry_date IS NOT NULL AND expiry_date < NOW()");
        $expired = (int)($db->single()->c ?? 0);

        // today's transactions (by rental_date)
        $db->query("SELECT COUNT(*) AS c FROM burials WHERE DATE(rental_date) = CURDATE()");
        $today = (int)($db->single()->c ?? 0);

        // staff accounts
        $db->query("SELECT COUNT(*) AS c FROM users WHERE role = 'staff' AND is_active = 1");
        $staff = (int)($db->single()->c ?? 0);

        echo json_encode([
            'ok' => true,
            'active'  => $active,
            'expired' => $expired,
            'today'   => $today,
            'staff'   => $staff,
        ]);
    } catch (Throwable $e) {
        echo json_encode(['ok' => false, 'message' => 'server_error']);
    }
    exit;
}

// === CALENDAR: RENTAL EXPIRY EVENTS ========================================
public function expiryEvents()
{
    header('Content-Type: application/json; charset=utf-8');

    $from = $_GET['from'] ?? null;
    $to   = $_GET['to']   ?? null;
    if (!$from || !$to) { echo json_encode(['ok'=>true,'events'=>[]]); exit; }

    try {
        $db = new Database();
        $sql = "
          SELECT b.burial_id,
                 b.interment_full_name,
                 b.expiry_date,
                 b.grave_level, b.grave_type,
                 p.plot_number,
                 mb.title AS block_title
          FROM burials b
          JOIN plots p       ON p.id = b.plot_id
          JOIN map_blocks mb ON mb.id = p.map_block_id
          WHERE b.expiry_date IS NOT NULL
            AND b.expiry_date >= :from
            AND b.expiry_date <  :to
        ";
        $db->query($sql);
        $db->bind(':from', $from);
        $db->bind(':to',   $to);
        $rows = $db->resultSet();

        $events = [];
        foreach ($rows as $r) {
            $events[] = [
                'title' => 'Expiry: ' . ($r->interment_full_name ?? '—'),
                'start' => $r->expiry_date,
                'allDay' => false,
                'extendedProps' => [
                    'holder'    => $r->interment_full_name,
                    'burial_id' => $r->burial_id,
                    'block'     => $r->block_title,
                    'plot'      => $r->plot_number,
                    'grave'     => trim(($r->grave_level ?? '') . ' ' . ($r->grave_type ?? '')),
                    'expiry'    => $r->expiry_date,
                ],
            ];
        }

        echo json_encode(['ok'=>true,'events'=>$events]);
    } catch (Throwable $e) {
        echo json_encode(['ok'=>false,'events'=>[]]);
    }
    exit;
}


}
