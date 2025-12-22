<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ForumModel.php';
require_once __DIR__ . '/../PHP_Templates/AdminForum.php';

class AdminForumController extends BaseController {
    private $forumModel;

    public function __construct() {
        parent::__construct();
        $this->forumModel = new ForumModel();
    }

    public function adminForum() {
        $this->requireAuth('admin');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->handlePostActions();
            return;
        }

        $user = $this->getCurrentUser();
        $forumPosts = $this->forumModel->getAll();

        $forum = new AdminForum($user['username'], $forumPosts);
        $forum->render();
    }

    private function handlePostActions() {
        header('Content-Type: application/json');

        $action = $_POST['action'] ?? '';
        $postId = $_POST['post_id'] ?? '';

        if (!$action || !$postId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        try {
            if ($action === 'delete') {
                $result = $this->forumModel->delete($postId);
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
                }
            } elseif ($action === 'edit') {
                $content = $_POST['content'] ?? '';
                if (empty($content)) {
                    echo json_encode(['success' => false, 'message' => 'Content cannot be empty']);
                    return;
                }

                $result = $this->forumModel->update($postId, $content);
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update post']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
}
?>