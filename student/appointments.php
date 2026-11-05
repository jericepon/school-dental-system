<?php
require_once __DIR__ . '/_bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_appointment'])) {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $appointmentId = filter_var($_POST['appointment_id'] ?? null, FILTER_VALIDATE_INT);
        if ($appointmentId) {
            $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ? AND student_id = ? AND status IN ('Pending', 'Cancelled')");
            $stmt->execute([$appointmentId, $student_id]);
            if ($stmt->rowCount()) {
                audit_log($pdo, $student_id, $_SESSION['name'], 'appointment_deleted', 'appointment', $appointmentId);
                $_SESSION['flash_success'] = 'Appointment removed.';
                $returnQuery = $_SERVER['QUERY_STRING'] ?? '';
                header('Location: appointments.php' . ($returnQuery !== '' ? '?' . $returnQuery : ''));
                exit;
            }
        }
        $error = 'Only your pending or cancelled appointments can be removed.';
    }
}

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$appointmentSql = 'SELECT * FROM appointments WHERE student_id = ?';
$appointmentParams = [$student_id];
if ($dateFrom !== '') {
    $appointmentSql .= ' AND appointment_date >= ?';
    $appointmentParams[] = $dateFrom;
}
if ($dateTo !== '') {
    $appointmentSql .= ' AND appointment_date <= ?';
    $appointmentParams[] = $dateTo;
}
if (in_array($statusFilter, ['Pending', 'Confirmed', 'Completed', 'Cancelled'], true)) {
    $appointmentSql .= ' AND status = ?';
    $appointmentParams[] = $statusFilter;
}
$appointmentSql .= ' ORDER BY appointment_date DESC, appointment_time DESC';
$appointmentStmt = $pdo->prepare($appointmentSql);
$appointmentStmt->execute($appointmentParams);
$appointments = $appointmentStmt->fetchAll();

require_once __DIR__ . '/_layout.php';
render_student_layout_start('Appointments', 'appointments', 'Track the status and details of your appointment requests.');
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
    <button type="submit">Apply filters</button>
    <a class="filter-reset" href="appointments.php">Reset</a>
</form>
<div class="section-heading">
    <h2>Your requests</h2>
    <button type="button" class="button-secondary no-print" onclick="window.print()"><i data-lucide="printer"></i>Print</button>
</div>
<table class="report-table">
    <thead><tr><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th class="no-print">Action</th></tr></thead>
    <tbody>
        <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?php echo html_escape($appointment['appointment_date']); ?></td>
                <td><?php echo html_escape(substr($appointment['appointment_time'], 0, 5)); ?></td>
                <td><?php echo html_escape($appointment['reason']); ?></td>
                <td><span class="badge badge-<?php echo html_escape(strtolower($appointment['status'])); ?>"><?php echo html_escape($appointment['status']); ?></span></td>
                <td class="no-print action-cell">
                    <?php if (in_array($appointment['status'], ['Pending', 'Cancelled'], true)): ?>
                        <form method="POST" onsubmit="return confirm('Remove this appointment?')">
                            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                            <input type="hidden" name="appointment_id" value="<?php echo (int) $appointment['id']; ?>">
                            <button type="submit" name="delete_appointment" class="button-danger button-compact"><i data-lucide="trash-2"></i>Remove</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$appointments): ?><tr><td colspan="5">No appointments match these filters.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php render_student_layout_end(); ?>