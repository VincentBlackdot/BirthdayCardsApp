<?php
require_once 'config/database.php';
require_once 'config/env.php';
require_once 'api/generate_card.php';
require_once 'classes/EmailService.php';
require 'vendor/autoload.php';

try {
    // Load environment variables
    loadEnv();

    // Test data
    $name = "Test User";
    $email = "firstvincevsiame1@gmail.com";
    $customNote = "This is a test birthday card!";

    // Connect to database
    $db = new Database();
    $conn = $db->connect();

    // Get a template
    $stmt = $conn->prepare("SELECT * FROM templates ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();

    if (!$template) {
        throw new Exception('No templates found. Please import the database.sql file first.');
    }

    // Replace [NAME] placeholder and add custom note
    $message = str_replace('[NAME]', $name, $template['message']);
    if (!empty($customNote)) {
        $message .= "\n\n" . $customNote;
    }

    // Generate card image
    $cardImage = generateCardImage($message, $template['background'], $template['design']);
    echo "Card image generated at: " . $cardImage . "\n";

    // Send email using EmailService
    $emailService = new EmailService();
    $emailService->sendBirthdayEmail($name, $email, $template['id'], $message, $cardImage);
    
    echo "Email sent successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    error_log("Email sending error: " . $e->getMessage());
}
