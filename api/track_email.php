<?php
require_once __DIR__ . '/../config/database.php';

header("Content-Type: image/gif");
// Return a 1x1 transparent GIF
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

if (isset($_GET['tracking_id'])) {
    $tracking_id = $_GET['tracking_id'];
    
    try {
        $pdo = getConnection();
        
        // Update the email_logs table
        $stmt = $pdo->prepare("
            UPDATE email_logs 
            SET opened = 1, 
                opened_at = NOW() 
            WHERE tracking_id = ? 
            AND opened = 0
        ");
        $stmt->execute([$tracking_id]);

        // Log the activity
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs 
            (action, details, ip_address, user_agent) 
            VALUES 
            ('email_opened', ?, ?, ?)
        ");
        
        $details = json_encode([
            'tracking_id' => $tracking_id,
            'opened_at' => date('Y-m-d H:i:s')
        ]);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt->execute(['email_opened', $details, $ip, $userAgent]);
        
    } catch (PDOException $e) {
        // Silently fail - we don't want to show errors in the tracking pixel
        error_log("Email tracking error: " . $e->getMessage());
    }
}
?>
