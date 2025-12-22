<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ForumModel.php';
require_once __DIR__ . '/../PHP_Templates/Forum.php';

class ForumController extends BaseController {
    private $forumModel;

    public function __construct() {
        parent::__construct();
        $this->forumModel = new ForumModel();
    }

    public function forum() {
        $this->requireAuth();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $content = $_POST['content'] ?? '';
            if ($content) {
                $user_id = $_SESSION['user_id'];
                if ($this->forumModel->create($user_id, $content)) {
                    $message = 'Post submitted successfully!';
                } else {
                    $message = 'Error submitting post. Please try again.';
                }
            } else {
                $message = 'Please enter some content.';
            }
        }

        $posts = $this->forumModel->getAll();
        $forum = new Forum($posts, $message);
        $forum->render();
    }

    public function getAllPosts() {
        $this->requireAuth();
        return $this->forumModel->getAll();
    }

    public function getUserPosts() {
        $this->requireAuth();
        $user_id = $_SESSION['user_id'];
        return $this->forumModel->getByUser($user_id);
    }
}
?>







