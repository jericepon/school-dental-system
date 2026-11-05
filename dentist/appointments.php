<?php
require_once __DIR__ . '/_bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        if ($appointmentId && in_array($status, ['Pending', 'Confirmed', 'Completed', 'Cancelled'], true)) {
            $stmt = $pdo->prepare('UPDATE appointments SET status = ?, dentist_id = ? WHERE id = ?');
            $stmt->execute([$status, $dentist_id, $appointmentId]);
            audit_log($pdo, $dentist_id, $_SESSION['name'], 'appointment_status_updated_' . strtolower($status), 'appointment', $appointmentId);
            $_SESSION['flash_success'] = 'Appointment status updated.';
            $returnQuery = $_SERVER['QUERY_STRING'] ?? '';
            header('Location: appointments.php' . ($returnQuery !== '' ? '?' . $returnQuery : ''));
            exit;
        }
        $error = 'Choose a valid appointment status.';
    }
}

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$appointmentSql = 'SELECT appointments.*, users.name AS student_name, users.email AS student_email FROM appointments JOIN users ON appointments.student_id = users.id WHERE 1 = 1';
$appointmentParams = [];
if ($dateFrom !== '') {
    $appointmentSql .= ' AND appointments.appointment_date >= ?';
    $appointmentParams[] = $dateFrom;
}
if ($dateTo !== '') {
    $appointmentSql .= ' AND appointments.appointment_date <= ?';
    $appointmentParams[] = $dateTo;
}
if (in_array($statusFilter, ['Pending', 'Confirmed', 'Completed', 'Cancelled'], true)) {
    $appointmentSql .= ' AND appointments.status = ?';
    $appointmentParams[] = $statusFilter;
}
if ($search !== '') {
    $appointmentSql .= ' AND (users.name LIKE ? OR users.email LIKE ? OR appointments.reason LIKE ?)';
    array_push($appointmentParams, '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
}
$appointmentSql .= ' ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC';
$appointmentStmt = $pdo->prepare($appointmentSql);
$appointmentStmt->execute($appointmentParams);
$appointments = $appointmentStmt->fetchAll();
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Appointments', 'appointments', 'Review requests and update appointment status.');
?>
<?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
<form method="GET" class="filter-bar no-print">
    <div><label for="date_from">Date from</label><input id="date_from" type="date" name="date_from" value="<?php echo html_escape($dateFrom); ?>"></div>
    <div><label for="date_to">Date to</label><input id="date_to" type="date" name="date_to" value="<?php echo html_escape($dateTo); ?>"></div>
    <div><label for="status">Status</label><select id="status" name="status">
        <option value="">All statuses</option>
        <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $option): ?>
            <option value="<?php echo $option; ?>" <?php echo $statusFilter === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
        <?php endforeach; ?>
    </select></div>
    <div><label for="search">Student or reason</label><input id="search" type="search" name="search" value="<?php echo html_escape($search); ?>" placeholder="Name, email, reason"></div>
    <button type="submit">Apply filters</button>
    <a class="filter-reset" href="appointments.php">Reset</a>
</form>
<table class="report-table">
    <thead><tr><th>Student</th><th>Date &amp; time</th><th>Reason</th><th>Status</th><th class="no-print">Action</th></tr></thead>
    <tbody>
        <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?php echo html_escape($appointment['student_name']); ?><br><small><?php echo html_escape($appointment['student_email']); ?></small></td>
                <td><?php echo html_escape($appointment['appointment_date'] . ' ' . substr($appointment['appointment_time'], 0, 5)); ?></td>
                <td><?php echo html_escape($appointment['reason']); ?></td>
                <td><span class="badge badge-<?php echo html_escape(strtolower($appointment['status'])); ?>"><?php echo html_escape($appointment['status']); ?></span></td>
                <td class="no-print action-cell">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                        <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['id']; ?>">
                        <select name="status" aria-label="Status for appointment with <?php echo html_escape($appointment['student_name']); ?>">
                            <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo $appointment['status'] === $option ? 'selected' : ''; ?>><?php echo $option; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button-compact"><i data-lucide="save"></i>Save</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="5">No appointments match these filters.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php render_dentist_layout_end(); ?>