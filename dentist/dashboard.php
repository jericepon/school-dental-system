<?php
// dentist/dashboard.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dentist') {
    header("Location: ../auth/login.php");
    exit;
}

$dentist_id = (int) $_SESSION['user_id'];

$summary = $pdo->query('SELECT status, COUNT(*) AS total FROM appointments GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
$totalAppointments = array_sum(array_map('intval', $summary));
$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalRecords = (int) $pdo->query('SELECT COUNT(*) FROM dental_records')->fetchColumn();
$upcomingAppointments = $pdo->query("SELECT appointments.id, appointments.appointment_date, appointments.appointment_time, appointments.reason, appointments.status, users.name AS student_name FROM appointments JOIN users ON appointments.student_id = users.id WHERE appointments.appointment_date >= CURDATE() AND appointments.status IN ('Pending', 'Confirmed') ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC LIMIT 5")->fetchAll();
$recentRecords = $pdo->query('SELECT dental_records.id, dental_records.checkup_date, dental_records.diagnosis, users.name AS student_name FROM dental_records JOIN users ON dental_records.student_id = users.id ORDER BY dental_records.checkup_date DESC, dental_records.id DESC LIMIT 5')->fetchAll();
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Overview', 'overview', 'Today at a glance and quick access to clinic work.');
?>
<section class="summary-grid" aria-label="Clinic overview statistics">
    <a class="summary-item summary-link" href="appointments.php"><strong><?php echo $totalAppointments; ?></strong><span>Total appointments</span></a>
    <a class="summary-item summary-link" href="appointments.php?status=Pending"><strong><?php echo (int) ($summary['Pending'] ?? 0); ?></strong><span>Pending requests</span></a>
    <a class="summary-item summary-link" href="analytics.php"><strong><?php echo $totalStudents; ?></strong><span>Students</span></a>
    <a class="summary-item summary-link" href="records.php"><strong><?php echo $totalRecords; ?></strong><span>Dental records</span></a>
</section>

<div class="overview-grid">
    <section class="chart-panel" aria-labelledby="upcoming-title">
        <div class="section-heading">
            <h2 id="upcoming-title">Upcoming appointments</h2>
            <a href="appointments.php">View all</a>
        </div>
        <table class="report-table">
            <thead><tr><th>Student</th><th>Date &amp; time</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($upcomingAppointments as $appointment): ?>
                    <tr>
                        <td><?php echo html_escape($appointment['student_name']); ?></td>
                        <td><?php echo html_escape($appointment['appointment_date'] . ' ' . substr($appointment['appointment_time'], 0, 5)); ?></td>
                        <td><span class="badge badge-<?php echo html_escape(strtolower($appointment['status'])); ?>"><?php echo html_escape($appointment['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$upcomingAppointments): ?><tr><td colspan="3">No upcoming appointments.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="chart-panel" aria-labelledby="recent-records-title">
        <div class="section-heading">
            <h2 id="recent-records-title">Recent dental records</h2>
            <a href="records.php">View all</a>
        </div>
        <table class="report-table">
            <thead><tr><th>Student</th><th>Checkup date</th><th>Diagnosis</th></tr></thead>
            <tbody>
                <?php foreach ($recentRecords as $record): ?>
                    <tr>
                        <td><?php echo html_escape($record['student_name']); ?></td>
                        <td><?php echo html_escape($record['checkup_date']); ?></td>
                        <td><?php echo html_escape($record['diagnosis']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentRecords): ?><tr><td colspan="3">No dental records yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<section class="quick-actions" aria-label="Quick actions">
    <a class="quick-action" href="new_record.php"><i data-lucide="file-plus-2"></i><span><strong>New dental record</strong><small>Document an examination</small></span><i data-lucide="arrow-up-right"></i></a>
    <a class="quick-action" href="analytics.php"><i data-lucide="bar-chart-3"></i><span><strong>Clinic statistics</strong><small>Explore trends and charts</small></span><i data-lucide="arrow-up-right"></i></a>
    <a class="quick-action" href="reports.php"><i data-lucide="file-text"></i><span><strong>Summary report</strong><small>Review the last six months</small></span><i data-lucide="arrow-up-right"></i></a>
</section>
<?php render_dentist_layout_end(); exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dentist Dashboard</title>
    <script src="../assets/js/script.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar" aria-label="Dentist dashboard navigation">
            <a class="sidebar-brand" href="../index.php">
                <span class="brand-mark"><i data-lucide="heart-pulse"></i></span>
                <span>School Dental<br>Care</span>
            </a>
            <div class="sidebar-user">
                <span>CLINIC WORKSPACE</span>
                <strong><?php echo html_escape($_SESSION['name']); ?></strong>
            </div>
            <nav class="sidebar-nav" aria-label="Clinic sections">
                <a class="is-active" href="#overview"><i data-lucide="layout-dashboard"></i><span>Overview</span></a>
                <a href="#analytics"><i data-lucide="bar-chart-3"></i><span>Statistics &amp; charts</span></a>
                <a href="#summary-report"><i data-lucide="file-text"></i><span>Summary report</span></a>
                <a href="#appointments"><i data-lucide="calendar-days"></i><span>Appointments</span></a>
                <a href="#new-record"><i data-lucide="file-plus-2"></i><span>New record</span></a>
                <a href="#records"><i data-lucide="files"></i><span>Dental records</span></a>
                <a href="#activity"><i data-lucide="activity"></i><span>Activity</span></a>
                <a href="backup.php"><i data-lucide="archive"></i><span>Backup &amp; Restore</span></a>
            </nav>
            <div class="sidebar-footer">
