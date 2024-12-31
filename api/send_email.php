<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../classes/ActivityLogger.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

// Initialize Database and Logger
$database = new Database();
$db = $database->connect();
$logger = new ActivityLogger($db);

// Load environment variables
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    throw new Exception('.env file not found');
}

header('Content-Type: application/json');

try {
    // Get POST data
    $raw_input = file_get_contents('php://input');
    
    if (empty($raw_input)) {
        throw new Exception('No input data received');
    }
    
    $data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['name']) || empty($data['email']) || empty($data['cardImage'])) {
        throw new Exception('Name, email and card image are required');
    }

    $name = $data['name'];
    $email = $data['email'];
    $customNote = $data['custom_note'] ?? '';
    $cardImage = $data['cardImage'];

    // Decode base64 image
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $cardImage));
    
    // Generate a unique filename
    $filename = 'birthday_card_' . time() . '.png';
    $filepath = __DIR__ . '/../uploads/' . $filename;
    
    // Ensure uploads directory exists
    $uploadsDir = __DIR__ . '/../uploads';
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }
    
    // Save the image temporarily
    if (!file_put_contents($filepath, $imageData)) {
        throw new Exception('Failed to save card image');
    }

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = $env['SMTP_SERVER'];
    $mail->Port = (int)$env['SMTP_PORT'];
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Username = $env['EMAIL_USER'];
    $mail->Password = $env['EMAIL_PASSWORD'];

    // Set timeout and keep alive
    $mail->Timeout = 60;
    $mail->SMTPKeepAlive = true;

    // Sender and recipient
    $mail->setFrom($env['EMAIL_USER'], 'Birthday Cards');
    $mail->addAddress($email, $name);

    // Add the card as an attachment
    $mail->addAttachment($filepath, 'birthday_card.png');
    
    // Set email content
    $mail->isHTML(true);
    $mail->Subject = "Happy Birthday!";
    $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; font-size: 16px; line-height: 1.6;'>
            <h1 style='color: #333; font-size: 24px;'>Happy Birthday!</h1>
            <p style='font-size: 18px;'>Dear $name,</p>
            <p style='font-size: 18px;'>Someone special has sent you a birthday card! Please find it attached to this email.</p>
            " . ($customNote ? "<p style='font-size: 18px; font-style: italic;'>Personal Note: $customNote</p>" : "") . "
            <p style='font-size: 18px;'>Best wishes,<br>Birthday Cards App</p>
        </body>
        </html>
    ";

    // Send email
    if (!$mail->send()) {
        throw new Exception('Email could not be sent: ' . $mail->ErrorInfo);
    }

    // Log the successful email send
    $logger->log('email_sent', [
        'recipient_name' => $name,
        'recipient_email' => $email,
        'custom_note' => !empty($customNote)
    ]);

    // Clean up the temporary file
    @unlink($filepath);

    echo json_encode([
        'success' => true,
        'message' => 'Birthday card sent successfully!'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
