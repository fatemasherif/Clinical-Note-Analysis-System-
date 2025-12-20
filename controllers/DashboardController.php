<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../PHP_Templates/AdminDashboard.php';
require_once __DIR__ . '/../PHP_Templates/DoctorDashboard.php';

class DashboardController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function adminDashboard() {
        $this->requireAuth('admin');
        $user = $this->getCurrentUser();

        $users = $this->userModel->getAllWithEmail();

        $dashboard = new AdminDashboard($user['username'], $users);
        $dashboard->render();
    }

    public function doctorDashboard() {
        $this->requireAuth();
        $user = $this->getCurrentUser();

        $dashboard = new DoctorDashboard($user['username'], $user['role']);
        $dashboard->render();
    }
}
?>


