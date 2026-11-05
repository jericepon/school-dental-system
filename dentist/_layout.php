<?php
function render_dentist_layout_start(string $pageTitle, string $activePage, string $description = ''): void
{
    $navigation = [
        ['key' => 'overview', 'href' => 'dashboard.php', 'label' => 'Overview', 'icon' => 'layout-dashboard'],
        ['key' => 'analytics', 'href' => 'analytics.php', 'label' => 'Statistics & charts', 'icon' => 'bar-chart-3'],
        ['key' => 'reports', 'href' => 'reports.php', 'label' => 'Summary report', 'icon' => 'file-text'],
        ['key' => 'appointments', 'href' => 'appointments.php', 'label' => 'Appointments', 'icon' => 'calendar-days'],
        ['key' => 'new-record', 'href' => 'new_record.php', 'label' => 'New record', 'icon' => 'file-plus-2'],
        ['key' => 'records', 'href' => 'records.php', 'label' => 'Dental records', 'icon' => 'files'],
        ['key' => 'activity', 'href' => 'activity.php', 'label' => 'Activity', 'icon' => 'activity'],
        ['key' => 'backup', 'href' => 'backup.php', 'label' => 'Backup & Restore', 'icon' => 'archive'],
        ['key' => 'help', 'href' => 'help.php', 'label' => 'Help', 'icon' => 'circle-help'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo html_escape($pageTitle); ?> | Dentist Portal</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/favicon.ico" sizes="16x16 32x32 48x48">
    <script src="../assets/js/script.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar" aria-label="Dentist dashboard navigation">
            <a class="sidebar-brand" href="../index.php">
                <img class="brand-logo" src="../assets/img/logo.jpeg" alt="">
                <span>School Dental<br>Care</span>
            </a>
            <div class="sidebar-user">
                <span>CLINIC WORKSPACE</span>
                <strong><?php echo html_escape($_SESSION['name']); ?></strong>
            </div>
            <nav class="sidebar-nav" aria-label="Clinic sections">
                <?php foreach ($navigation as $item): ?>
                    <a href="<?php echo html_escape($item['href']); ?>" class="<?php echo $activePage === $item['key'] ? 'is-active' : ''; ?>" <?php echo $activePage === $item['key'] ? 'aria-current="page"' : ''; ?>>
                        <i data-lucide="<?php echo html_escape($item['icon']); ?>"></i><span><?php echo html_escape($item['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <button class="theme-toggle" type="button" data-theme-toggle>
                    <span data-theme-icon></span><span data-theme-label>Dark mode</span>
                </button>
                <form method="POST" action="../auth/logout.php" class="no-print">
                    <input type="hidden" name="csrf_token" value="<?php echo html_escape(csrf_token()); ?>">
                    <button type="submit" class="button-secondary"><i data-lucide="log-out"></i>Sign out</button>
                </form>
            </div>
        </aside>
        <main class="main-content dentist-dashboard">
            <header class="page-header">
                <div>
                    <p class="eyebrow">Clinic workspace</p>
                    <h1><?php echo html_escape($pageTitle); ?></h1>
                    <?php if ($description !== ''): ?><p><?php echo html_escape($description); ?></p><?php endif; ?>
                </div>
            </header>
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <p class="success"><?php echo html_escape($_SESSION['flash_success']); ?></p>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
    <?php
}

function render_dentist_layout_end(): void
{
    ?>
        </main>
    </div>
    <script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js" defer></script>
</body>
</html>
    <?php
}