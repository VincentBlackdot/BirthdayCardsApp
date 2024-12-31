<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $customNote = $data['custom_note'] ?? '';

    error_log("Preview card input - Name: $name, Custom Note: $customNote");

    if (empty($name)) {
        throw new Exception('Name is required');
    }

    // Connect to database
    $db = new Database();
    $conn = $db->connect();

    // Get a random template
    $stmt = $conn->prepare("SELECT * FROM templates WHERE is_active = 1 ORDER BY RAND() LIMIT 1");
    $stmt->execute();
    $template = $stmt->get_result()->fetch_assoc();

    if (!$template) {
        throw new Exception('No templates found');
    }

    // Log template details for debugging
    error_log("Selected Template: " . print_r($template, true));

    // Replace [NAME] placeholder case-insensitively
    $message = preg_replace('/\[name\]/i', $name, $template['message']);
    error_log("Processed Message: $message");

    if (!empty($customNote)) {
        $message .= "\n\n" . $customNote;
    }

    // Ensure image path starts with /Bdaysphp
    $imagePath = $template['image_path'];
    if (strpos($imagePath, '/Bdaysphp') !== 0) {
        $imagePath = '/Bdaysphp' . (strpos($imagePath, '/') === 0 ? '' : '/') . $imagePath;
    }

    echo json_encode([
        'message' => $message,
        'background' => $imagePath,
        'design' => $template['design']
    ]);

} catch (Exception $e) {
    error_log("Preview card error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => [
            'name' => $name ?? null,
            'template' => $template ?? null
        ]
    ]);
}
