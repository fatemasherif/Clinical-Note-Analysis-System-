<?php
require_once __DIR__ . '/Database.php';

class UploadModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find upload by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find upload by filename and role
     */
    public function findByFilename($filename, $role) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE filename = ? AND role = ?");
        $stmt->execute([$filename, $role]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all uploads
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM uploads ORDER BY uploaded_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get uploads by uploader
     */
    public function getByUploader($uploader) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE uploader = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$uploader]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get uploads by role
     */
    public function getByRole($role) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE role = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new upload record
     */
    public function create($filename, $uploader, $role) {
        $stmt = $this->db->prepare("INSERT INTO uploads (filename, uploader, role) VALUES (?, ?, ?)");
        return $stmt->execute([$filename, $uploader, $role]);
    }

    /**
     * Delete upload record
     */
    public function delete($filename, $role) {
        $stmt = $this->db->prepare("DELETE FROM uploads WHERE filename = ? AND role = ?");
        return $stmt->execute([$filename, $role]);
    }

    /**
     * Check if user can delete upload (owner or admin)
     */
    public function canDelete($filename, $role, $currentUsername, $currentUserRole) {
        $upload = $this->findByFilename($filename, $role);
        if (!$upload) {
            return false;
        }
        return $upload['uploader'] == $currentUsername || $currentUserRole == 'admin';
    }
}
?>

