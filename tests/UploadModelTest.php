<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Upload.php';

class UploadModelTest extends TestCase {
    private $uploadModel;
    private $testDbPath;

    protected function setUp(): void {
        $this->testDbPath = __DIR__ . '/test_database.db';
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
        
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS uploads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL,
            uploader TEXT NOT NULL,
            role TEXT NOT NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        
        $this->uploadModel = new UploadModel();
        
        // Manually set test database connection
        $reflection = new ReflectionClass($this->uploadModel);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->uploadModel, $pdo);
    }

    protected function tearDown(): void {
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }

    public function testCreateUpload() {
        $result = $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        $this->assertTrue($result);
        
        $upload = $this->uploadModel->findByFilename('test.txt', 'doctor');
        $this->assertNotFalse($upload);
        $this->assertEquals('test.txt', $upload['filename']);
        $this->assertEquals('doctor1', $upload['uploader']);
    }

    public function testFindByFilename() {
        $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        $upload = $this->uploadModel->findByFilename('test.txt', 'doctor');
        
        $this->assertNotFalse($upload);
        $this->assertEquals('test.txt', $upload['filename']);
    }

    public function testGetAll() {
        $this->uploadModel->create('file1.txt', 'doctor1', 'doctor');
        $this->uploadModel->create('file2.txt', 'nurse1', 'nurse');
        
        $uploads = $this->uploadModel->getAll();
        $this->assertCount(2, $uploads);
    }

    public function testGetByUploader() {
        $this->uploadModel->create('file1.txt', 'doctor1', 'doctor');
        $this->uploadModel->create('file2.txt', 'doctor1', 'doctor');
        $this->uploadModel->create('file3.txt', 'doctor2', 'doctor');
        
        $uploads = $this->uploadModel->getByUploader('doctor1');
        $this->assertCount(2, $uploads);
    }

    public function testGetByRole() {
        $this->uploadModel->create('file1.txt', 'doctor1', 'doctor');
        $this->uploadModel->create('file2.txt', 'nurse1', 'nurse');
        
        $doctorUploads = $this->uploadModel->getByRole('doctor');
        $this->assertCount(1, $doctorUploads);
    }

    public function testDeleteUpload() {
        $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        
        $result = $this->uploadModel->delete('test.txt', 'doctor');
        $this->assertTrue($result);
        
        $deleted = $this->uploadModel->findByFilename('test.txt', 'doctor');
        $this->assertFalse($deleted);
    }

    public function testCanDeleteAsOwner() {
        $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        
        $canDelete = $this->uploadModel->canDelete('test.txt', 'doctor', 'doctor1', 'doctor');
        $this->assertTrue($canDelete);
    }

    public function testCanDeleteAsAdmin() {
        $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        
        $canDelete = $this->uploadModel->canDelete('test.txt', 'doctor', 'admin1', 'admin');
        $this->assertTrue($canDelete);
    }

    public function testCannotDeleteAsOtherUser() {
        $this->uploadModel->create('test.txt', 'doctor1', 'doctor');
        
        $canDelete = $this->uploadModel->canDelete('test.txt', 'doctor', 'doctor2', 'doctor');
        $this->assertFalse($canDelete);
    }
}

