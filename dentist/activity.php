<?php
require_once __DIR__ . '/_bootstrap.php';
$activityLogs = $pdo->query('SELECT user_name, action, entity, entity_id, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 100')->fetchAll();
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Activity history', 'activity', 'Recent changes and actions across the clinic system.');
?>
<table class="report-table">
    <thead><tr><th>Date and time</th><th>User</th><th>Activity</th><th>Record</th></tr></thead>
    <tbody>
        <?php foreach ($activityLogs as $log): ?>
            <tr>
                <td><?php echo html_escape($log['created_at']); ?></td>
                <td><?php echo html_escape($log['user_name'] ?? 'System/unknown user'); ?></td>
                <td><?php echo html_escape(str_replace('_', ' ', $log['action'])); ?></td>
                <td><?php echo html_escape($log['entity'] ?? ''); ?><?php echo $log['entity_id'] ? ' #' . (int) $log['entity_id'] : ''; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$activityLogs): ?><tr><td colspan="4">No activity has been recorded yet.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php render_dentist_layout_end(); ?>