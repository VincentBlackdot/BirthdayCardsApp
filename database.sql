DROP DATABASE IF EXISTS birthday_cards;
CREATE DATABASE birthday_cards;
USE birthday_cards;

CREATE TABLE IF NOT EXISTS templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    background VARCHAR(50) NOT NULL,
    design VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,
    path VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default templates
INSERT INTO templates (name, message, background, design, category) VALUES
('Birthday Wishes 1', 'Dear [NAME], wishing you a fantastic birthday filled with joy and laughter! 🎉', 'bg-primary', 'stars', 'message'),
('Birthday Wishes 2', 'Happy Birthday [NAME]! May your special day be as wonderful as you are! 🎂', 'bg-success', 'balloons', 'message'),
('Birthday Wishes 3', 'Hey [NAME]! Happy Birthday! Here''s to another year of amazing adventures! 🎈', 'bg-info', 'confetti', 'message'),
('Birthday Wishes 4', 'To [NAME], sending you the warmest birthday wishes on your special day! 🎊', 'bg-warning', 'cake', 'message'),
('Birthday Wishes 5', 'Dearest [NAME], have a magical birthday filled with unforgettable moments! ✨', 'bg-danger', 'gifts', 'message');

-- Create email tracking tables
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

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(50) NOT NULL COMMENT 'Type of action (e.g., email_sent, card_downloaded)',
    details JSON COMMENT 'JSON object containing additional details about the action',
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IP address of the user performing the action',
    user_agent VARCHAR(255) DEFAULT NULL COMMENT 'User agent of the browser/client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
