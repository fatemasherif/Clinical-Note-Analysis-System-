<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/FeedbackModel.php';
require_once __DIR__ . '/../PHP_Templates/Feedback.php';

class FeedbackController extends BaseController {
    private $feedbackModel;

    public function __construct() {
        parent::__construct();
        $this->feedbackModel = new FeedbackModel();
    }

    public function feedback() {
        $this->requireAuth();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rating = $_POST['rating'] ?? '';
            $comments = $_POST['comments'] ?? '';
            
            if ($rating && $comments) {
                $user_id = $_SESSION['user_id'];
                if ($this->feedbackModel->create($user_id, $rating, $comments)) {
                    $message = 'Feedback submitted successfully! Thank you for your input.';
                } else {
                    $message = 'Error submitting feedback. Please try again.';
                }
            } else {
                $message = 'Please fill in all fields.';
            }
        }

        $feedback = new Feedback($message);
        $feedback->render();
    }

    public function getAllFeedbacks() {
        $this->requireAuth();
        return $this->feedbackModel->getAll();
    }

    public function getUserFeedbacks() {
        $this->requireAuth();
        $user_id = $_SESSION['user_id'];
        return $this->feedbackModel->getByUser($user_id);
    }
}
?>




