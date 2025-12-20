<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../PHP_Templates/Login.php';
require_once __DIR__ . '/../PHP_Templates/Signup.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function login() {
        session_start();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->authenticate($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] == 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: doctor_dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        }

        $login = new Login($error);
        $login->render();
    }

    public function signup() {
        session_start();
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? '';

            if ($username && $password && $role) {
                try {
                    if ($this->userModel->usernameExists($username)) {
                        $error = 'Username already exists';
                    } else {
                        if ($this->userModel->create($username, $password, $role)) {
                            header('Location: login.php');
                            exit;
                        } else {
                            $error = 'Error creating account';
                        }
                    }
                } catch (PDOException $e) {
                    $error = 'Username already exists or error occurred';
                }
            } else {
                $error = 'All fields are required';
            }
        }

        $signup = new Signup($error);
        $signup->render();
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
?>


