<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../PHP_Templates/Feedback.php';

class FeedbackController extends BaseController {
    public function feedback() {
        $this->requireAuth();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rating = $_POST['rating'] ?? '';
            $comments = $_POST['comments'] ?? '';
            
            if ($rating && $comments) {
                // TODO: Save feedback to database if needed
                // For now, just show success message
                $message = 'Feedback submitted successfully! Thank you for your input.';
            } else {
                $message = 'Please fill in all fields.';
            }
        }

        $feedback = new Feedback($message);
        $feedback->render();
    }
}
?>




