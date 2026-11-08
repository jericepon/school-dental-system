<?php
// database/seed.php
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'johnwincellopez_schooldental';
$username = getenv('DB_USER') ?: 'johnwincellopez_dentaluser';
$password = getenv('DB_PASSWORD') ?: 'P@ssword@123';

$isCli = PHP_SAPI === 'cli';
$seedToken = getenv('SEED_TOKEN');
if (!is_string($seedToken) || $seedToken === '') {
    $seedConfig = @parse_ini_file(__DIR__ . '/../config/.env', false, INI_SCANNER_RAW);
    $seedToken = is_array($seedConfig) ? ($seedConfig['SEED_TOKEN'] ?? null) : null;
}
$pageMessage = '';
$seedAuthorized = $isCli;

if (!$isCli) {
    require_once __DIR__ . '/../config/security.php';
    start_secure_session();
    header('Cache-Control: no-store, no-cache, must-revalidate');

    if (!empty($_SESSION['seed_completed'])) {
        $pageMessage = 'Database seeding completed.';
        unset($_SESSION['seed_completed']);
    }

    if (!is_string($seedToken) || strlen($seedToken) < 32) {
        http_response_code(503);
        exit('Browser seeding is disabled. Configure a SEED_TOKEN with at least 32 characters to enable it.');
    }

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET' && $method !== 'POST') {
        header('Allow: GET, POST');
        http_response_code(405);
        exit('Method not allowed.');
    }

    if ($method === 'POST') {
        if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('Invalid form token. Reload the page and try again.');
        }

        $providedToken = $_POST['seed_token'] ?? null;
        if (!is_string($providedToken) || !hash_equals($seedToken, $providedToken)) {
            http_response_code(403);
            $pageMessage = 'Invalid seed token.';
        } else {
            $seedAuthorized = true;
        }
    }

    if (!$seedAuthorized) {
        http_response_code($pageMessage === '' ? 200 : 403);
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Database Seeding</title>
        </head>
        <body>
            <main>
                <h1>Database Seeding</h1>
                <?php if ($pageMessage !== ''): ?>
                    <p role="status"><?php echo html_escape($pageMessage); ?></p>
                <?php endif; ?>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                    <label for="seed-token">Seed token</label>
                    <input id="seed-token" name="seed_token" type="password" autocomplete="current-password" required>
                    <button type="submit">Run database seed</button>
                </form>
            </main>
        </body>
        </html>
        <?php
        exit;
    }
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

    if ($isCli) {
        echo "Dental Database Seeded Successfully!\n";
        echo "Test Student Account: student@college.edu / password123\n";
        echo "Test Dentist Account: dentist@college.edu / password123\n";
    } else {
        $_SESSION['seed_completed'] = true;
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'seed.php'), true, 303);
        exit;
    }

} catch (PDOException $e) {
    error_log('Dental system seeding failed: ' . $e->getMessage());
    http_response_code(500);
    die('<h3>Database seeding failed. Check the server logs.</h3>');
}

?>