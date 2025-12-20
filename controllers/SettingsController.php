<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../PHP_Templates/Settings.php';

class SettingsController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function settings() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $messages = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            if ($new_password) {
                if ($this->userModel->updatePassword($user['id'], $new_password)) {
                    $messages[] = ['success', 'Password updated successfully'];
                } else {
                    $messages[] = ['error', 'Error updating password'];
                }
            } else {
                $messages[] = ['error', 'Password cannot be empty'];
            }
        }

        $settings = new Settings($messages);
        $settings->render();
    }
}
?>


