<?php
// student/dashboard.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../auth/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$summaryStmt = $pdo->prepare('SELECT status, COUNT(*) AS total FROM appointments WHERE student_id = ? GROUP BY status');
$summaryStmt->execute([$student_id]);
$appointmentSummary = $summaryStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$upcomingStmt = $pdo->prepare("SELECT appointment_date, appointment_time, reason, status FROM appointments WHERE student_id = ? AND appointment_date >= CURDATE() AND status IN ('Pending', 'Confirmed') ORDER BY appointment_date ASC, appointment_time ASC LIMIT 5");
$upcomingStmt->execute([$student_id]);
$upcomingAppointments = $upcomingStmt->fetchAll();
$recentRecordStmt = $pdo->prepare('SELECT checkup_date, diagnosis, treatment FROM dental_records WHERE student_id = ? ORDER BY checkup_date DESC, id DESC LIMIT 3');
$recentRecordStmt->execute([$student_id]);
$recentRecords = $recentRecordStmt->fetchAll();
require_once __DIR__ . '/_layout.php';
render_student_layout_start('Overview', 'overview', 'A quick view of your appointments and dental care.');
?>
<section class="summary-grid" aria-label="Appointment summary">
    <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $summaryStatus): ?>
        <a class="summary-item summary-link" href="appointments.php?status=<?php echo rawurlencode($summaryStatus); ?>"><strong><?php echo (int) ($appointmentSummary[$summaryStatus] ?? 0); ?></strong><span><?php echo html_escape($summaryStatus); ?> appointments</span></a>
    <?php endforeach; ?>
</section>

