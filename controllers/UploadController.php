<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Upload.php';
require_once __DIR__ . '/../PHP_Templates/Upload.php';
require_once __DIR__ . '/../PHP_Templates/ViewUploads.php';

class UploadController extends BaseController {
    private $uploadModel;

    public function __construct() {
        parent::__construct();
        $this->uploadModel = new UploadModel();
    }

    public function upload() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $message = '';

        require_once __DIR__ . '/../models/Validation.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];
                if ($file['error'] == 0) {
                    $filename = basename($file['name']);
                    
                    // Validate filename
                    $filenameValidation = Validation::validateFilename($filename);
                    if (!$filenameValidation['valid']) {
                        $message = $filenameValidation['error'];
                    } else {
                        // Validate file type
                        $fileTypeValidation = Validation::validateFileType($filename, ['txt']);
                        if (!$fileTypeValidation['valid']) {
                            $message = $fileTypeValidation['error'];
                        } else {
                            // Validate file size (5MB max)
                            $fileSizeValidation = Validation::validateFileSize($file, 5242880);
                            if (!$fileSizeValidation['valid']) {
                                $message = $fileSizeValidation['error'];
                            } else {
                                $filename = Validation::sanitizeInput($filename);
                                $dir = 'uploads/' . $user['role'] . 's';
                                $target = $dir . '/' . $filename;
                                
                                if (!is_dir($dir)) {
                                    mkdir($dir, 0755, true);
                                }
                                
                                if (move_uploaded_file($file['tmp_name'], $target)) {
                                    if ($this->uploadModel->create($filename, $user['username'], $user['role'])) {
                                        $message = 'File uploaded successfully';
                                    } else {
                                        $message = 'Upload failed to save record';
                                    }
                                } else {
                                    $message = 'Upload failed';
                                }
                            }
                        }
                    }
                } else {
                    $message = 'File upload error occurred';
                }
            } elseif (isset($_POST['note_content'])) {
                $content = Validation::sanitizeInput($_POST['note_content']);
                $filename = Validation::sanitizeInput($_POST['note_filename'] ?? '') ?: 'note_' . time() . '.txt';
                
                // Validate filename
                $filenameValidation = Validation::validateFilename($filename);
                if (!$filenameValidation['valid']) {
                    $message = $filenameValidation['error'];
                } else {
                    $dir = 'uploads/' . $user['role'] . 's';
                    $target = $dir . '/' . $filename;
                    
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    
                    if (file_put_contents($target, $content)) {
                        if ($this->uploadModel->create($filename, $user['username'], $user['role'])) {
                            $message = 'Note saved successfully';
                        } else {
                            $message = 'Save failed to save record';
                        }
                    } else {
                        $message = 'Save failed';
                    }
                }
            }
        }

        $upload = new Upload($message);
        $upload->render();
    }

    public function viewUploads() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $message = '';

        // Auto-sync files from filesystem to database on first load
        // This ensures all existing files are in the database
        $this->startSession();
        if (!isset($_SESSION['files_synced'])) {
            $synced = $this->uploadModel->syncFilesFromFilesystem();
            if ($synced > 0) {
                $message = "Synced $synced existing file(s) to database.";
            }
            $_SESSION['files_synced'] = true;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['download'])) {
            $filename = $_GET['filename'] ?? '';
            $role = $_GET['uploader_role'] ?? '';
            
            if (empty($filename) || empty($role)) {
                $message = 'Invalid download parameters';
            } else {
                // Sanitize filename to prevent directory traversal
                $filename = basename($filename);
                $role = basename($role); // Sanitize role
                
                // Verify file exists in database first
                $uploadRecord = $this->uploadModel->findByFilename($filename, $role);
                if (!$uploadRecord) {
                    $message = 'File not found in database: ' . $filename;
                } else {
                    // Build file path - use absolute path for reliability
                    $baseDir = dirname(__DIR__);
                    $file_path = $baseDir . '/uploads/' . $role . 's/' . $filename;
                    
                    // Also try relative path as fallback
                    $relative_path = 'uploads/' . $role . 's/' . $filename;
                    
                    // Try absolute path first, then relative
                    $final_path = null;
                    if (file_exists($file_path) && is_file($file_path)) {
                        $final_path = $file_path;
                    } elseif (file_exists($relative_path) && is_file($relative_path)) {
                        $final_path = $relative_path;
                    }
                    
                    if ($final_path && is_readable($final_path)) {
                        // Determine content type
                        $contentType = 'application/octet-stream';
                        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        if ($ext == 'txt') {
                            $contentType = 'text/plain';
                        } elseif ($ext == 'pdf') {
                            $contentType = 'application/pdf';
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $contentType = 'image/' . $ext;
                        }
                        
                        // Clear any output buffering
                        while (ob_get_level()) {
                            ob_end_clean();
                        }
                        
                        header('Content-Type: ' . $contentType);
                        header('Content-Disposition: attachment; filename="' . htmlspecialchars(basename($filename), ENT_QUOTES, 'UTF-8') . '"');
                        header('Content-Length: ' . filesize($final_path));
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Expires: 0');
                        
                        readfile($final_path);
                        exit;
                    } else {
                        $message = 'File exists in database but not found on disk: ' . $filename . ' (Path: ' . $file_path . ')';
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_filename'])) {
            $filename = $_POST['delete_filename'];
            $role = $_POST['uploader_role'];
            
            if ($this->uploadModel->canDelete($filename, $role, $user['username'], $user['role'])) {
                if ($this->uploadModel->delete($filename, $role)) {
                    $file_path = 'uploads/' . $role . 's/' . $filename;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $message = 'File deleted successfully';
                } else {
                    $message = 'Error deleting file record';
                }
            } else {
                $message = 'Permission denied';
            }
        }

        // Get all uploads from database
        $uploads = $this->uploadModel->getAll();
        
        // Verify files exist on disk and filter out missing files
        $validUploads = [];
        foreach ($uploads as $upload) {
            $baseDir = dirname(__DIR__);
            $file_path = $baseDir . '/uploads/' . $upload['role'] . 's/' . $upload['filename'];
            $relative_path = 'uploads/' . $upload['role'] . 's/' . $upload['filename'];
            
            // Only include files that exist on disk
            if (file_exists($file_path) || file_exists($relative_path)) {
                $validUploads[] = $upload;
            }
        }

        $view = new ViewUploads($validUploads, $message);
        $view->render();
    }
}
?>


