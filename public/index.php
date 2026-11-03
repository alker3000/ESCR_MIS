<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Zero-Config First-Run Auto-Bootstrapper for Fresh GitHub Clones on XAMPP
$rootDir = realpath(__DIR__ . '/..') ?: dirname(__DIR__);

// 1. Auto-create .env from .env.example if missing
if (!file_exists($rootDir . '/.env') && file_exists($rootDir . '/.env.example')) {
    @copy($rootDir . '/.env.example', $rootDir . '/.env');
}

// 2. Ensure required Laravel storage & cache directories exist
foreach ([
    '/storage/app/private',
    '/storage/framework/cache/data',
    '/storage/framework/sessions',
    '/storage/framework/views',
    '/storage/logs',
    '/bootstrap/cache',
] as $relDir) {
    $fullDir = $rootDir . $relDir;
    if (!is_dir($fullDir)) {
        @mkdir($fullDir, 0775, true);
    }
}

// 3. Auto-extract vendor.zip if vendor/autoload.php is missing after a fresh git clone
if (!file_exists($rootDir . '/vendor/autoload.php') && file_exists($rootDir . '/vendor.zip')) {
    @set_time_limit(180);
    $zipFile = $rootDir . '/vendor.zip';
    if (class_exists('ZipArchive')) {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($rootDir);
            $zip->close();
        }
    }
    if (!file_exists($rootDir . '/vendor/autoload.php') && function_exists('exec')) {
        @exec('tar.exe -xf ' . escapeshellarg($zipFile) . ' -C ' . escapeshellarg($rootDir));
    }
}

// 4. Auto-create & import escr_mis database on XAMPP if not yet imported
$dbFlagFile = $rootDir . '/storage/framework/db_initialized.flag';
if (!file_exists($dbFlagFile) && file_exists($rootDir . '/database/escr_mis.sql') && class_exists('PDO')) {
    try {
        $pdo = new \PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `escr_mis` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $tableCheck = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'escr_mis' AND table_name = 'users'")->fetchColumn();
        if ((int) $tableCheck === 0) {
            $mysqlExe = 'C:\\xampp\\mysql\\bin\\mysql.exe';
            $sqlFile = $rootDir . '/database/escr_mis.sql';
            if (file_exists($mysqlExe) && function_exists('exec')) {
                @exec('"' . $mysqlExe . '" -u root escr_mis < "' . $sqlFile . '"');
            }
        }
        @file_put_contents($dbFlagFile, date('c'));
    } catch (\Throwable $e) {
        // Ignore if MySQL is using custom credentials
    }
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Normalize subfolder URL casing on Windows/XAMPP (e.g., /mis/public -> /MIS/public)
if (isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
    $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    if ($baseDir !== '' && stripos($_SERVER['REQUEST_URI'], $baseDir) === 0) {
        $_SERVER['REQUEST_URI'] = $baseDir . substr($_SERVER['REQUEST_URI'], strlen($baseDir));
    }
}

$app->handleRequest(Request::capture());
