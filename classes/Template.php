<?php

class Template {
    private $db;
    private $maxFileSize = 2097152; // 2MB in bytes
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    private $uploadDir;
    private $webRoot;

    public function __construct($database) {
        $this->db = $database;
        
        // Set the web root path
        $this->webRoot = dirname($_SERVER['SCRIPT_FILENAME']);
        if (strpos($this->webRoot, 'api') !== false) {
            $this->webRoot = dirname($this->webRoot);
        }
        
        // Set upload directory path
        $this->uploadDir = $this->webRoot . '/uploads/templates/';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0777, true)) {
                error_log("Failed to create directory: " . $this->uploadDir);
                throw new Exception("Failed to create upload directory");
            }
            // Set directory permissions
            chmod($this->uploadDir, 0777);
        }

        if (!is_writable($this->uploadDir)) {
            error_log("Directory not writable: " . $this->uploadDir);
            throw new Exception("Upload directory is not writable");
        }
    }

    public function getAllTemplates() {
        try {
            $query = "SELECT * FROM templates WHERE is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $templates = [];
            while ($row = $result->fetch_assoc()) {
                // Ensure image path starts with a forward slash
                $imagePath = $row['image_path'];
                if (!str_starts_with($imagePath, '/')) {
                    $imagePath = '/' . $imagePath;
                }
                
                $templates[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'message' => $row['message'],
                    'design' => $row['design'],
                    'image_path' => $imagePath
                ];
            }
            
            return $templates;
        } catch (Exception $e) {
            error_log("Error in getAllTemplates: " . $e->getMessage());
            throw $e;
        }
    }

    public function uploadTemplate($name, $message, $image, $design) {
        try {
            // Log the upload attempt
            error_log("Attempting to upload template - Name: $name, Design: $design");
            error_log("Image info: " . print_r($image, true));
            error_log("Upload directory: " . $this->uploadDir);

            // Validate file size
            if ($image['size'] > $this->maxFileSize) {
                throw new Exception('File size exceeds maximum limit of 2MB');
            }

            // Validate file type
            if (!isset($image['tmp_name']) || !file_exists($image['tmp_name'])) {
                throw new Exception('No file uploaded or file is invalid');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $image['tmp_name']);
            finfo_close($finfo);

            error_log("File MIME type: " . $mimeType);

            if (!in_array($mimeType, $this->allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed. Got: ' . $mimeType);
            }

            // Generate unique filename
            $extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $filename = uniqid('template_') . '.' . $extension;
            $filepath = $this->uploadDir . $filename;
            $webpath = '/uploads/templates/' . $filename; // Add leading slash for web path

            // Ensure upload directory exists and is writable
            if (!file_exists($this->uploadDir)) {
                if (!mkdir($this->uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory");
                }
                chmod($this->uploadDir, 0777);
            }

            if (!is_writable($this->uploadDir)) {
                throw new Exception("Upload directory is not writable");
            }

            // Log the file paths
            error_log("File path: " . $filepath);
            error_log("Web path: " . $webpath);

            // Move uploaded file
            if (!move_uploaded_file($image['tmp_name'], $filepath)) {
                $uploadError = error_get_last();
                error_log("Failed to move uploaded file. Upload error code: " . $image['error']);
                error_log("PHP error: " . ($uploadError ? $uploadError['message'] : 'No PHP error'));
                throw new Exception('Failed to upload file. Please check file permissions.');
            }

            // Verify the file was uploaded successfully
            if (!file_exists($filepath)) {
                throw new Exception('File upload failed - file does not exist after upload');
            }

            // Set file permissions
            chmod($filepath, 0644);

            // Insert into database
            $query = "INSERT INTO templates (name, message, design, image_path, is_active) VALUES (?, ?, ?, ?, 1)";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $this->db->error);
            }

            $stmt->bind_param("ssss", $name, $message, $design, $webpath);
            
            if (!$stmt->execute()) {
                // Log database error
                error_log("Database error: " . $stmt->error);
                // Delete uploaded file if database insert fails
                unlink($filepath);
                throw new Exception('Failed to save template to database: ' . $stmt->error);
            }

            return [
                'success' => true,
                'message' => 'Template uploaded successfully',
                'template' => [
                    'id' => $stmt->insert_id,
                    'name' => $name,
                    'message' => $message,
                    'design' => $design,
                    'image_path' => $webpath
                ]
            ];

        } catch (Exception $e) {
            error_log("Error in uploadTemplate: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'debug' => [
                    'file_info' => $image,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ];
        }
    }

    public function getTemplateById($id) {
        try {
            $query = "SELECT * FROM templates WHERE id = ? AND is_active = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Ensure image path starts with a forward slash
                $imagePath = $row['image_path'];
                if (!str_starts_with($imagePath, '/')) {
                    $imagePath = '/' . $imagePath;
                }
                
                return [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'message' => $row['message'],
                    'design' => $row['design'],
                    'image_path' => $imagePath
                ];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error in getTemplateById: " . $e->getMessage());
            throw $e;
        }
    }
}