<div class="overview-grid">
    <section class="chart-panel" aria-labelledby="upcoming-appointments-title">
        <div class="section-heading">
            <h2 id="upcoming-appointments-title">Upcoming appointments</h2>
            <a href="appointments.php">View all</a>
        </div>
        <table class="report-table">
            <thead><tr><th>Date</th><th>Time</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($upcomingAppointments as $appointment): ?>
                    <tr>
                        <td><?php echo html_escape($appointment['appointment_date']); ?></td>
                        <td><?php echo html_escape(substr($appointment['appointment_time'], 0, 5)); ?></td>
                        <td><?php echo html_escape($appointment['reason']); ?></td>
                        <td><span class="badge badge-<?php echo html_escape(strtolower($appointment['status'])); ?>"><?php echo html_escape($appointment['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$upcomingAppointments): ?><tr><td colspan="4">No upcoming appointments.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>

    <section class="chart-panel" aria-labelledby="recent-dental-history-title">
        <div class="section-heading">
            <h2 id="recent-dental-history-title">Recent dental history</h2>
            <a href="history.php">View all</a>
        </div>
        <table class="report-table">
            <thead><tr><th>Checkup date</th><th>Diagnosis</th><th>Treatment</th></tr></thead>
            <tbody>
                <?php foreach ($recentRecords as $record): ?>
                    <tr>
                        <td><?php echo html_escape($record['checkup_date']); ?></td>
                        <td><?php echo html_escape($record['diagnosis']); ?></td>
                        <td><?php echo html_escape($record['treatment']); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recentRecords): ?><tr><td colspan="3">No dental records are available.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<section class="quick-actions" aria-label="Quick actions">
    <a class="quick-action" href="book_appointment.php"><i data-lucide="calendar-plus-2"></i><span><strong>Book a visit</strong><small>Request an appointment</small></span><i data-lucide="arrow-up-right"></i></a>
    <a class="quick-action" href="appointments.php"><i data-lucide="calendar-days"></i><span><strong>Appointments</strong><small>Review your requests</small></span><i data-lucide="arrow-up-right"></i></a>
    <a class="quick-action" href="history.php"><i data-lucide="files"></i><span><strong>Dental history</strong><small>Review examinations</small></span><i data-lucide="arrow-up-right"></i></a>
</section>
<?php render_student_layout_end(); exit; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dental Portal</title>
    <script src="../assets/js/script.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar" aria-label="Student portal navigation">
            <a class="sidebar-brand" href="../index.php">
                <span class="brand-mark"><i data-lucide="heart-pulse"></i></span>
                <span>School Dental<br>Care</span>
            </a>
            <div class="sidebar-user">
                <span>STUDENT PORTAL</span>
                <strong><?php echo html_escape($_SESSION['name']); ?></strong>
            </div>
            <nav class="sidebar-nav" aria-label="Student portal">
                <a class="is-active" href="#overview"><i data-lucide="layout-dashboard"></i><span>Overview</span></a>
                <a href="#booking"><i data-lucide="calendar-plus-2"></i><span>Book a visit</span></a>
                <a href="#appointments"><i data-lucide="calendar-days"></i><span>Appointments</span></a>
                <a href="#history"><i data-lucide="files"></i><span>Dental history</span></a>
            </nav>
            <div class="sidebar-footer">
                <button class="theme-toggle" type="button" data-theme-toggle>
                    <span data-theme-icon></span><span data-theme-label>Dark mode</span>
                </button>
                <form method="POST" action="../auth/logout.php" class="no-print">
                    <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                    <button type="submit" class="button-secondary"><i data-lucide="log-out"></i>Sign out</button>
                </form>
            </div>
        </aside>
        <main class="main-content student-dashboard">
        <header class="page-header">
            <div>
                <p class="eyebrow">Student portal</p>
                <h1>Your dental care</h1>
                <p>Welcome, <strong><?php echo html_escape($_SESSION['name']); ?></strong></p>
            </div>
        </header>

        <?php if ($success !== ''): ?><p class="success"><?php echo html_escape($success); ?></p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>

        <section class="summary-grid" id="overview" aria-label="Appointment summary">
            <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $summaryStatus): ?>
                <div class="summary-item"><strong><?php echo (int) ($appointmentSummary[$summaryStatus] ?? 0); ?></strong><span><?php echo html_escape($summaryStatus); ?></span></div>
            <?php endforeach; ?>
        </section>

        <section class="form-panel" id="booking">
        <h3>Book a dental appointment</h3>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
            <input type="hidden" name="book_appointment" value="1">
            <div class="field-row">
                <div>
                    <label for="appointment_date">Preferred date</label>
                    <input id="appointment_date" type="date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div>
                    <label for="appointment_time">Preferred time</label>
                    <input id="appointment_time" type="time" name="appointment_time" required>
                </div>
            </div>
            <label for="reason">Reason for visit</label>
            <input id="reason" type="text" name="reason" placeholder="e.g. Toothache, routine cleaning, consultation" required>
            
            <button type="submit">Schedule Appointment</button>
        </form>
        </section>

        <hr>

        <div class="section-heading" id="appointments">
            <h3>Your Dental Appointments</h3>
            <button type="button" class="button-secondary no-print" onclick="window.print()"><i data-lucide="printer"></i>Print Preview</button>
        </div>
        <form method="GET" class="filter-bar no-print">
            <div><label for="date_from">From</label><input id="date_from" type="date" name="date_from" value="<?php echo html_escape($dateFrom); ?>"></div>
            <div><label for="date_to">To</label><input id="date_to" type="date" name="date_to" value="<?php echo html_escape($dateTo); ?>"></div>
            <div><label for="status">Status</label><select id="status" name="status">
                <option value="">All statuses</option>
                <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $option): ?>
                    <option value="<?php echo $option; ?>" <?php echo $statusFilter === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select></div>
            <button type="submit">Filter</button>
            <a class="filter-reset" href="dashboard.php">Reset</a>
        </form>
        <table>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Reason</th>
                <th>Status</th>
                <th class="no-print">Action</th>
            </tr>
            <?php foreach ($appointments as $app): ?>
            <tr>
                <td><?php echo html_escape($app['appointment_date']); ?></td>
                <td><?php echo html_escape(substr($app['appointment_time'], 0, 5)); ?></td>
                <td><?php echo html_escape($app['reason']); ?></td>
                <td><span class="badge badge-<?php echo html_escape(strtolower($app['status'])); ?>"><?php echo html_escape($app['status']); ?></span></td>
                <td class="no-print action-cell">
                    <?php if (in_array($app['status'], ['Pending', 'Cancelled'], true)): ?>
                        <form method="POST" onsubmit="return confirm('Remove this appointment?')">
                            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                            <input type="hidden" name="appointment_id" value="<?php echo (int) $app['id']; ?>">
                            <button type="submit" name="delete_appointment" class="button-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$appointments): ?><tr><td colspan="5">No appointments match these filters.</td></tr><?php endif; ?>
        </table>

        <hr>

        <h3 id="history">Your Dental History &amp; Examination Records</h3>
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
                <td><?php echo html_escape($rec['checkup_date']); ?></td>
                <td><?php echo html_escape($rec['dentist_name']); ?></td>
                <td><?php echo html_escape($rec['diagnosis']); ?></td>
                <td><?php echo html_escape($rec['treatment']); ?></td>
                <td><?php echo html_escape($rec['remarks']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$records): ?><tr><td colspan="5">No dental records are available.</td></tr><?php endif; ?>
        </table>
        </main>
    </div>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</body>
</html>