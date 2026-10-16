<?php
// index.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Dental Records & Appointment System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="text-align: center; margin-top: 60px;">
        <h2>School Dental Care & Appointment Portal</h2>
        <p>Maintains student dental health records and simplifies appointment scheduling.</p>
        <hr style="margin: 20px 0;">
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>!</p>
            <?php if ($_SESSION['role'] === 'student'): ?>
                <a href="student/dashboard.php"><button type="button">Go to Student Dental Portal</button></a>
            <?php else: ?>
                <a href="dentist/dashboard.php"><button type="button">Go to Dentist Dashboard</button></a>
            <?php endif; ?>
            <p style="margin-top: 15px;"><a href="auth/logout.php">Logout</a></p>
        <?php else: ?>
            <p>Please log in or register to book a dental checkup and view your oral health records.</p>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
                <a href="auth/login.php" style="width: 45%;"><button type="button">Login</button></a>
                <a href="auth/register.php" style="width: 45%;"><button type="button" style="background-color: #0f766e;">Register</button></a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>