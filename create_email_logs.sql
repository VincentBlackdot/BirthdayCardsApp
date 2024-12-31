CREATE TABLE IF NOT EXISTS email_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recipient_name VARCHAR(100) NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    template_id INT NOT NULL,
    tracking_id VARCHAR(50) NOT NULL,
    action VARCHAR(50) NOT NULL,
    opened TINYINT(1) DEFAULT 0,
    opened_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES templates(id)
);
