<?php
// auth/register.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = 'student';

    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } elseif (!empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($password) >= 10) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$name, $email, $hashed_password, $role])) {
                audit_log($pdo, (int) $pdo->lastInsertId(), $name, 'student_registered', 'user');
                $success = "Registration successful! You can now <a href='login.php'>Login here</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    } else {
        $error = 'Enter a valid email, a name, and a password of at least 10 characters.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration - Dental Portal</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" sizes="16x16 32x32 48x48">
    <script src="../assets/js/script.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="auth-container">
        <p class="eyebrow">School Dental Clinic</p>
        <h1>Create a student account</h1>
        <p>Register to request appointments and view your dental history.</p>
        <?php if (!empty($error)): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
        <?php if (!empty($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
        
        <form action="register.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
            <label for="name">Full name</label>
            <input id="name" type="text" name="name" autocomplete="name" required placeholder="e.g. Maria Santos">

            <label for="email">Email address</label>
            <input id="email" type="email" name="email" autocomplete="email" required placeholder="student@college.edu">
            
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" required>
            
            <button type="submit"><i data-lucide="user-round-plus"></i>Create Account</button>
        </form>
        <p>Already registered? <a href="login.php">Sign in</a></p>
    </main>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</body>
</html>