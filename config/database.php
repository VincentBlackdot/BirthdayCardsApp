<?php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "bdaysphp";
    private $conn;

    public function connect() {
        try {
            // Create database if it doesn't exist
            $tempConn = new mysqli($this->host, $this->username, $this->password);
            if ($tempConn->connect_error) {
                throw new Exception("Connection failed: " . $tempConn->connect_error);
            }

            // Create database if it doesn't exist
            $tempConn->query("CREATE DATABASE IF NOT EXISTS {$this->database}");
            $tempConn->close();

            // Connect to the database
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }

            // Create templates table if it doesn't exist
            $this->createTemplatesTable();
            
            return $this->conn;
        } catch (Exception $e) {
            throw new Exception("Database connection error: " . $e->getMessage());
        }
    }

    private function createTemplatesTable() {
        $query = "CREATE TABLE IF NOT EXISTS templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            design VARCHAR(50) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (!$this->conn->query($query)) {
            throw new Exception("Error creating templates table: " . $this->conn->error);
        }

        // Check if we need to alter the table to add new columns
        $result = $this->conn->query("SHOW COLUMNS FROM templates LIKE 'image_path'");
        if ($result->num_rows === 0) {
            $alterQuery = "ALTER TABLE templates 
                ADD COLUMN image_path VARCHAR(255) NOT NULL AFTER design,
                ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER image_path,
                ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER is_active";
            
            if (!$this->conn->query($alterQuery)) {
                throw new Exception("Error updating table structure: " . $this->conn->error);
            }
        }
    }
}
