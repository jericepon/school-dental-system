<?php
require_once __DIR__ . '/_bootstrap.php';
$dentistId = $dentist_id;
$requestRecordId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['record_id'] ?? null)
    : ($_GET['id'] ?? null);
$recordId = filter_var($requestRecordId, FILTER_VALIDATE_INT);
if (!$recordId || $recordId < 1) {
    header('Location: dashboard.php');
    exit;
}

$recordStmt = $pdo->prepare('SELECT * FROM dental_records WHERE id = ?');
$recordStmt->execute([$recordId]);
$record = $recordStmt->fetch();
if (!$record) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $studentId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
        $recordDate = $_POST['checkup_date'] ?? '';
        $diagnosis = trim($_POST['diagnosis'] ?? '');
        $treatment = trim($_POST['treatment'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');
        $parsedDate = DateTime::createFromFormat('Y-m-d', $recordDate);
        $studentCheck = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'student'");
        $studentCheck->execute([$studentId]);

        if (!$studentId || !$studentCheck->fetchColumn() || !$parsedDate || $parsedDate->format('Y-m-d') !== $recordDate || $recordDate > date('Y-m-d') || $diagnosis === '' || $treatment === '') {
            $error = 'Choose a student and enter a valid examination date, diagnosis, and treatment.';
        } else {
            $stmt = $pdo->prepare('UPDATE dental_records SET student_id = ?, checkup_date = ?, diagnosis = ?, treatment = ?, remarks = ? WHERE id = ?');
            $stmt->execute([$studentId, $recordDate, $diagnosis, $treatment, $remarks, $recordId]);
            audit_log($pdo, $dentistId, $_SESSION['name'], 'dental_record_updated', 'dental_record', $recordId);
            $_SESSION['flash_success'] = 'Dental record updated.';
            header('Location: records.php');
            exit;
        }
    }
}

$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name")->fetchAll();
$selectedStudentId = $_POST['student_id'] ?? $record['student_id'];
$checkupDate = $_POST['checkup_date'] ?? $record['checkup_date'];
$diagnosis = $_POST['diagnosis'] ?? $record['diagnosis'];
$treatment = $_POST['treatment'] ?? $record['treatment'];
$remarks = $_POST['remarks'] ?? $record['remarks'];
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Edit dental record', 'records', 'Update examination details for this student.');
?>
<section class="form-panel record-form-panel">
        <h2>Edit record #<?php echo (int) $record['id']; ?></h2>
        <?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
            <input type="hidden" name="record_id" value="<?php echo (int) $record['id']; ?>">

            <label for="student_id">Select Student:</label>
            <select id="student_id" name="student_id" required>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo (int) $student['id']; ?>" <?php echo (string) $selectedStudentId === (string) $student['id'] ? 'selected' : ''; ?>><?php echo html_escape($student['name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="checkup_date">Examination Date:</label>
            <input id="checkup_date" type="date" name="checkup_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo html_escape($checkupDate); ?>" required>

            <label for="diagnosis">Diagnosis:</label>
            <textarea id="diagnosis" name="diagnosis" rows="2" required><?php echo html_escape($diagnosis); ?></textarea>

            <label for="treatment">Treatment Rendered:</label>
            <textarea id="treatment" name="treatment" rows="2" required><?php echo html_escape($treatment); ?></textarea>

            <label for="remarks">Clinical Remarks / Recommendations:</label>
            <textarea id="remarks" name="remarks" rows="2"><?php echo html_escape($remarks); ?></textarea>

            <button type="submit"><i data-lucide="save"></i>Update Dental Record</button>
        </form>
        <p><a href="records.php"><i data-lucide="arrow-left"></i>Cancel and return to records</a></p>
    </section>
    <?php render_dentist_layout_end(); ?>