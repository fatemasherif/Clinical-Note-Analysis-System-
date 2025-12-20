<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Upload.php';
require_once __DIR__ . '/../PHP_Templates/EditFile.php';

class EditFileController extends BaseController {
    private $uploadModel;

    public function __construct() {
        parent::__construct();
        $this->uploadModel = new UploadModel();
    }

    public function editFile() {
        $this->requireAuth('doctor');
        $user = $this->getCurrentUser();
        $message = '';
        $filename = '';
        $role = '';
        $fileContent = '';

        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['filename']) && isset($_GET['uploader_role'])) {
            $filename = $_GET['filename'];
            $role = $_GET['uploader_role'];
            
            $upload = $this->uploadModel->findByFilename($filename, $role);
            
            if ($upload && ($role == 'doctor' || $role == 'nurse')) {
                $file_path = 'uploads/' . $role . 's/' . $filename;
                if (file_exists($file_path) && str_ends_with($filename, '.txt')) {
                    $fileContent = file_get_contents($file_path);
                } else {
                    $message = 'File not found or not editable';
                }
            } else {
                $message = 'Permission denied';
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['filename']) && isset($_POST['uploader_role'])) {
            $filename = $_POST['filename'];
            $role = $_POST['uploader_role'];
            $fileContent = $_POST['content'];
            
            $upload = $this->uploadModel->findByFilename($filename, $role);
            
            if ($upload && ($role == 'doctor' || $role == 'nurse')) {
                $file_path = 'uploads/' . $role . 's/' . $filename;
                if (file_put_contents($file_path, $fileContent)) {
                    $message = 'File updated successfully';
                } else {
                    $message = 'Update failed';
                }
            } else {
                $message = 'Permission denied';
            }
        }

        $view = new EditFile($filename, $role, $fileContent, $message);
        $view->render();
    }
}
?>


