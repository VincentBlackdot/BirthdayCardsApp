<?php
require_once '../config/database.php';

// Generate a 1x1 transparent GIF
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

// Log the email open if tracking ID is provided
if (isset($_GET['id'])) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $trackingId = $_GET['id'];
        
        // Update email_logs table
        $stmt = $conn->prepare("UPDATE email_logs SET opened = 1, opened_at = NOW() WHERE tracking_id = ? AND opened = 0");
        $stmt->bind_param("s", $trackingId);
        $stmt->execute();

    } catch (Exception $e) {
        // Silently fail - we don't want to show errors to the email recipient
        error_log('Email tracking error: ' . $e->getMessage());
    }
}
?>
