<?php
class ActivityLogger {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->db = new Database();
    }

    public function logActivity($action, $details = []) {
        $conn = $this->db->connect();
        
        // Get client information
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Convert details to JSON
        $detailsJson = json_encode($details);
        
        $stmt = $conn->prepare("
            INSERT INTO activity_logs (action, details, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->bind_param("ssss", $action, $detailsJson, $ipAddress, $userAgent);
        
        return $stmt->execute();
    }

    public function getRecentActivities($limit = 10) {
        $conn = $this->db->connect();
        
        $stmt = $conn->prepare("
            SELECT action, details, ip_address, created_at
            FROM activity_logs
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
