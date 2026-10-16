<?php
// database/seed.php
$host = 'localhost';
$db_name = 'dental_care_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear existing data
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE dental_records;");
    $pdo->exec("TRUNCATE TABLE appointments;");
    $pdo->exec("TRUNCATE TABLE users;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    $default_pass = password_hash('password123', PASSWORD_DEFAULT);

    // Seed Student & Dentist
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    $stmt->execute(['Alex Rivera', 'student@college.edu', $default_pass, 'student']);
    $student_id = $pdo->lastInsertId();

    $stmt->execute(['Dr. Sarah Jenkins', 'dentist@college.edu', $default_pass, 'dentist']);
    $dentist_id = $pdo->lastInsertId();

    // Seed Sample Appointment
    $stmtApp = $pdo->prepare("INSERT INTO appointments (student_id, dentist_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtApp->execute([$student_id, $dentist_id, date('Y-m-d', strtotime('+2 days')), '10:00:00', 'Routine Dental Cleaning and Oral Screening', 'Confirmed']);

    // Seed Sample Dental Record
    $stmtRec = $pdo->prepare("INSERT INTO dental_records (student_id, dentist_id, checkup_date, diagnosis, treatment, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtRec->execute([$student_id, $dentist_id, date('Y-m-d', strtotime('-1 month')), 'Mild Gingivitis on lower molars', 'Prophylaxis and Fluoride application', 'Advised flossing twice daily and follow-up in 6 months.']);

    echo "<h3 style='color: #0d9488;'>Dental Database Seeded Successfully!</h3>";
    echo "<p><strong>Test Student Account:</strong> student@college.edu / password123</p>";
    echo "<p><strong>Test Dentist Account:</strong> dentist@college.edu / password123</p>";
    echo "<p><a href='../auth/login.php'>Proceed to Login</a></p>";

} catch (PDOException $e) {
    die("<h3 style='color: red;'>Seeding Error: " . $e->getMessage() . "</h3>");
}
?>