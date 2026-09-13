<?php
session_start(); // Har file mein session yahi se start hoga
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$db   = 'firstdoctor';
$user = 'root'; 
$pass = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Helper for env
function loadEnv($filePath) {
    if (!file_exists($filePath)) return;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value, '"\' ')));
    }
}
loadEnv(dirname(__DIR__) . '/.env');
?>