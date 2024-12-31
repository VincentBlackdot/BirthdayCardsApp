<?php
require_once '../config/database.php';
require_once '../classes/ActivityLogger.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action']) || !isset($data['details'])) {
        throw new Exception('Missing required fields');
    }
    
    // Initialize Database and Logger
    $database = new Database();
    $db = $database->connect();
    $logger = new ActivityLogger($db);
    
    // Log the activity
    $success = $logger->log($data['action'], $data['details']);
    
    if (!$success) {
        throw new Exception('Failed to log activity');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Activity logged successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
