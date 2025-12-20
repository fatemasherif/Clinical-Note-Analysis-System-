<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../PHP_Templates/Feedback.php';

class FeedbackController extends BaseController {
    public function feedback() {
        $this->requireAuth();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // In original, it might save to db, but for now, just show message
            $message = 'Feedback submitted successfully';
        }

        $feedback = new Feedback();
        $feedback->render();
    }
}
?>


