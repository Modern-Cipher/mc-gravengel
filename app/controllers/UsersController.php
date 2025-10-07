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
    
    /**
     * Handles both profile detail updates and image uploads from a single form submission.
     */
    public function updateProfile() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // --- 1. Update Text Details ---
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

        $details_updated = $this->userModel->updateProfile($data);
        if ($details_updated) {
            // Update session name immediately so the UI reflects the change without a full re-login
            $_SESSION['user']['name'] = $data['first_name'] . ' ' . $data['last_name'];
        }

        // --- 2. Handle Image Upload ---
        $image_message = '';
        $filepath = null;
        $image_update_success = false;
        
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $user_id = $_SESSION['user']['id'];
            $file = $_FILES['profile_image'];

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
            $random_chars = substr(bin2hex(random_bytes(3)), 0, 6); // 3 bytes -> 6 hex characters
            $new_filename = str_replace(' ', '_', $original_name) . '_' . $random_chars . '.' . $extension;
            $target_file = $target_dir . $new_filename;

            $currentUser = $this->userModel->findById($user_id);
            $old_image = $currentUser->profile_image ?? null;

            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                if ($this->userModel->updateProfileImage($user_id, $new_filename)) {
                    // On successful DB update, delete the old image
                    if ($old_image && file_exists($target_dir . $old_image)) {
                        unlink($target_dir . $old_image);
                    }
                    $image_message = 'Image uploaded successfully.';
                    // Important: The filepath must be relative to the webroot for the browser
                    $filepath = URLROOT . '/public/' . $target_file;
                    $image_update_success = true;
                } else {
                     $image_message = 'Image uploaded but failed to update database record.';
                }
            } else {
                $image_message = 'Failed to move uploaded file.';
            }
        }
        
        // --- 3. Final JSON Response ---
        if ($details_updated || $image_update_success) {
            $final_message = 'Profile updated successfully.';
            if (!empty($image_message) && !$image_update_success) {
                // If details saved but image failed, report the image error
                $final_message = 'Details saved. However, ' . $image_message;
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $final_message,
                'filepath' => $filepath // This will be null if no new image was uploaded
            ]);
        } else {
            // This case handles when nothing was changed or an error occurred.
            echo json_encode(['success' => false, 'message' => 'No changes were detected or the update failed.']);
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
}