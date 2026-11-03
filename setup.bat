@echo off
title ESCR MIS - 1-Click XAMPP Setup
cd /d "%~dp0"

echo ========================================================
echo   EAST SYSTEMS COLLEGES OF RIZAL (ESCR) MIS SETUP
echo ========================================================
echo.

if not exist ".env" (
    echo [1/4] Creating .env configuration file...
    copy /Y ".env.example" ".env" >nul
) else (
    echo [1/4] .env file already exists.
)

if not exist "vendor\autoload.php" (
    if exist "vendor.zip" (
        echo [2/4] Extracting PHP vendor dependencies from vendor.zip...
        tar.exe -xf "vendor.zip"
    ) else (
        echo [2/4] Running Composer install...
        composer install
    )
) else (
    echo [2/4] vendor\autoload.php is ready.
)

echo [3/4] Creating database escr_mis and importing database\escr_mis.sql...
if exist "C:\xampp\mysql\bin\mysql.exe" (
    "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS escr_mis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    "C:\xampp\mysql\bin\mysql.exe" -u root escr_mis < "database\escr_mis.sql"
)

echo [4/4] Clearing Laravel caches...
if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" artisan optimize:clear
)

echo.
echo ========================================================
echo   SETUP COMPLETE!
echo   Open in browser: http://localhost/MIS/public
echo ========================================================
pause
