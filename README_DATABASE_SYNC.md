# Database File Sync

## Problem
Files that exist in the `uploads/` folder might not be in the database, making them invisible and undownloadable.

## Solution
The system now automatically syncs files from the filesystem to the database when you first visit the "View Uploads" page.

## Manual Sync
If you need to manually sync files, run:

```bash
php sync_files_to_db.php
```

This will:
- Scan `uploads/doctors/` folder
- Scan `uploads/nurses/` folder
- Add any files found to the database (if not already there)
- Report how many files were synced

## How It Works

1. **Automatic Sync**: When you visit `view_uploads.php`, the system automatically checks for files on disk that aren't in the database and adds them.

2. **Database Verification**: Before downloading, the system verifies:
   - File exists in database
   - File exists on disk
   - File is readable

3. **File Filtering**: Only files that exist both in database AND on disk are shown in the uploads list.

## Database Connection

All file operations are now properly connected to the database:
- ✅ Files are saved to database when uploaded
- ✅ Files are retrieved from database when viewing
- ✅ Files are verified in database before downloading
- ✅ Missing files are automatically synced

## Troubleshooting

If files still can't be downloaded:
1. Check if file exists in `uploads/doctors/` or `uploads/nurses/`
2. Run `sync_files_to_db.php` to sync files
3. Check database: `SELECT * FROM uploads;`
4. Verify file permissions (files should be readable)

