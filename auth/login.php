<?php
// auth/login.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

$error = '';
if (!isset($_SESSION['captcha_answer'])) {
    $_SESSION['captcha_left'] = random_int(1, 9);
    $_SESSION['captcha_right'] = random_int(1, 9);
    $_SESSION['captcha_answer'] = $_SESSION['captcha_left'] + $_SESSION['captcha_right'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } elseif (!empty($email) && !empty($password)) {
        $attemptStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip_address = ? AND attempted_at >= NOW() - INTERVAL 15 MINUTE'
        );
        $attemptStmt->execute([$email, $ipAddress]);

        if ((int) $attemptStmt->fetchColumn() >= 5) {
            $error = 'Too many failed sign-in attempts. Try again in 15 minutes.';
            audit_log($pdo, null, $email, 'login_blocked', 'authentication');
        } elseif (!hash_equals((string) $_SESSION['captcha_answer'], (string) ($_POST['captcha_answer'] ?? ''))) {
            $error = 'The security answer was incorrect.';
            $pdo->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)')->execute([$email, $ipAddress]);
            audit_log($pdo, null, $email, 'login_failed_captcha', 'authentication');
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $pdo->prepare('DELETE FROM login_attempts WHERE email = ? AND ip_address = ?')->execute([$email, $ipAddress]);
                audit_log($pdo, (int) $user['id'], $user['name'], 'login_success', 'authentication');

                if ($user['role'] === 'student') {
                    header('Location: ../student/dashboard.php');
                } else {
                    header('Location: ../dentist/dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
                $pdo->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)')->execute([$email, $ipAddress]);
                audit_log($pdo, null, $email, 'login_failed', 'authentication');
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }

    $_SESSION['captcha_left'] = random_int(1, 9);
    $_SESSION['captcha_right'] = random_int(1, 9);
    $_SESSION['captcha_answer'] = $_SESSION['captcha_left'] + $_SESSION['captcha_right'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dental Care System - Login</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" sizes="16x16 32x32 48x48">
    <script src="../assets/js/script.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="auth-container">
        <p class="eyebrow">School Dental Clinic</p>
        <h1>Welcome back</h1>
        <p>Sign in to manage appointments and dental health records.</p>
        
        <?php if (!empty($error)): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" autocomplete="username" required>
            
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <label for="captcha_answer">Security check: <?php echo (int) $_SESSION['captcha_left']; ?> + <?php echo (int) $_SESSION['captcha_right']; ?> = ?</label>
            <input type="number" id="captcha_answer" name="captcha_answer" required>
            
            <button type="submit"><i data-lucide="log-in"></i>Sign In</button>
        </form>

        <section class="demo-accounts" aria-label="Tester accounts">
            <h3>Tester Accounts</h3>
            <p><strong>Student</strong><br>Email: <code>student@college.edu</code><br>Password: <code>password123</code></p>
            <p><strong>Dentist</strong><br>Email: <code>dentist@college.edu</code><br>Password: <code>password123</code></p>
        </section>
    </main>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</body>
</html>