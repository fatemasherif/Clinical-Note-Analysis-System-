<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserTest extends TestCase {
    private $user;
    private $testDbPath;
    private $originalDbPath;

    protected function setUp(): void {
        // Backup original database path
        $this->testDbPath = __DIR__ . '/test_database.db';
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
        
        // Create test database directly
        $pdo = new PDO('sqlite:' . $this->testDbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT NOT NULL)");
        
        // Temporarily change database path for testing
        // Note: This is a simplified test - in production, you'd use dependency injection
        $this->user = new User();
        
        // Manually set test database connection
        $reflection = new ReflectionClass($this->user);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($this->user, $pdo);
    }

    protected function tearDown(): void {
        // Clean up test database
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }

    public function testCreateUser() {
        $result = $this->user->create('testuser', 'password123', 'doctor');
        $this->assertTrue($result);
        
        $user = $this->user->findByUsername('testuser');
        $this->assertNotFalse($user);
        $this->assertEquals('testuser', $user['username']);
        $this->assertEquals('doctor', $user['role']);
    }

    public function testFindByUsername() {
        $this->user->create('testuser', 'password123', 'doctor');
        $user = $this->user->findByUsername('testuser');
        
        $this->assertNotFalse($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testFindByUsernameNotFound() {
        $user = $this->user->findByUsername('nonexistent');
        $this->assertFalse($user);
    }

    public function testAuthenticateValidCredentials() {
        $this->user->create('testuser', 'password123', 'doctor');
        $user = $this->user->authenticate('testuser', 'password123');
        
        $this->assertNotFalse($user);
        $this->assertEquals('testuser', $user['username']);
    }

    public function testAuthenticateInvalidPassword() {
        $this->user->create('testuser', 'password123', 'doctor');
        $user = $this->user->authenticate('testuser', 'wrongpassword');
        
        $this->assertFalse($user);
    }

    public function testAuthenticateInvalidUsername() {
        $user = $this->user->authenticate('nonexistent', 'password123');
        $this->assertFalse($user);
    }

    public function testUpdateUser() {
        $this->user->create('testuser', 'password123', 'doctor');
        $user = $this->user->findByUsername('testuser');
        
        $result = $this->user->update($user['id'], 'updateduser', 'admin');
        $this->assertTrue($result);
        
        $updated = $this->user->findById($user['id']);
        $this->assertEquals('updateduser', $updated['username']);
        $this->assertEquals('admin', $updated['role']);
    }

    public function testUpdatePassword() {
        $this->user->create('testuser', 'oldpassword', 'doctor');
        $user = $this->user->findByUsername('testuser');
        
        $result = $this->user->updatePassword($user['id'], 'newpassword');
        $this->assertTrue($result);
        
        // Verify new password works
        $authenticated = $this->user->authenticate('testuser', 'newpassword');
        $this->assertNotFalse($authenticated);
        
        // Verify old password doesn't work
        $notAuthenticated = $this->user->authenticate('testuser', 'oldpassword');
        $this->assertFalse($notAuthenticated);
    }

    public function testDeleteUser() {
        $this->user->create('testuser', 'password123', 'doctor');
        $user = $this->user->findByUsername('testuser');
        
        $result = $this->user->delete($user['id']);
        $this->assertTrue($result);
        
        $deleted = $this->user->findByUsername('testuser');
        $this->assertFalse($deleted);
    }

    public function testUsernameExists() {
        $this->user->create('testuser', 'password123', 'doctor');
        
        $this->assertTrue($this->user->usernameExists('testuser'));
        $this->assertFalse($this->user->usernameExists('nonexistent'));
    }

    public function testGetAll() {
        $this->user->create('user1', 'pass1', 'doctor');
        $this->user->create('user2', 'pass2', 'admin');
        
        $users = $this->user->getAll();
        $this->assertCount(2, $users);
    }
}

