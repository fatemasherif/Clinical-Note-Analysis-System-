<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../models/ForumModel.php';
require_once __DIR__ . '/../PHP_Templates/AdminDashboard.php';
require_once __DIR__ . '/../PHP_Templates/DoctorDashboard.php';

class DashboardController extends BaseController {
    private $userModel;
    private $feedbackModel;
    private $forumModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->feedbackModel = new FeedbackModel();
        $this->forumModel = new ForumModel();
    }

    public function adminDashboard() {
        $this->requireAuth('admin');
        $user = $this->getCurrentUser();

        // Use getAll() since email column doesn't exist in current schema
        $users = $this->userModel->getAll();
        $feedbacks = $this->feedbackModel->getAll();
        $forumPosts = $this->forumModel->getAll();

        $dashboard = new AdminDashboard($user['username'], $users, $feedbacks);
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


