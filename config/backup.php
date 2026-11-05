<?php
function system_backup_directory(): string
{
    $directory = dirname(__DIR__) . '/storage/backups';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create the protected backup directory.');
    }

    return $directory;
}

function system_backup_tables(): array
{
    return [
        'users' => ['id', 'name', 'email', 'password', 'role', 'created_at'],
        'appointments' => ['id', 'student_id', 'dentist_id', 'appointment_date', 'appointment_time', 'reason', 'status', 'created_at'],
        'dental_records' => ['id', 'student_id', 'dentist_id', 'checkup_date', 'diagnosis', 'treatment', 'remarks', 'created_at'],
        'activity_logs' => ['id', 'user_id', 'user_name', 'action', 'entity', 'entity_id', 'ip_address', 'created_at'],
        'login_attempts' => ['id', 'email', 'ip_address', 'attempted_at'],
    ];
}

function create_system_backup(PDO $pdo): string
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('The PHP Zip extension is required for backups.');
    }

    $backupDirectory = system_backup_directory();
    $filename = 'dental-backup-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.zip';
    $archivePath = $backupDirectory . DIRECTORY_SEPARATOR . $filename;
    $zip = new ZipArchive();
    if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
        throw new RuntimeException('Unable to create the backup archive.');
    }

    try {
        $tables = [];
        $pdo->beginTransaction();
        try {
            foreach (system_backup_tables() as $table => $columns) {
                $tables[$table] = $pdo->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        $databaseSnapshot = ['version' => 1, 'created_at' => date(DATE_ATOM), 'tables' => $tables];
        $zip->addFromString('database.json', json_encode($databaseSnapshot, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $root = dirname(__DIR__);
        $excludedDirectories = ['.git', 'storage', 'vendor', 'node_modules'];
        $manifestFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
            $parts = explode('/', $relativePath);
            if (array_intersect($parts, $excludedDirectories) || in_array('.env', $parts, true) || $item->isLink() || $item->isDir()) {
                continue;
            }
            if (str_ends_with($relativePath, '.log') || str_starts_with($relativePath, 'storage/')) {
                continue;
            }

            $zipPath = 'files/' . $relativePath;
            if (!$zip->addFile($item->getPathname(), $zipPath)) {
                throw new RuntimeException('Unable to add a project file to the backup.');
            }
            $manifestFiles[$relativePath] = hash_file('sha256', $item->getPathname());
        }

        $zip->addFromString('manifest.json', json_encode([
            'version' => 1,
            'files' => $manifestFiles,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        $zip->close();
        @chmod($archivePath, 0600);
        prune_system_backups();
    } catch (Throwable $exception) {
        $zip->close();
        @unlink($archivePath);
        throw $exception;
    }

    return $filename;
}

function list_system_backups(): array
{
    $files = glob(system_backup_directory() . DIRECTORY_SEPARATOR . 'dental-backup-*.zip') ?: [];
    usort($files, static fn($first, $second) => filemtime($second) <=> filemtime($first));

    return array_map('basename', $files);
}

function valid_system_backup_filename(string $filename): bool
{
    return (bool) preg_match('/\Adental-backup-\d{8}-\d{6}-[a-f0-9]{8}\.zip\z/', $filename);
}

function restore_system_backup(PDO $pdo, string $filename): void
{
    if (!class_exists('ZipArchive')) {
        throw new RuntimeException('The PHP Zip extension is required to restore backups.');
    }
    if (!valid_system_backup_filename($filename)) {
        throw new RuntimeException('Invalid backup selection.');
    }

    $backupDirectory = realpath(system_backup_directory());
    $archivePath = realpath($backupDirectory . DIRECTORY_SEPARATOR . $filename);
    if (!$archivePath || dirname($archivePath) !== $backupDirectory) {
        throw new RuntimeException('Backup file not found.');
    }

    $zip = new ZipArchive();
    if ($zip->open($archivePath) !== true) {
        throw new RuntimeException('The selected backup archive cannot be opened.');
    }

    try {
        $snapshot = json_decode($zip->getFromName('database.json') ?: '', true, 512, JSON_THROW_ON_ERROR);
        $manifest = json_decode($zip->getFromName('manifest.json') ?: '', true, 512, JSON_THROW_ON_ERROR);
        if (($snapshot['version'] ?? null) !== 1 || ($manifest['version'] ?? null) !== 1 || !isset($snapshot['tables'], $manifest['files']) || !is_array($manifest['files'])) {
            throw new RuntimeException('The backup archive has an unsupported format.');
        }

        $allowedTables = system_backup_tables();
        foreach ($allowedTables as $table => $columns) {
            if (!isset($snapshot['tables'][$table]) || !is_array($snapshot['tables'][$table])) {
                throw new RuntimeException('The database snapshot is incomplete.');
            }
            foreach ($snapshot['tables'][$table] as $row) {
                if (!is_array($row) || array_diff($columns, array_keys($row))) {
                    throw new RuntimeException('The database snapshot contains an invalid row.');
                }
            }
        }

        $root = realpath(dirname(__DIR__));
        $restoredFiles = [];
        $allowedRoots = ['assets', 'auth', 'config', 'database', 'dentist', 'student'];
        foreach ($manifest['files'] as $relativePath => $expectedHash) {
            if (!is_string($relativePath) || !is_string($expectedHash) || str_contains($relativePath, "\0") || str_contains($relativePath, '\\')) {
                throw new RuntimeException('The file manifest contains an invalid path.');
            }
            $parts = explode('/', $relativePath);
            if (in_array('..', $parts, true) || in_array('.', $parts, true) || !in_array($parts[0], $allowedRoots, true) && count($parts) !== 1) {
                throw new RuntimeException('The file manifest contains a disallowed path.');
            }
            if (count($parts) === 1 && $parts[0] !== 'index.php' && $parts[0] !== 'README.md') {
                throw new RuntimeException('The file manifest contains a disallowed root file.');
            }
            if ($parts[0] === 'config' && ($parts[1] ?? '') === '.env') {
                throw new RuntimeException('Environment files cannot be restored from an archive.');
            }

            $content = $zip->getFromName('files/' . $relativePath);
            if ($content === false || !hash_equals($expectedHash, hash('sha256', $content))) {
                throw new RuntimeException('A project file failed backup integrity validation.');
            }
            $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $targetDirectory = realpath(dirname($target));
            if (!$targetDirectory || strpos($targetDirectory . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR) !== 0 || is_link($target)) {
                throw new RuntimeException('A project file target is not safe to restore.');
            }
            $restoredFiles[$target] = $content;
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        try {
            $pdo->beginTransaction();
            foreach (['login_attempts', 'activity_logs', 'dental_records', 'appointments', 'users'] as $table) {
                $pdo->exec('DELETE FROM `' . $table . '`');
            }
            foreach ($allowedTables as $table => $columns) {
                $columnSql = implode(', ', array_map(static fn($column) => '`' . $column . '`', $columns));
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $insert = $pdo->prepare('INSERT INTO `' . $table . '` (' . $columnSql . ') VALUES (' . $placeholders . ')');
                foreach ($snapshot['tables'][$table] as $row) {
                    $insert->execute(array_map(static fn($column) => $row[$column], $columns));
                }
            }
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        foreach ($restoredFiles as $target => $content) {
            $temporaryPath = $target . '.restore-' . bin2hex(random_bytes(4));
            if (file_put_contents($temporaryPath, $content, LOCK_EX) === false || !rename($temporaryPath, $target)) {
                @unlink($temporaryPath);
                throw new RuntimeException('A project file could not be restored.');
            }
        }
    } finally {
        $zip->close();
    }
}

function prune_system_backups(int $retentionDays = 30): void
{
    $cutoff = time() - $retentionDays * 86400;
    foreach (glob(system_backup_directory() . DIRECTORY_SEPARATOR . 'dental-backup-*.zip') ?: [] as $backupPath) {
        if (is_file($backupPath) && filemtime($backupPath) < $cutoff) {
            @unlink($backupPath);
        }
    }
}