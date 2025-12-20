# How to Run the Local Server

## Quick Start

### Option 1: Using the Batch File (Windows)
1. Navigate to this directory: `Clinical-Note-Analysis-System--main\Clinical-Note-Analysis-System--main\`
2. Double-click `start_server.bat`
3. Open your browser and go to: http://localhost:8000

### Option 2: Manual Command (Windows)
1. Open Command Prompt or PowerShell
2. Navigate to the project directory:
   ```
   cd "C:\Users\ahmed\Downloads\Clinical-Note-Analysis-System--main\Clinical-Note-Analysis-System--main"
   ```
3. Run the PHP server:
   ```
   php -S localhost:8000
   ```
4. Open your browser and go to: http://localhost:8000

### Option 3: Manual Command (Mac/Linux)
1. Open Terminal
2. Navigate to the project directory:
   ```
   cd /path/to/Clinical-Note-Analysis-System--main/Clinical-Note-Analysis-System--main
   ```
3. Run the PHP server:
   ```
   php -S localhost:8000
   ```
   Or use the shell script:
   ```
   chmod +x start_server.sh
   ./start_server.sh
   ```
4. Open your browser and go to: http://localhost:8000

## Important Notes

- **Make sure you're in the correct directory**: The server must be run from `Clinical-Note-Analysis-System--main\Clinical-Note-Analysis-System--main\` directory (where `index.php` is located)
- **PHP must be installed**: Make sure PHP is installed and accessible from your command line
- **Port 8000**: If port 8000 is already in use, you can change it:
  ```
  php -S localhost:8080
  ```
  Then access it at: http://localhost:8080

## Default Login Credentials

- **Admin**: username: `admin`, password: `admin123`
- **Doctor**: username: `doctor`, password: `doctor123`
- **Nurse**: username: `nurse`, password: `nurse123`

## Troubleshooting

### "Not Found" Error
- Make sure you're running the server from the correct directory (where `index.php` is located)
- Check that PHP is installed: `php -v`
- Try accessing: http://localhost:8000/index.php directly

### Database Issues
- The database file (`database.db`) should be in the same directory as `index.php`
- If you get database errors, make sure SQLite is enabled in PHP

