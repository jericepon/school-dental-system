<?php
// config/database.php

$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'johnwincellopez_schooldental';
$username = getenv('DB_USER') ?: 'johnwincellopez_dentaluser';
$password = getenv('DB_PASSWORD') ?: 'P@ssword@123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Dental system database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Database connection failed. Check the server configuration and logs.');
}
?>