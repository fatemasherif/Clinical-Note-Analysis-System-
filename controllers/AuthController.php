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
        $this->startSession();
        $error = '';
        require_once __DIR__ . '/../models/Validation.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = Validation::sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Basic validation
            if (empty($username) || empty($password)) {
                $error = 'Username and password are required';
            } else {
                $user = $this->userModel->authenticate($username, $password);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
                    $_SESSION['role'] = htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8');
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
        }

        $login = new Login($error);
        $login->render();
    }

    public function signup() {
        $this->startSession();
        $error = '';
        require_once __DIR__ . '/../models/Validation.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = Validation::sanitizeInput($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = Validation::sanitizeInput($_POST['role'] ?? '');

            // Validate inputs
            $usernameValidation = Validation::validateUsername($username);
            if (!$usernameValidation['valid']) {
                $error = $usernameValidation['error'];
            } else {
                $passwordValidation = Validation::validatePassword($password);
                if (!$passwordValidation['valid']) {
                    $error = $passwordValidation['error'];
                } else {
                    $roleValidation = Validation::validateRole($role);
                    if (!$roleValidation['valid']) {
                        $error = $roleValidation['error'];
                    } else {
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
                    }
                }
            }
        }

        $signup = new Signup($error);
        $signup->render();
    }

    public function logout() {
        $this->startSession();
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
?>


