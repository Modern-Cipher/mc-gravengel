<?php
class UsersController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['user']['id'])) {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }
    }

    public function profile() {
        $user = $this->userModel->findById($_SESSION['user']['id']);
        $data = [
            'title' => 'My Profile',
            'user' => $user
        ];
        $this->view('users/profile', $data);
    }
    
    public function updateDetails() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $_SESSION['user']['id'],
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'staff_id' => trim($_POST['staff_id']),
                'designation' => trim($_POST['designation']),
            ];

            if ($this->userModel->updateProfile($data)) {
                $_SESSION['user']['name'] = $data['first_name'] . ' ' . $data['last_name'];
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Something went wrong.']);
            }
        }
    }

    public function changePassword() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
                 echo json_encode(['success' => false, 'message' => 'Please fill out all fields.']);
                return;
            }
            if (strlen($_POST['new_password']) < 6) {
                echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters.']);
                return;
            }
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
                return;
            }
            
            $data = [
                'id' => $_SESSION['user']['id'],
                'current_password' => $_POST['current_password'],
                'new_password_hash' => password_hash($_POST['new_password'], PASSWORD_DEFAULT),
            ];

            if ($this->userModel->updatePassword($data)) {
                echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
            }
        }
    }

    public function uploadImage() {
        header('Content-Type: application/json');
        if (isset($_FILES['profile_image'])) {
            $user_id = $_SESSION['user']['id'];
            $file = $_FILES['profile_image'];

            // VALIDATION PARA SA FILE TYPE (PNG/JPEG LANG)
            $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $allowed_types = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PNG and JPG/JPEG are allowed.']);
                return;
            }
            
            $target_dir = "img/profiles/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $original_name = pathinfo($file["name"], PATHINFO_FILENAME);
            $random_chars = substr(bin2hex(random_bytes(4)), 0, 6);
            $new_filename = str_replace(' ', '_', $original_name) . '_' . $random_chars . '.' . $extension;
            $target_file = $target_dir . $new_filename;

            $currentUser = $this->userModel->findById($user_id);
            $old_image = $currentUser->profile_image ?? null;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                if ($this->userModel->updateProfileImage($user_id, $new_filename)) {
                    if ($old_image && file_exists($target_dir . $old_image)) {
                        unlink($target_dir . $old_image);
                    }
                    echo json_encode(['success' => true, 'message' => 'Image uploaded!', 'filepath' => URLROOT . '/public/' . $target_file]);
                } else {
                     echo json_encode(['success' => false, 'message' => 'Failed to update database.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No file was uploaded.']);
        }
    }
}