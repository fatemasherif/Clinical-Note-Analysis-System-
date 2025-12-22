<?php
require_once __DIR__ . '/Database.php';

class FeedbackModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->createTable();
    }

    private function createTable() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS feedbacks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            rating INTEGER NOT NULL,
            comments TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
    }

    public function create($user_id, $rating, $comments) {
        $stmt = $this->db->prepare("INSERT INTO feedbacks (user_id, rating, comments) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $rating, $comments]);
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT f.*, u.username, u.role 
            FROM feedbacks f 
            JOIN users u ON f.user_id = u.id 
            ORDER BY f.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT f.*, u.username, u.role 
            FROM feedbacks f 
            JOIN users u ON f.user_id = u.id 
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>