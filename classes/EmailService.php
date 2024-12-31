<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/env.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $pdo;
    private $mail;

    public function __construct() {
        $this->pdo = getConnection();
        $this->initializeMailer();
    }

    private function initializeMailer() {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = getenv('SMTP_SERVER');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = getenv('EMAIL_USER');
        $this->mail->Password = getenv('EMAIL_PASSWORD');
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = getenv('SMTP_PORT');
    }

    private function generateTrackingId() {
        return bin2hex(random_bytes(16));
    }

    private function getTrackingPixel($trackingId) {
        $trackingUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/api/track_email.php?tracking_id=' . urlencode($trackingId);
        return '<img src="' . $trackingUrl . '" alt="" width="1" height="1" style="display:none;">';
    }

    public function sendBirthdayEmail($recipientName, $recipientEmail, $templateId, $message, $cardImagePath = null) {
        try {
            // Generate tracking ID
            $trackingId = $this->generateTrackingId();

            // Prepare email
            $this->mail->setFrom(getenv('EMAIL_USER'), 'Birthday Cards');
            $this->mail->addAddress($recipientEmail, $recipientName);
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Happy Birthday!';

            // Add tracking pixel to email body
            $emailBody = $message;
            if ($cardImagePath) {
                $emailBody .= '<br><img src="cid:birthday-card" style="max-width: 100%;">';
                $this->mail->addEmbeddedImage($cardImagePath, 'birthday-card', 'birthday-card.png');
            }
            $emailBody .= $this->getTrackingPixel($trackingId);

            $this->mail->Body = $emailBody;
            $this->mail->AltBody = strip_tags($message);

            // Send email
            $this->mail->send();

            // Log the email
            $stmt = $this->pdo->prepare("
                INSERT INTO email_logs 
                (recipient_name, recipient_email, template_id, tracking_id, action, created_at) 
                VALUES 
                (?, ?, ?, ?, 'sent', NOW())
            ");
            $stmt->execute([$recipientName, $recipientEmail, $templateId, $trackingId]);

            // Log activity
            $stmt = $this->pdo->prepare("
                INSERT INTO activity_logs 
                (action, details) 
                VALUES 
                ('email_sent', ?)
            ");
            $details = json_encode([
                'recipient' => $recipientEmail,
                'template_id' => $templateId,
                'tracking_id' => $trackingId
            ]);
            $stmt->execute(['email_sent', $details]);

            return true;
        } catch (Exception $e) {
            error_log("Failed to send email: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEmailStatus($trackingId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM email_logs 
            WHERE tracking_id = ?
        ");
        $stmt->execute([$trackingId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
