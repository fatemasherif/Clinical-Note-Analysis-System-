# Automated Unit Tests

## Setup

1. Install PHPUnit via Composer:
```bash
composer install
```

Or if you don't have Composer, install PHPUnit globally:
```bash
composer global require phpunit/phpunit
```

## Running Tests

Run all tests:
```bash
vendor/bin/phpunit
```

Or if installed globally:
```bash
phpunit
```

Run specific test file:
```bash
vendor/bin/phpunit tests/UserTest.php
vendor/bin/phpunit tests/UploadModelTest.php
```

## Test Coverage

The test suite includes:

### User Model Tests (`tests/UserTest.php`)
- ✅ Create user
- ✅ Find by username
- ✅ Find by username (not found)
- ✅ Authenticate with valid credentials
- ✅ Authenticate with invalid password
- ✅ Authenticate with invalid username
- ✅ Update user
- ✅ Update password
- ✅ Delete user
- ✅ Username exists check
- ✅ Get all users

### Upload Model Tests (`tests/UploadModelTest.php`)
- ✅ Create upload
- ✅ Find by filename
- ✅ Get all uploads
- ✅ Get by uploader
- ✅ Get by role
- ✅ Delete upload
- ✅ Can delete as owner
- ✅ Can delete as admin
- ✅ Cannot delete as other user

## Test Database

Tests use a separate test database (`tests/test_database.db`) that is automatically created and cleaned up after each test run.

## Continuous Integration

These tests can be integrated into CI/CD pipelines to ensure code quality.

