<?php
class StaffController extends Controller
{
    private $userModel;
    private $mapModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }
        $this->userModel = $this->model('User');
        $this->mapModel = $this->model('Map');
    }

    public function index()
    {
        $this->view('staff/index', [
            'title'            => 'Staff Dashboard',
            'name'             => $_SESSION['user']['name'] ?? 'Staff',
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
        $this->view('staff/cemetery_map', $data);
    }
    
    public function profile()
    {
        $user_id = $_SESSION['user']['id'];
        $user = $this->userModel->findById($user_id);

        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];
        $this->view('staff/profile', $data);
    }
}