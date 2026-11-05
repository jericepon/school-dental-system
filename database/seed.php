<?php
// database/seed.php
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'dental_care_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

if (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $default_pass = password_hash('password123', PASSWORD_DEFAULT);

    $userStmt = $pdo->prepare('INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
    $userStmt->execute(['Alex Rivera', 'student@college.edu', $default_pass, 'student']);
    $userStmt->execute(['Dr. Sarah Jenkins', 'dentist@college.edu', $default_pass, 'dentist']);

    $studentStmt = $pdo->prepare("SELECT id FROM users WHERE email = 'student@college.edu' AND role = 'student'");
    $studentStmt->execute();
    $student_id = $studentStmt->fetchColumn();
    $dentistStmt = $pdo->prepare("SELECT id FROM users WHERE email = 'dentist@college.edu' AND role = 'dentist'");
    $dentistStmt->execute();
    $dentist_id = $dentistStmt->fetchColumn();

    if ($student_id && $dentist_id) {
        $appointmentCheck = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE student_id = ?');
        $appointmentCheck->execute([$student_id]);
        if (!(int) $appointmentCheck->fetchColumn()) {
            $stmtApp = $pdo->prepare('INSERT INTO appointments (student_id, dentist_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmtApp->execute([$student_id, $dentist_id, date('Y-m-d', strtotime('+2 days')), '10:00:00', 'Routine Dental Cleaning and Oral Screening', 'Confirmed']);
        }

        $recordCheck = $pdo->prepare('SELECT COUNT(*) FROM dental_records WHERE student_id = ?');
        $recordCheck->execute([$student_id]);
        if (!(int) $recordCheck->fetchColumn()) {
            $stmtRec = $pdo->prepare('INSERT INTO dental_records (student_id, dentist_id, checkup_date, diagnosis, treatment, remarks) VALUES (?, ?, ?, ?, ?, ?)');
            $stmtRec->execute([$student_id, $dentist_id, date('Y-m-d', strtotime('-1 month')), 'Mild Gingivitis on lower molars', 'Prophylaxis and Fluoride application', 'Advised flossing twice daily and follow-up in 6 months.']);
        }
    }

    echo "<h3 style='color: #0d9488;'>Dental Database Seeded Successfully!</h3>";
    echo "<p><strong>Test Student Account:</strong> student@college.edu / password123</p>";
    echo "<p><strong>Test Dentist Account:</strong> dentist@college.edu / password123</p>";
    echo "<p><a href='../auth/login.php'>Proceed to Login</a></p>";

} catch (PDOException $e) {
    error_log('Dental system seeding failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h3>Database seeding failed. Check the server logs.</h3>');
}
?>