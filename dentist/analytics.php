<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_analytics_data.php';
$analytics = get_dentist_analytics($pdo);
extract($analytics, EXTR_SKIP);
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Statistics & charts', 'analytics', 'A snapshot of clinic activity and six-month trends.');
?>
<section class="analytics-section" aria-labelledby="analytics-title">
    <div class="analytics-heading">
        <div>
            <p class="eyebrow">Clinic insights</p>
            <h2 id="analytics-title">Statistics &amp; charts</h2>
        </div>
        <button type="button" class="button-secondary no-print" onclick="window.print()"><i data-lucide="printer"></i>Print charts</button>
    </div>

    <section class="summary-grid" aria-label="Clinic statistics">
        <div class="summary-item"><strong><?php echo $totalAppointments; ?></strong><span>Total appointments</span></div>
        <div class="summary-item"><strong><?php echo (int) ($summary['Pending'] ?? 0); ?></strong><span>Pending</span></div>
        <div class="summary-item"><strong><?php echo (int) ($summary['Confirmed'] ?? 0); ?></strong><span>Confirmed</span></div>
        <div class="summary-item"><strong><?php echo (int) ($summary['Completed'] ?? 0); ?></strong><span>Completed</span></div>
        <div class="summary-item"><strong><?php echo $totalStudents; ?></strong><span>Students</span></div>
        <div class="summary-item"><strong><?php echo $totalRecords; ?></strong><span>Dental records</span></div>
    </section>

    <div class="analytics-grid">
        <section class="chart-panel" aria-labelledby="appointment-chart-title">
            <h3 id="appointment-chart-title">All-time appointment status</h3>
            <div class="bar-chart">
                <?php foreach (['Pending', 'Confirmed', 'Completed', 'Cancelled'] as $chartStatus): ?>
                    <?php $chartCount = (int) ($summary[$chartStatus] ?? 0); $barWidth = (int) round(($chartCount / $maxStatusCount) * 100); ?>
                    <div class="chart-row"><span><?php echo html_escape($chartStatus); ?></span><div class="chart-track"><div class="chart-bar status-<?php echo strtolower($chartStatus); ?>" style="width: <?php echo $barWidth; ?>%"></div></div><strong><?php echo $chartCount; ?></strong></div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="chart-panel" aria-labelledby="monthly-appointments-title">
            <h3 id="monthly-appointments-title">Appointments by month</h3>
            <div class="month-chart" aria-label="Monthly appointment totals">
                <?php foreach ($monthlyReport as $monthData): ?>
                    <?php $appointmentBarHeight = (int) round(($monthData['appointments'] / $maxMonthlyAppointments) * 100); ?>
                    <div class="month-chart-item" title="<?php echo html_escape($monthData['label'] . ': ' . $monthData['appointments'] . ' appointments'); ?>">
                        <strong><?php echo $monthData['appointments']; ?></strong>
                        <div class="month-chart-track"><div class="month-chart-bar" style="height: <?php echo $appointmentBarHeight; ?>%"></div></div>
                        <span><?php echo html_escape($monthData['short_label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="chart-panel" aria-labelledby="monthly-records-title">
            <h3 id="monthly-records-title">Dental records by month</h3>
            <div class="month-chart records-chart" aria-label="Monthly dental record totals">
                <?php foreach ($monthlyReport as $monthData): ?>
                    <?php $recordBarHeight = (int) round(($monthData['records'] / $maxMonthlyRecords) * 100); ?>
                    <div class="month-chart-item" title="<?php echo html_escape($monthData['label'] . ': ' . $monthData['records'] . ' dental records'); ?>">
                        <strong><?php echo $monthData['records']; ?></strong>
                        <div class="month-chart-track"><div class="month-chart-bar" style="height: <?php echo $recordBarHeight; ?>%"></div></div>
                        <span><?php echo html_escape($monthData['short_label']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <p class="report-period-note">Monthly charts cover <?php echo html_escape($reportPeriodStart); ?> through <?php echo html_escape($reportPeriodEnd); ?>.</p>
</section>
<?php render_dentist_layout_end(); ?>