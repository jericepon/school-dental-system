<?php
require_once __DIR__ . '/_bootstrap.php';
$recordStmt = $pdo->prepare('SELECT dental_records.*, users.name AS dentist_name FROM dental_records JOIN users ON dental_records.dentist_id = users.id WHERE dental_records.student_id = ? ORDER BY dental_records.checkup_date DESC, dental_records.id DESC');
$recordStmt->execute([$student_id]);
$records = $recordStmt->fetchAll();
require_once __DIR__ . '/_layout.php';
render_student_layout_start('Dental history', 'history', 'Your examination history and treatment details.');
?>
<table class="report-table">
    <thead><tr><th>Checkup date</th><th>Dentist</th><th>Diagnosis</th><th>Treatment rendered</th><th>Remarks</th></tr></thead>
    <tbody>
        <?php foreach ($records as $record): ?>
            <tr>
                <td><?php echo html_escape($record['checkup_date']); ?></td>
                <td><?php echo html_escape($record['dentist_name']); ?></td>
                <td><?php echo html_escape($record['diagnosis']); ?></td>
                <td><?php echo html_escape($record['treatment']); ?></td>
                <td><?php echo html_escape($record['remarks']); ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$records): ?><tr><td colspan="5">No dental records are available.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php render_student_layout_end(); ?>