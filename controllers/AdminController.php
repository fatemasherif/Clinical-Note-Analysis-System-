<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../PHP_Templates/AdminUsers.php';
require_once __DIR__ . '/../PHP_Templates/AdminNotes.php';

class AdminController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function manageUsers() {
        $this->requireAuth('admin');
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['add'])) {
                $username = $data['username'];
                $password = $data['password'];
                $role = $data['role'];
                try {
                    if ($this->userModel->create($username, $password, $role)) {
                        echo json_encode(['message' => 'User added']);
                    } else {
                        echo json_encode(['error' => 'Error adding user']);
                    }
                } catch (Exception $e) {
                    echo json_encode(['error' => 'Error adding user']);
                }
                exit;
            } elseif (isset($data['edit'])) {
                $id = $data['id'];
                $username = $data['username'];
                $role = $data['role'];
                if ($this->userModel->update($id, $username, $role)) {
                    echo json_encode(['message' => 'User updated']);
                } else {
                    echo json_encode(['error' => 'Error updating user']);
                }
                exit;
            } elseif (isset($data['delete'])) {
                $id = $data['id'];
                if ($this->userModel->delete($id)) {
                    echo json_encode(['message' => 'User deleted']);
                } else {
                    echo json_encode(['error' => 'Error deleting user']);
                }
                exit;
            }
        }

        $users = $this->userModel->getAll();

        $adminUsers = new AdminUsers($users);
        $adminUsers->render();
    }

    public function manageNotes() {
        $this->requireAuth('admin');

        // For simplicity, use dummy data as in original
        $notes = [
            ['id' => 1, 'doctor' => 'Dr. Smith', 'diagnosis' => 'Hypertension', 'summary' => 'Patient prescribed Amlodipine.', 'date' => '2025-10-08', 'status' => 'approved'],
            ['id' => 2, 'doctor' => 'Dr. Ahmed', 'diagnosis' => 'Diabetes', 'summary' => 'Metformin 500mg daily.', 'date' => '2025-10-07', 'status' => 'pending'],
            ['id' => 3, 'doctor' => 'Dr. Lee', 'diagnosis' => 'Asthma', 'summary' => 'Inhaler twice daily.', 'date' => '2025-10-06', 'status' => 'rejected']
        ];

        $adminNotes = new AdminNotes($notes);
        $adminNotes->render();
    }
}
?>

