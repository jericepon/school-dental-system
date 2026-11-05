<?php
require_once __DIR__ . '/_bootstrap.php';

$error = '';
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name")->fetchAll();
$studentIdValue = $_POST['student_id'] ?? '';
$recordDate = $_POST['checkup_date'] ?? date('Y-m-d');
$diagnosis = trim($_POST['diagnosis'] ?? '');
$treatment = trim($_POST['treatment'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $studentId = filter_var($studentIdValue, FILTER_VALIDATE_INT);
        $parsedDate = DateTime::createFromFormat('Y-m-d', $recordDate);
        $studentCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
        $studentCheck->execute([$studentId]);

        if (!$studentId || !$studentCheck->fetchColumn() || !$parsedDate || $parsedDate->format('Y-m-d') !== $recordDate || $recordDate > date('Y-m-d') || $diagnosis === '' || $treatment === '') {
            $error = 'Choose a student and enter a valid examination date, diagnosis, and treatment.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO dental_records (student_id, dentist_id, checkup_date, diagnosis, treatment, remarks) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$studentId, $dentist_id, $recordDate, $diagnosis, $treatment, $remarks]);
            $recordId = (int) $pdo->lastInsertId();
            audit_log($pdo, $dentist_id, $_SESSION['name'], 'dental_record_created', 'dental_record', $recordId);
            $_SESSION['flash_success'] = 'Dental record added successfully.';
            header('Location: records.php');
            exit;
        }
    }
}

require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('New dental record', 'new-record', 'Document a student examination and treatment.');
?>
<?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
<section class="form-panel record-form-panel">
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
        <label for="student_id">Select student</label>
        <select id="student_id" name="student_id" required>
            <option value="">Choose a student</option>
            <?php foreach ($students as $student): ?>
                <option value="<?php echo (int) $student['id']; ?>" <?php echo (string) $studentIdValue === (string) $student['id'] ? 'selected' : ''; ?>><?php echo html_escape($student['name']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="checkup_date">Examination date</label>
        <input id="checkup_date" type="date" name="checkup_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo html_escape($recordDate); ?>" required>

        <label for="diagnosis">Diagnosis</label>
        <textarea id="diagnosis" name="diagnosis" rows="3" required><?php echo html_escape($diagnosis); ?></textarea>

        <label for="treatment">Treatment rendered</label>
        <textarea id="treatment" name="treatment" rows="3" required><?php echo html_escape($treatment); ?></textarea>

        <label for="remarks">Clinical remarks / recommendations</label>
        <textarea id="remarks" name="remarks" rows="3"><?php echo html_escape($remarks); ?></textarea>

        <button type="submit"><i data-lucide="save"></i>Save dental record</button>
    </form>
</section>
<?php render_dentist_layout_end(); ?>