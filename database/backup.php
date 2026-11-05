<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/backup.php';

try {
    $filename = create_system_backup($pdo);
    fwrite(STDOUT, 'Backup created: ' . $filename . PHP_EOL);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Backup failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}