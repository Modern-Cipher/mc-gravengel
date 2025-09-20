<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    public function sendEmail($recipient_email, $recipient_name, $subject, $body_html) {
        $mail = new PHPMailer(true);

        try {
            //Server settings (gamit ang Gmail SMTP)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'cano.orionseal.bsit@gmail.com'; // Iyong Gmail Email
            $mail->Password   = 'okwh qaex tsaa bfmr'; // Iyong App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('mdctechservices@gmail.com', 'PLARIDEL PUBLIC CEMETERY');
            $mail->addAddress($recipient_email, $recipient_name);

            //Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body_html;
            $mail->AltBody = strip_tags($body_html);

            $mail->send();
            return true;
        } catch (Exception $e) {
            // BINAGO: Ibalik ang eksaktong error message
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return "Mailer Error: {$mail->ErrorInfo}";
        }
    }
}