<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT id, path FROM templates ORDER BY uploaded_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $templates = [];
    while ($row = $result->fetch_assoc()) {
        $templates[] = [
            'id' => $row['id'],
            'path' => $row['path']
        ];
    }

    echo json_encode([
        'success' => true,
        'templates' => $templates
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
