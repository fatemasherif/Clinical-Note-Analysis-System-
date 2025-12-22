# 🚀 Clinical Note Analysis System - Setup Guide

## Prerequisites

Before running the project, ensure you have:

1. **PHP 7.4+** ✅ (You have PHP 8.5.1)
2. **Python 3.7+** (for NLP service)
3. **Composer** (for PHP dependencies - optional, only needed for tests)
4. **SQLite** (included with PHP)

---

## 📋 Quick Start Guide

### Step 1: Install PHP Dependencies (Optional - Only for Tests)

If you want to run unit tests, install Composer dependencies:

```bash
composer install
```

**Note:** If Composer is not installed, you can skip this step. The application will work without it, but you won't be able to run PHPUnit tests.

---

### Step 2: Install Python Dependencies (For NLP Service)

Install Python packages for the NLP microservice:

```bash
pip install -r requirements.txt
```

Or if `pip` is not in PATH:
```bash
python -m pip install -r requirements.txt
```

Or on Windows with Python Launcher:
```bash
py -m pip install -r requirements.txt
```

**Required packages:**
- Flask
- Flask-CORS
- Werkzeug

---

### Step 3: Start the Python NLP Service

The NLP service must be running for AI analysis features to work.

**Option A: Using the batch file (Windows)**
```bash
start_nlp_service.bat
```

**Option B: Manual start**
```bash
python models/nlp_api.py
```

Or on Windows:
```bash
py models/nlp_api.py
```

**Expected output:**
```
Starting Clinical NLP API Service...
The service will run on http://127.0.0.1:5001
 * Running on http://127.0.0.1:5001
```

**Keep this terminal window open!** The service must stay running.

---

### Step 4: Start the PHP Server

You have several options:

#### Option A: PHP Built-in Server (Recommended for Development)

```bash
php -S localhost:8000
```

Or specify a different port:
```bash
php -S localhost:8080
```

#### Option B: XAMPP/WAMP/MAMP

1. Copy the project folder to `htdocs` (XAMPP) or `www` (WAMP)
2. Start Apache from XAMPP/WAMP control panel
3. Access via `http://localhost/Clinical-Note-Analysis-System--main/`

#### Option C: Any Web Server

Configure your web server (Apache/Nginx) to point to the project directory.

---

### Step 5: Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

Or if using XAMPP/WAMP:
```
http://localhost/Clinical-Note-Analysis-System--main/
```

---

## 🎯 Default Login Credentials

The system creates default users on first run. Check `controllers/IndexController.php` for default credentials.

**Common defaults:**
- **Admin**: `admin` / `admin123`
- **Doctor**: `doctor` / `doctor123`
- **Nurse**: `nurse` / `nurse123`

---

## ✅ Verify Everything is Working

1. **Homepage**: Should load without errors
2. **Login**: Try logging in with default credentials
3. **Upload**: Upload a clinical note file
4. **Analyze**: Click "Analyze with AI" - should connect to Python service
5. **Menu**: Menu should change based on your role

---

## 🔧 Troubleshooting

### Issue: "Database connection failed"
- **Solution**: Ensure `database.db` file exists in the project root
- The database is created automatically on first run

### Issue: "NLP service unavailable" or AI analysis doesn't work
- **Solution**: Make sure Python NLP service is running on port 5001
- Check: `http://127.0.0.1:5001/health` should return `{"status": "ok"}`

### Issue: "curl_init()" error
- **Solution**: Enable `php_curl` extension in `php.ini`
- Or the system will fallback to `file_get_contents()` automatically

### Issue: Python not found
- **Solution**: Install Python 3.7+ from python.org
- Or use `py` command instead of `python` on Windows
- Add Python to PATH environment variable

### Issue: Composer not found
- **Solution**: Install Composer from getcomposer.org
- Or skip it if you don't need to run tests

### Issue: Port already in use
- **Solution**: Change the port:
  - PHP: `php -S localhost:8080` (use different port)
  - Python: Edit `models/nlp_api.py` and change port 5001

---

## 📁 Project Structure

```
Clinical-Note-Analysis-System--main/
├── controllers/          # MVC Controllers
├── models/              # MVC Models + Python NLP
│   └── nlp/            # Python NLP module
├── PHP_Templates/      # MVC Views
├── Static/             # CSS, JS files
├── uploads/            # Uploaded files
├── tests/              # PHPUnit tests
├── index.php           # Entry point
├── database.db         # SQLite database
└── requirements.txt    # Python dependencies
```

---

## 🚀 Quick Start Script (Windows)

Create a `start_project.bat` file:

```batch
@echo off
echo Starting Clinical Note Analysis System...
echo.

echo [1/2] Starting Python NLP Service...
start "NLP Service" cmd /k "python models\nlp_api.py"

timeout /t 3 /nobreak >nul

echo [2/2] Starting PHP Server...
echo.
echo Server running at http://localhost:8000
echo Press Ctrl+C to stop
echo.
php -S localhost:8000
```

Then just run:
```bash
start_project.bat
```

---

## 📝 Notes

- **Database**: SQLite database (`database.db`) is created automatically
- **File Uploads**: Files are stored in `uploads/` directory
- **NLP Service**: Must run separately on port 5001
- **Session**: Uses PHP sessions (no additional setup needed)
- **Tests**: Run with `vendor/bin/phpunit` (requires Composer)

---

## 🎉 You're Ready!

Once both services are running:
1. PHP server on `http://localhost:8000`
2. Python NLP service on `http://127.0.0.1:5001`

Open your browser and start using the Clinical Note Analysis System!

