<?php
require_once __DIR__ . '/_bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $recordId = filter_var($_POST['record_id'] ?? null, FILTER_VALIDATE_INT);
        if ($recordId) {
            $stmt = $pdo->prepare('DELETE FROM dental_records WHERE id = ?');
            $stmt->execute([$recordId]);
            if ($stmt->rowCount()) {
                audit_log($pdo, $dentist_id, $_SESSION['name'], 'dental_record_deleted', 'dental_record', $recordId);
                $_SESSION['flash_success'] = 'Dental record deleted.';
                $returnQuery = $_SERVER['QUERY_STRING'] ?? '';
                header('Location: records.php' . ($returnQuery !== '' ? '?' . $returnQuery : ''));
                exit;
            }
        }
        $error = 'The dental record could not be deleted.';
    }
}

$recordDateFrom = trim($_GET['record_date_from'] ?? '');
$recordDateTo = trim($_GET['record_date_to'] ?? '');
$recordStudent = filter_var($_GET['record_student'] ?? '', FILTER_VALIDATE_INT) ?: '';
$recordSql = 'SELECT dental_records.*, students.name AS student_name, dentists.name AS dentist_name FROM dental_records JOIN users AS students ON dental_records.student_id = students.id JOIN users AS dentists ON dental_records.dentist_id = dentists.id WHERE 1 = 1';
$recordParams = [];
if ($recordDateFrom !== '') {
    $recordSql .= ' AND dental_records.checkup_date >= ?';
    $recordParams[] = $recordDateFrom;
}
if ($recordDateTo !== '') {
    $recordSql .= ' AND dental_records.checkup_date <= ?';
    $recordParams[] = $recordDateTo;
}
if ($recordStudent !== '') {
    $recordSql .= ' AND dental_records.student_id = ?';
    $recordParams[] = $recordStudent;
}
$recordSql .= ' ORDER BY dental_records.checkup_date DESC';
$recordStmt = $pdo->prepare($recordSql);
$recordStmt->execute($recordParams);
$records = $recordStmt->fetchAll();
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student' ORDER BY name")->fetchAll();
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Dental records', 'records', 'Browse, filter, edit, and remove examination records.');
?>
<?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>
<form method="GET" class="filter-bar no-print">
    <div><label for="record_date_from">Date from</label><input id="record_date_from" type="date" name="record_date_from" value="<?php echo html_escape($recordDateFrom); ?>"></div>
    <div><label for="record_date_to">Date to</label><input id="record_date_to" type="date" name="record_date_to" value="<?php echo html_escape($recordDateTo); ?>"></div>
    <div><label for="record_student">Student</label><select id="record_student" name="record_student"><option value="">All students</option>
        <?php foreach ($students as $student): ?><option value="<?php echo (int) $student['id']; ?>" <?php echo (string) $recordStudent === (string) $student['id'] ? 'selected' : ''; ?>><?php echo html_escape($student['name']); ?></option><?php endforeach; ?>
    </select></div>
    <button type="submit">Apply filters</button>
    <a class="filter-reset" href="records.php">Reset</a>
</form>
<table class="report-table">
    <thead><tr><th>Date</th><th>Student</th><th>Dentist</th><th>Diagnosis</th><th>Treatment</th><th class="no-print">Actions</th></tr></thead>
    <tbody>
        <?php foreach ($records as $record): ?>
            <tr>
                <td><?php echo html_escape($record['checkup_date']); ?></td>
                <td><?php echo html_escape($record['student_name']); ?></td>
                <td><?php echo html_escape($record['dentist_name']); ?></td>
                <td><?php echo html_escape($record['diagnosis']); ?></td>
                <td><?php echo html_escape($record['treatment']); ?></td>
                <td class="no-print action-cell">
                    <a href="edit_record.php?id=<?php echo (int) $record['id']; ?>"><i data-lucide="pencil"></i>Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this dental record?')">
                        <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                        <input type="hidden" name="record_id" value="<?php echo (int) $record['id']; ?>">
                        <button type="submit" name="delete_record" class="button-danger button-compact"><i data-lucide="trash-2"></i>Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$records): ?><tr><td colspan="6">No dental records match these filters.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php render_dentist_layout_end(); ?>