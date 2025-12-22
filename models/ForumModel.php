<?php
require_once __DIR__ . '/Database.php';

class ForumModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->createTable();
    }

    private function createTable() {
        $this->db->exec("CREATE TABLE IF NOT EXISTS forum_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
    }

    public function create($user_id, $content) {
        $stmt = $this->db->prepare("INSERT INTO forum_posts (user_id, content) VALUES (?, ?)");
        return $stmt->execute([$user_id, $content]);
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT f.*, u.username, u.role 
            FROM forum_posts f 
            JOIN users u ON f.user_id = u.id 
            ORDER BY f.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT f.*, u.username, u.role 
            FROM forum_posts f 
            JOIN users u ON f.user_id = u.id 
            WHERE f.user_id = ? 
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $content) {
        $stmt = $this->db->prepare("UPDATE forum_posts SET content = ? WHERE id = ?");
        return $stmt->execute([$content, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM forum_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>