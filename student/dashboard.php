<?php
// student/dashboard.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$success = '';

// Handle Appointment Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);

    $stmt = $pdo->prepare("INSERT INTO appointments (student_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->execute([$student_id, $date, $time, $reason]);
    $success = "Appointment requested successfully!";
}

// Fetch Student Appointments
$appStmt = $pdo->prepare("SELECT * FROM appointments WHERE student_id = ? ORDER BY appointment_date DESC");
$appStmt->execute([$student_id]);
$appointments = $appStmt->fetchAll();

// Fetch Student Dental Records
$recStmt = $pdo->prepare("
    SELECT dental_records.*, users.name as dentist_name 
    FROM dental_records 
    JOIN users ON dental_records.dentist_id = users.id 
    WHERE student_id = ? 
    ORDER BY checkup_date DESC
");
$recStmt->execute([$student_id]);
$records = $recStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dental Portal</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Student Dental Portal</h2>
        <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> | <a href="../auth/logout.php">Logout</a></p>
        <hr>

        <?php if (!empty($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>

        <h3>Book a Dental Appointment</h3>
        <form method="POST">
            <input type="hidden" name="book_appointment" value="1">
            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Preferred Date:</label>
                    <input type="date" name="appointment_date" required>
                </div>
                <div style="flex: 1;">
                    <label>Preferred Time:</label>
                    <input type="time" name="appointment_time" required>
                </div>
            </div>
            <label>Reason for Visit:</label>
            <input type="text" name="reason" placeholder="e.g. Toothache, Routine Cleaning, Consultation" required>
            
            <button type="submit">Schedule Appointment</button>
        </form>

        <hr>

        <h3>Your Dental Appointments</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
            <?php foreach ($appointments as $app): ?>
            <tr>
                <td><?php echo $app['appointment_date']; ?></td>
                <td><?php echo $app['appointment_time']; ?></td>
                <td><?php echo htmlspecialchars($app['reason']); ?></td>
                <td><span class="badge badge-<?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h3>Your Dental History & Examination Records</h3>
        <table>
            <tr>
                <th>Checkup Date</th>
                <th>Dentist</th>
                <th>Diagnosis</th>
                <th>Treatment Rendered</th>
                <th>Remarks</th>
            </tr>
            <?php foreach ($records as $rec): ?>
            <tr>
                <td><?php echo $rec['checkup_date']; ?></td>
                <td><?php echo htmlspecialchars($rec['dentist_name']); ?></td>
                <td><?php echo htmlspecialchars($rec['diagnosis']); ?></td>
                <td><?php echo htmlspecialchars($rec['treatment']); ?></td>
                <td><?php echo htmlspecialchars($rec['remarks']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>