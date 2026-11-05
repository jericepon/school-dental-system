<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../config/backup.php';

if (isset($_GET['download'])) {
    $filename = basename((string) $_GET['download']);
    if (!valid_system_backup_filename($filename)) {
        http_response_code(404);
        exit;
    }
    $directory = realpath(system_backup_directory());
    $filePath = realpath($directory . DIRECTORY_SEPARATOR . $filename);
    if (!$filePath || dirname($filePath) !== $directory) {
        http_response_code(404);
        exit;
    }

    audit_log($pdo, (int) $_SESSION['user_id'], $_SESSION['name'], 'system_backup_downloaded', 'backup');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    readfile($filePath);
    exit;
}

$message = $_SESSION['flash_success'] ?? '';
$error = '';
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } elseif (isset($_POST['create_backup'])) {
        try {
            $filename = create_system_backup($pdo);
            audit_log($pdo, (int) $_SESSION['user_id'], $_SESSION['name'], 'system_backup_created', 'backup');
            $_SESSION['flash_success'] = 'Backup created: ' . $filename;
            header('Location: backup.php');
            exit;
        } catch (Throwable $exception) {
            error_log('Dental system backup failed: ' . $exception->getMessage());
            $error = 'Backup creation failed. Confirm the PHP Zip extension is enabled and check server logs.';
        }
    } elseif (isset($_POST['restore_backup'])) {
        try {
            $filename = (string) ($_POST['backup_file'] ?? '');
            restore_system_backup($pdo, $filename);
            audit_log($pdo, null, $_SESSION['name'], 'system_backup_restored', 'backup');
            $_SESSION['flash_success'] = 'System restored from ' . $filename . '.';
            header('Location: backup.php');
            exit;
        } catch (Throwable $exception) {
            error_log('Dental system restore failed: ' . $exception->getMessage());
            $error = 'Restore failed. The backup may be invalid or incompatible with this version.';
        }
    }
}

$backups = list_system_backups();
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Backup & restore', 'backup', 'Create and manage protected application backups.');
?>
<section class="backup-page">
        <?php if ($message !== ''): ?><p class="success"><?php echo html_escape($message); ?></p><?php endif; ?>
        <?php if ($error !== ''): ?><p class="error"><?php echo html_escape($error); ?></p><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
            <button type="submit" name="create_backup" value="1"><i data-lucide="archive"></i>Create backup</button>
        </form>

        <h2>Available backups</h2>
        <?php if ($backups): ?>
            <table>
                <tr><th>Archive</th><th>Size</th><th class="no-print">Actions</th></tr>
                <?php foreach ($backups as $backup): ?>
                    <?php $backupPath = system_backup_directory() . DIRECTORY_SEPARATOR . $backup; ?>
                    <tr>
                        <td><?php echo html_escape($backup); ?></td>
                        <td><?php echo number_format(filesize($backupPath) / 1048576, 2); ?> MB</td>
                        <td class="no-print action-cell">
                            <a href="?download=<?php echo rawurlencode($backup); ?>">Download</a>
                            <form method="POST" onsubmit="return confirm('Restore will replace current database data and application files. Continue?')">
                                <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                                <input type="hidden" name="backup_file" value="<?php echo html_escape($backup); ?>">
                                <button type="submit" name="restore_backup" value="1" class="button-danger button-compact">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No backups have been created yet.</p>
        <?php endif; ?>
        <p class="backup-warning">Restoring replaces all current users, appointments, dental records, activity history, and login attempt data.</p>
    </section>
    <?php render_dentist_layout_end(); ?>