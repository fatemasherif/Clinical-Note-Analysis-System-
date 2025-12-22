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

    /**
     * Sync files from filesystem to database
     * Adds files that exist on disk but not in database
     */
    public function syncFilesFromFilesystem() {
        $synced = 0;
        $baseDir = dirname(__DIR__);
        
        // Sync doctors folder
        $doctorsDir = $baseDir . '/uploads/doctors';
        if (is_dir($doctorsDir)) {
            $files = scandir($doctorsDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && is_file($doctorsDir . '/' . $file)) {
                    $existing = $this->findByFilename($file, 'doctor');
                    if (!$existing) {
                        // Try to extract uploader from filename or use 'system'
                        $uploader = 'system';
                        if ($this->create($file, $uploader, 'doctor')) {
                            $synced++;
                        }
                    }
                }
            }
        }
        
        // Sync nurses folder
        $nursesDir = $baseDir . '/uploads/nurses';
        if (is_dir($nursesDir)) {
            $files = scandir($nursesDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && is_file($nursesDir . '/' . $file)) {
                    $existing = $this->findByFilename($file, 'nurse');
                    if (!$existing) {
                        $uploader = 'system';
                        if ($this->create($file, $uploader, 'nurse')) {
                            $synced++;
                        }
                    }
                }
            }
        }
        
        return $synced;
    }

    /**
     * Verify file exists in database and on disk
     */
    public function verifyFile($filename, $role) {
        $upload = $this->findByFilename($filename, $role);
        if (!$upload) {
            return ['exists_in_db' => false, 'exists_on_disk' => false];
        }
        
        $baseDir = dirname(__DIR__);
        $file_path = $baseDir . '/uploads/' . $role . 's/' . $filename;
        $exists_on_disk = file_exists($file_path) || file_exists('uploads/' . $role . 's/' . $filename);
        
        return [
            'exists_in_db' => true,
            'exists_on_disk' => $exists_on_disk,
            'upload' => $upload
        ];
    }
}
?>

