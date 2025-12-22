<?php
// Test bootstrap file
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Upload.php';

// Use test database for testing
define('TEST_DB_PATH', __DIR__ . '/test_database.db');

