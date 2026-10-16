<?php
// dentist/dashboard.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dentist') {
    header("Location: ../auth/login.php");
    exit;
}

$dentist_id = $_SESSION['user_id'];
$msg = '';

// Update Appointment Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_appointment'])) {
    $app_id = $_POST['appointment_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE appointments SET status = ?, dentist_id = ? WHERE id = ?");
    $stmt->execute([$status, $dentist_id, $app_id]);
    $msg = "Appointment status updated!";
}

// Add Dental Record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $student_id = $_POST['student_id'];
    $date = $_POST['checkup_date'];
    $diagnosis = trim($_POST['diagnosis']);
    $treatment = trim($_POST['treatment']);
    $remarks = trim($_POST['remarks']);

    $stmt = $pdo->prepare("INSERT INTO dental_records (student_id, dentist_id, checkup_date, diagnosis, treatment, remarks) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $dentist_id, $date, $diagnosis, $treatment, $remarks]);
    $msg = "Dental record added successfully!";
}

// Fetch Appointments
$appointments = $pdo->query("
    SELECT appointments.*, users.name as student_name 
    FROM appointments 
    JOIN users ON appointments.student_id = users.id 
    ORDER BY appointment_date ASC
")->fetchAll();

// Fetch Students List
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dentist Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 900px;">
        <h2>Dentist Portal - Clinic Management</h2>
        <p>Logged in as: <strong>Dr. <?php echo htmlspecialchars($_SESSION['name']); ?></strong> | <a href="../auth/logout.php">Logout</a></p>
        <hr>

        <?php if (!empty($msg)): ?><p class="success"><?php echo $msg; ?></p><?php endif; ?>

        <h3>Manage Student Appointments</h3>
        <table>
            <tr>
                <th>Student</th>
                <th>Date & Time</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($appointments as $app): ?>
            <tr>
                <td><?php echo htmlspecialchars($app['student_name']); ?></td>
                <td><?php echo $app['appointment_date'] . ' ' . $app['appointment_time']; ?></td>
                <td><?php echo htmlspecialchars($app['reason']); ?></td>
                <td><span class="badge badge-<?php echo strtolower($app['status']); ?>"><?php echo $app['status']; ?></span></td>
                <td>
                    <form method="POST" style="margin:0; display:flex; gap:5px;">
                        <input type="hidden" name="update_appointment" value="1">
                        <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                        <select name="status" style="margin:0; padding:4px;">
                            <option value="Pending" <?php if($app['status']=='Pending') echo 'selected'; ?>>Pending</option>
                            <option value="Confirmed" <?php if($app['status']=='Confirmed') echo 'selected'; ?>>Confirmed</option>
                            <option value="Completed" <?php if($app['status']=='Completed') echo 'selected'; ?>>Completed</option>
                            <option value="Cancelled" <?php if($app['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button type="submit" style="width: auto; padding: 4px 8px; margin:0;">Save</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <hr>

        <h3>Create New Student Dental Record</h3>
        <form method="POST">
            <input type="hidden" name="add_record" value="1">
            <label>Select Student:</label>
            <select name="student_id" required>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label>Examination Date:</label>
            <input type="date" name="checkup_date" value="<?php echo date('Y-m-d'); ?>" required>

            <label>Diagnosis:</label>
            <textarea name="diagnosis" rows="2" required></textarea>

            <label>Treatment Rendered:</label>
            <textarea name="treatment" rows="2" required></textarea>

            <label>Clinical Remarks / Recommendations:</label>
            <textarea name="remarks" rows="2"></textarea>

            <button type="submit">Save Dental Record</button>
        </form>
    </div>
</body>
</html>