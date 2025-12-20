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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];
                if ($file['error'] == 0) {
                    $filename = basename($file['name']);
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
            } elseif (isset($_POST['note_content'])) {
                $content = $_POST['note_content'];
                $filename = $_POST['note_filename'] ?: 'note_' . time() . '.txt';
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

        $upload = new Upload($message);
        $upload->render();
    }

    public function viewUploads() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['download'])) {
            $filename = $_GET['filename'];
            $role = $_GET['uploader_role'];
            $file_path = 'uploads/' . $role . 's/' . $filename;
            
            if (file_exists($file_path)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit;
            } else {
                $message = 'File not found';
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

        $uploads = $this->uploadModel->getAll();

        $view = new ViewUploads($uploads, $message);
        $view->render();
    }
}
?>


