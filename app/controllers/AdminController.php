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
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }
        $this->userModel = $this->model('User');
        $this->mapModel = $this->model('Map');
        $this->burialModel = $this->model('Burial');
        
        $currentMethod = $this->params[0] ?? 'dashboard';
        
        if ($_SESSION['user']['role'] !== 'admin' &&
            in_array($currentMethod, ['userAccounts', 'updateBlock', 'addStaff'])) {
            redirect('admin/dashboard');
        }
    }

    public function dashboard()
    {
        $this->view('admin/index', [
            'title'            => 'Admin Dashboard',
            'name'             => $_SESSION['user']['name'] ?? 'Admin',
            'must_change_pwd'  => (int)($_SESSION['user']['must_change_pwd'] ?? 0)
        ]);
    }

    public function cemeteryMap()
    {
        $blocks = $this->mapModel->getAllBlocks();

        $data = [
            'title'  => 'Cemetery Map',
            'blocks' => $blocks
        ];
        $this->view('admin/cemetery_map', $data);
    }
    
    public function userAccounts()
    {
        $current_user_id = $_SESSION['user']['id'];
        $users = $this->userModel->getAllUsersExcluding($current_user_id);
        $data = [
            'title' => 'User Accounts',
            'users' => $users
        ];
        $this->view('admin/user_accounts', $data);
    }

    public function profile()
    {
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->findById($user_id);

        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];
        $this->view('admin/profile', $data);
    }

    public function updateBlock() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $_POST['id'],
                'title' => trim($_POST['title']),
                'offset_x' => (int)$_POST['offset_x'],
                'offset_y' => (int)$_POST['offset_y'],
                'modal_rows' => (int)$_POST['modal_rows'],
                'modal_cols' => (int)$_POST['modal_cols']
            ];

            if ($this->mapModel->updateBlock($data)) {
                $_SESSION['flash_message'] = 'Block details saved successfully!';
                $_SESSION['flash_type'] = 'success';
                redirect('admin/cemeteryMap');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('admin/cemeteryMap');
        }
    }

    public function addStaff()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
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
        
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['username']) || empty($data['email']) || empty($data['staff_id']) || empty($data['designation'])) {
             echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
             return;
        }

        if ($this->userModel->findByUsernameOrEmail($data['username'])) {
             echo json_encode(['success' => false, 'message' => 'Username or email is already taken.']);
             return;
        }

        $temp_password = substr(bin2hex(random_bytes(8)), 0, 16);
        $data['password_hash'] = password_hash($temp_password, PASSWORD_DEFAULT);
        $data['must_change_pwd'] = 1;

        $user_id = $this->userModel->addStaffUser($data);
        if ($user_id) {
            $emailHelper = new EmailHelper();
            $email_data = [
                'full_name' => $data['first_name'] . ' ' . $data['last_name'],
                'temp_password' => $temp_password,
            ];
            $email_body = $this->view('emails/welcome_staff', $email_data, true);
            
            $email_result = $emailHelper->sendEmail($data['email'], $email_data['full_name'], 'Welcome to Plaridel Public Cemetery System!', $email_body);

            if ($email_result === true) {
                echo json_encode(['success' => true, 'message' => 'Staff account created and welcome email sent!', 'temp_password' => $temp_password, 'user' => $email_data]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Account created, but failed to send welcome email. ' . $email_result]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create user account.']);
        }
    }

    public function burialRecords() {
        $records = $this->burialModel->getAllBurialRecords();
        $data = [
            'title'   => 'Burial Records',
            'records' => $records
        ];
        $this->view('admin/burial_records', $data);
    }
    
    public function addBurial() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            $data = [
                'plot_id'                  => trim($_POST['plot_id']),
                'deceased_first_name'      => trim($_POST['deceased_first_name']),
                'deceased_middle_name'     => trim($_POST['deceased_middle_name']),
                'deceased_last_name'       => trim($_POST['deceased_last_name']),
                'deceased_suffix'          => trim($_POST['deceased_suffix']),
                'age'                      => trim($_POST['age']),
                'sex'                      => trim($_POST['sex']),
                'date_born'                => trim($_POST['date_born']),
                'date_died'                => trim($_POST['date_died']),
                'cause_of_death'           => trim($_POST['cause_of_death']),
                'grave_level'              => trim($_POST['grave_level']),
                'grave_type'               => trim($_POST['grave_type']),
                'interment_full_name'      => trim($_POST['interment_full_name']),
                'interment_relationship'   => trim($_POST['interment_relationship']),
                'interment_contact_number' => trim($_POST['interment_contact_number']),
                'interment_address'        => trim($_POST['interment_address']),
                'payment_amount'           => trim($_POST['payment_amount']),
                'rental_date'              => trim($_POST['rental_date']),
                'expiry_date'              => trim($_POST['expiry_date']),
                'requirements'             => isset($_POST['requirements']) ? implode(', ', $_POST['requirements']) : '',
                'created_by_user_id'       => $_SESSION['user']['id']
            ];
            
            $last_id = $this->burialModel->lastInsertId();
            $data['burial_id'] = 'B-' . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);

            if ($this->burialModel->addBurial($data)) {
                $this->mapModel->updatePlotStatus($data['plot_id'], 'occupied');
                $_SESSION['flash_message'] = 'New burial record added successfully!';
                $_SESSION['flash_type'] = 'success';
                redirect('admin/burialRecords');
            } else {
                die('Something went wrong');
            }
        } else {
            $data = [
                'title' => 'Add New Burial',
                'plots' => $this->mapModel->getVacantPlots()
            ];
            $this->view('admin/add_burial', $data);
        }
    }

   public function getBurialDetails($burial_id) {
        header('Content-Type: application/json');
        if (empty($burial_id)) {
            echo json_encode(['error' => 'No burial ID provided']);
            return;
        }

        $burialDetails = $this->burialModel->findByBurialId($burial_id);
        echo json_encode($burialDetails);
    }
}