<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../PHP_Templates/Homepage.php';

class IndexController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->initializeDatabase();
    }

    private function initializeDatabase() {
        // Create tables if not exist
        $this->db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL)");
        $this->db->exec("CREATE TABLE IF NOT EXISTS uploads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL,
            uploader TEXT NOT NULL,
            role TEXT NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

        // Insert default users if not exist
        try {
            if (!$this->userModel->usernameExists('admin')) {
                $this->userModel->create('admin', 'admin123', 'admin');
            }
            if (!$this->userModel->usernameExists('doctor')) {
                $this->userModel->create('doctor', 'doctor123', 'doctor');
            }
            if (!$this->userModel->usernameExists('nurse')) {
                $this->userModel->create('nurse', 'nurse123', 'nurse');
            }
        } catch (Exception $e) {
            // Ignore if already exist
        }
    }

    public function index() {
        $this->startSession();
        $home = new Homepage();
        $home->render();
    }
}
?>


