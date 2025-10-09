<?php
class PagesController extends Controller {
    public function __construct(){
      
    }
    
    public function index(){
        $data = [
            'title' => 'MC-Gravengel',
            'description' => 'Smart Records. Sacred Grounds.'
        ];
       
        $this->view('pages/index', $data);
    }

    public function qr_scanner() {
        $this->view('pages/qr_scanner');
    }

    /**
     * Nagha-handle ng contact form submission.
     * Ito lang dapat ang function na ito sa loob ng class.
     */
    public function sendContactMessage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            return;
        }

        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'subject' => trim($_POST['subject'] ?? 'No Subject'),
            'message' => trim($_POST['message'] ?? '')
        ];

        // Basic validation
        if (empty($data['name']) || empty($data['email']) || empty($data['message'])) {
            echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
            return;
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
            return;
        }

        // Ihanda ang email
        $recipient_email = 'cano.orionseal.bsit@gmail.com'; // **PALITAN MO ITO** ng email kung saan mo gustong matanggap ang mga inquiry
        $recipient_name = 'Gravengel Administrator';
        $email_subject = 'New Contact Form Message: ' . $data['subject'];
        
        // I-load ang email body mula sa template
        $body_html = $this->view('emails/contact_form_submission', $data, true);

        // Gamitin ang EmailHelper para ipadala
        if (!class_exists('EmailHelper')) {
            // Siguraduhing na-load ang helper kung sakaling wala ito sa bootstrap
            require_once APPROOT . '/helpers/Email.php';
        }
        $emailHelper = new EmailHelper();
        $result = $emailHelper->sendEmail($recipient_email, $recipient_name, $email_subject, $body_html);

        if ($result === true) {
            echo json_encode(['success' => true, 'message' => 'Your message has been sent successfully! We will get back to you shortly.']);
        } else {
            // Ipakita ang error mula sa EmailHelper
            echo json_encode(['success' => false, 'message' => 'Failed to send message. ' . $result]);
        }
    }
}