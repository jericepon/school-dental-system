<?php
// database/migrate.php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS dental_care_db");
    $pdo->exec("USE dental_care_db");

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

    echo "<h3 style='color: #0d9488;'>Dental System Migration Completed Successfully!</h3>";
    echo "<p><a href='seed.php'>Run Database Seed</a> | <a href='../auth/login.php'>Go to Login</a></p>";

} catch (PDOException $e) {
    die("<h3 style='color: red;'>Migration Error: " . $e->getMessage() . "</h3>");
}
?>