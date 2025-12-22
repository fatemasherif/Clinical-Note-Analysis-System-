<?php
class Validation {
    /**
     * Validate username
     */
    public static function validateUsername($username) {
        if (empty($username)) {
            return ['valid' => false, 'error' => 'Username cannot be empty'];
        }
        if (strlen($username) < 3) {
            return ['valid' => false, 'error' => 'Username must be at least 3 characters'];
        }
        if (strlen($username) > 50) {
            return ['valid' => false, 'error' => 'Username must be less than 50 characters'];
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ['valid' => false, 'error' => 'Username can only contain letters, numbers, and underscores'];
        }
        return ['valid' => true];
    }

    /**
     * Validate password
     */
    public static function validatePassword($password) {
        if (empty($password)) {
            return ['valid' => false, 'error' => 'Password cannot be empty'];
        }
        if (strlen($password) < 6) {
            return ['valid' => false, 'error' => 'Password must be at least 6 characters'];
        }
        if (strlen($password) > 100) {
            return ['valid' => false, 'error' => 'Password must be less than 100 characters'];
        }
        return ['valid' => true];
    }

    /**
     * Validate role
     */
    public static function validateRole($role) {
        $validRoles = ['admin', 'doctor', 'nurse'];
        if (empty($role)) {
            return ['valid' => false, 'error' => 'Role cannot be empty'];
        }
        if (!in_array(strtolower($role), $validRoles)) {
            return ['valid' => false, 'error' => 'Invalid role. Must be: admin, doctor, or nurse'];
        }
        return ['valid' => true];
    }

    /**
     * Sanitize input string
     */
    public static function sanitizeInput($input) {
        if (is_string($input)) {
            $input = trim($input);
            $input = stripslashes($input);
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        return $input;
    }

    /**
     * Validate filename
     */
    public static function validateFilename($filename) {
        if (empty($filename)) {
            return ['valid' => false, 'error' => 'Filename cannot be empty'];
        }
        if (strlen($filename) > 255) {
            return ['valid' => false, 'error' => 'Filename too long'];
        }
        // Check for dangerous characters
        if (preg_match('/[\/\\\\\?\*\|<>:"]/', $filename)) {
            return ['valid' => false, 'error' => 'Filename contains invalid characters'];
        }
        return ['valid' => true];
    }

    /**
     * Validate file type
     */
    public static function validateFileType($filename, $allowedTypes = ['txt']) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
        }
        return ['valid' => true];
    }

    /**
     * Validate file size
     */
    public static function validateFileSize($file, $maxSize = 5242880) { // 5MB default
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'File upload error'];
        }
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File size exceeds maximum allowed size'];
        }
        return ['valid' => true];
    }
}
?>

