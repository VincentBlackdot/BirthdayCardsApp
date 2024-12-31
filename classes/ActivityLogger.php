<?php

class ActivityLogger {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Log an activity
     * @param string $action The action performed (e.g., 'email_sent', 'card_downloaded')
     * @param array $details Additional details about the action
     * @return bool Whether the logging was successful
     */
    public function log($action, $details = []) {
        try {
            // Convert details array to JSON
            $detailsJson = json_encode($details);
            
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (action, details)
                VALUES (?, ?)
            ");
            
            return $stmt->execute([$action, $detailsJson]);
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent activities
     * @param int $limit Number of activities to retrieve
     * @return array Array of activity logs
     */
    public function getRecentActivities($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_logs
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error retrieving activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get activities by action type
     * @param string $action The action type to filter by
     * @param int $limit Number of activities to retrieve
     * @return array Array of activity logs
     */
    public function getActivitiesByAction($action, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM activity_logs
                WHERE action = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([$action, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error retrieving activities by action: " . $e->getMessage());
            return [];
        }
    }
}
