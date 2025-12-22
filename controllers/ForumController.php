<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../PHP_Templates/Forum.php';

class ForumController extends BaseController {
    public function forum() {
        $this->startSession();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $content = $_POST['content'] ?? '';
            if ($content) {
                // For now, just echo or something, but since no db, just message
                $message = 'Post submitted';
            }
        }

        $forum = new Forum();
        $forum->render();
    }
}
?>







