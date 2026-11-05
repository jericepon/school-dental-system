<?php
// database/migrate.php
$host = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'dental_care_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $dbName)) {
    http_response_code(500);
    exit('Invalid database name configuration.');
}

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
    $pdo->exec("USE `$dbName`");

    // 1. Users Table (Students, Dentists/Staff)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'dentist') DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Appointments Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        dentist_id INT DEFAULT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        reason VARCHAR(255) NOT NULL,
        status ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (dentist_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // 3. Dental Records Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS dental_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        dentist_id INT NOT NULL,
        checkup_date DATE NOT NULL,
        diagnosis TEXT NOT NULL,
        treatment TEXT NOT NULL,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (dentist_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT DEFAULT NULL,
        user_name VARCHAR(100) DEFAULT NULL,
        action VARCHAR(100) NOT NULL,
        entity VARCHAR(100) DEFAULT NULL,
        entity_id INT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_activity_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_login_attempts_key_time (email, ip_address, attempted_at)
    )");

    echo "<h3 style='color: #0d9488;'>Dental System Migration Completed Successfully!</h3>";
    echo "<p><a href='seed.php'>Run Database Seed</a> | <a href='../auth/login.php'>Go to Login</a></p>";

} catch (PDOException $e) {
    error_log('Dental system migration failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h3>Database migration failed. Check the server logs.</h3>');
}
?>