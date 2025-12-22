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
        require_once __DIR__ . '/../models/Validation.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_password = $_POST['new_password'] ?? '';
            $passwordValidation = Validation::validatePassword($new_password);
            
            if (!$passwordValidation['valid']) {
                $messages[] = ['error', $passwordValidation['error']];
            } else {
                if ($this->userModel->updatePassword($user['id'], $new_password)) {
                    $messages[] = ['success', 'Password updated successfully'];
                } else {
                    $messages[] = ['error', 'Error updating password'];
                }
            }
        }

        $settings = new Settings($messages);
        $settings->render();
    }
}
?>


