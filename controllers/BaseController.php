<?php
require_once __DIR__ . '/../models/Database.php';

class BaseController {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function requireAuth($role = null) {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        if ($role && $_SESSION['role'] != $role) {
            header('Location: login.php');
            exit;
        }
    }

    protected function getCurrentUser() {
        session_start();
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];
    }
}
?>




