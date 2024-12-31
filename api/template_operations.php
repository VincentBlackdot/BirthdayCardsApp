<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../classes/Template.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Log function
function logError($message) {
    $logDir = dirname(__DIR__) . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logDir . '/error.log');
}

try {
    // Log the raw request
    logError("Raw POST data: " . file_get_contents('php://input'));
    logError("FILES array: " . print_r($_FILES, true));
    logError("POST array: " . print_r($_POST, true));

    $db = new Database();
    $conn = $db->connect();
    $template = new Template($conn);

    $action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : null);

    // Log incoming request
    logError("Received request - Action: " . $action . ", Method: " . $_SERVER['REQUEST_METHOD']);

    switch ($action) {
        case 'get_all':
            $templates = $template->getAllTemplates();
            echo json_encode(['success' => true, 'templates' => $templates]);
            break;

        case 'upload':
            // Validate required fields
            $requiredFields = ['name', 'message', 'design'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $missingFields[] = 'image (error: ' . ($_FILES['image']['error'] ?? 'not set') . ')';
            }

            if (!empty($missingFields)) {
                throw new Exception('Missing or invalid fields: ' . implode(', ', $missingFields));
            }

            $result = $template->uploadTemplate(
                $_POST['name'],
                $_POST['message'],
                $_FILES['image'],
                $_POST['design']
            );

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            echo json_encode($result);
            break;

        case 'get_single':
            if (!isset($_GET['id'])) {
                throw new Exception('Template ID is required');
            }

            $templateData = $template->getTemplateById($_GET['id']);
            if (!$templateData) {
                throw new Exception('Template not found');
            }

            echo json_encode(['success' => true, 'template' => $templateData]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    logError("Error occurred: " . $e->getMessage());
    logError("Stack trace: " . $e->getTraceAsString());
    
    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'post' => $_POST,
            'files' => $_FILES,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ];
    
    echo json_encode($response);
}

// Log the response
logError("Response sent: " . ob_get_contents());
?>
