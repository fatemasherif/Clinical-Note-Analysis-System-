<?php
/**
 * Sync existing files in uploads directory to database
 * This ensures all files are connected to the database
 */
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/Upload.php';

$uploadModel = new UploadModel();
$db = Database::getInstance()->getConnection();

// Ensure uploads table exists
$db->exec("CREATE TABLE IF NOT EXISTS uploads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    filename TEXT NOT NULL,
    uploader TEXT NOT NULL,
    role TEXT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");

$synced = 0;
$errors = 0;

// Scan doctors folder
$doctorsDir = __DIR__ . '/uploads/doctors';
if (is_dir($doctorsDir)) {
    $files = scandir($doctorsDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_file($doctorsDir . '/' . $file)) {
            // Check if file already exists in database
            $existing = $uploadModel->findByFilename($file, 'doctor');
            if (!$existing) {
                // Add to database with default uploader
                if ($uploadModel->create($file, 'system', 'doctor')) {
                    $synced++;
                    echo "Synced: doctors/$file\n";
                } else {
                    $errors++;
                    echo "Error syncing: doctors/$file\n";
                }
            }
        }
    }
}

// Scan nurses folder
$nursesDir = __DIR__ . '/uploads/nurses';
if (is_dir($nursesDir)) {
    $files = scandir($nursesDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_file($nursesDir . '/' . $file)) {
            // Check if file already exists in database
            $existing = $uploadModel->findByFilename($file, 'nurse');
            if (!$existing) {
                // Add to database with default uploader
                if ($uploadModel->create($file, 'system', 'nurse')) {
                    $synced++;
                    echo "Synced: nurses/$file\n";
                } else {
                    $errors++;
                    echo "Error syncing: nurses/$file\n";
                }
            }
        }
    }
}

echo "\nSync complete! Synced: $synced files, Errors: $errors\n";
?>

