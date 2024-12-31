<?php

class ErrorHandler {
    // Error codes
    const INVALID_EMAIL = 1001;
    const MISSING_RECIPIENT = 1002;
    const INVALID_INPUT = 1003;
    const EMAIL_SEND_FAILED = 1004;
    const SMTP_ERROR = 1005;
    const SERVER_ERROR = 1006;
    
    private $logger;
    
    public function __construct($logger = null) {
        $this->logger = $logger;
    }
    
    /**
     * Get error message for a specific error code
     */
    public static function getErrorMessage($code) {
        $messages = [
            self::INVALID_EMAIL => 'Invalid email address format',
            self::MISSING_RECIPIENT => 'Recipient name and email are required',
            self::INVALID_INPUT => 'Invalid or missing input data',
            self::EMAIL_SEND_FAILED => 'Failed to send email. Please try again later',
            self::SMTP_ERROR => 'Email server error. Please try again later',
            self::SERVER_ERROR => 'An unexpected error occurred. Please try again later'
        ];
        
        return $messages[$code] ?? 'Unknown error occurred';
    }
    
    /**
     * Handle and log an error
     */
    public function handleError($code, $details = [], $logLevel = 'error') {
        $message = self::getErrorMessage($code);
        
        // Log the error if logger is available
        if ($this->logger) {
            $this->logger->log('error_occurred', [
                'error_code' => $code,
                'error_message' => $message,
                'details' => $details
            ]);
        }
        
        return [
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ];
    }
    
    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate required fields
     */
    public static function validateRequired($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
