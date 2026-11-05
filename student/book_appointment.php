<?php
require_once __DIR__ . '/_bootstrap.php';

$error = '';
$appointmentDate = $_POST['appointment_date'] ?? '';
$appointmentTime = $_POST['appointment_time'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $parsedDate = DateTime::createFromFormat('Y-m-d', $appointmentDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $appointmentDate || $appointmentDate < date('Y-m-d') || $appointmentTime === '' || $reason === '' || strlen($reason) > 255) {
            $error = 'Enter a valid future date, time, and reason (up to 255 characters).';
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (student_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->execute([$student_id, $appointmentDate, $appointmentTime, $reason]);
            $appointmentId = (int) $pdo->lastInsertId();
            audit_log($pdo, $student_id, $_SESSION['name'], 'appointment_created', 'appointment', $appointmentId);
            $_SESSION['flash_success'] = 'Appointment requested successfully!';
            header('Location: appointments.php');
            exit;
        }
    }
}

require_once __DIR__ . '/_layout.php';
render_student_layout_start('Book a visit', 'booking', 'Request a dental appointment at the clinic.');
?>
<?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
<section class="form-panel record-form-panel">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
        <div class="field-row">
            <div>
                <label for="appointment_date">Preferred date</label>
                <input id="appointment_date" type="date" name="appointment_date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo html_escape($appointmentDate); ?>" required>
            </div>
            <div>
                <label for="appointment_time">Preferred time</label>
                <input id="appointment_time" type="time" name="appointment_time" value="<?php echo html_escape($appointmentTime); ?>" required>
            </div>
        </div>
        <label for="reason">Reason for visit</label>
        <input id="reason" type="text" name="reason" maxlength="255" value="<?php echo html_escape($reason); ?>" placeholder="e.g. Toothache, routine cleaning, consultation" required>
        <button type="submit"><i data-lucide="calendar-plus-2"></i>Request appointment</button>
    </form>
</section>
<?php render_student_layout_end(); ?>