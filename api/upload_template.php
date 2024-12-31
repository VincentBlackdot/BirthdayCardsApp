<?php
require_once '../config/database.php';
require_once '../classes/ActivityLogger.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();
    $logger = new ActivityLogger($conn);

    if (!isset($_FILES['templateFile'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['templateFile'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($fileTmpName);
    
    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
    }

    if ($fileSize > 5000000) { // 5MB limit
        throw new Exception('File is too large. Maximum size is 5MB.');
    }

    if ($fileError !== 0) {
        throw new Exception('Error uploading file.');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = '../uploads/templates/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid() . '.' . $fileExtension;
    $destination = $uploadDir . $newFileName;

    // Move file to destination
    if (!move_uploaded_file($fileTmpName, $destination)) {
        throw new Exception('Failed to move uploaded file.');
    }

    // Get other form data
    $name = $_POST['name'] ?? '';
    $message = $_POST['message'] ?? '';
    $design = $_POST['design'] ?? '';
    $category = $_POST['category'] ?? 'message';

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO templates (name, message, path, design, category)
        VALUES (?, ?, ?, ?, ?)
    ");

    $relativePath = 'uploads/templates/' . $newFileName;
    $stmt->bind_param("sssss", $name, $message, $relativePath, $design, $category);

    if ($stmt->execute()) {
        $templateId = $stmt->insert_id;
        
        // Log the template upload activity
        $logger->log('template_uploaded', [
            'template_id' => $templateId,
            'template_name' => $name,
            'file_name' => $newFileName,
            'file_size' => $fileSize,
            'file_type' => $fileType
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Template uploaded successfully',
            'template' => [
                'id' => $templateId,
                'name' => $name,
                'path' => $relativePath,
                'design' => $design
            ]
        ]);
    } else {
        throw new Exception('Failed to save template to database.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
