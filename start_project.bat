@echo off
echo ========================================
echo  Clinical Note Analysis System
echo ========================================
echo.

echo [Step 1/2] Checking Python dependencies...
py -m pip install -q -r requirements.txt
if %errorlevel% neq 0 (
    echo Warning: Could not install Python dependencies automatically.
    echo Please run: py -m pip install -r requirements.txt
    echo.
)

echo [Step 2/2] Starting services...
echo.

echo Starting Python NLP Service on port 5001...
start "NLP Service" cmd /k "py models\nlp_api.py"

timeout /t 3 /nobreak >nul

echo Starting PHP Server on port 8000...
echo.
echo ========================================
echo  Services Running:
echo  - PHP Server: http://localhost:8000
echo  - NLP Service: http://127.0.0.1:5001
echo ========================================
echo.
echo Press Ctrl+C to stop the PHP server
echo (Close the NLP Service window separately)
echo.

php -S localhost:8000

