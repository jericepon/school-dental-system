<?php
require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_analytics_data.php';
$analytics = get_dentist_analytics($pdo);
extract($analytics, EXTR_SKIP);
require_once __DIR__ . '/_layout.php';
render_dentist_layout_start('Summary report', 'reports', 'Appointment outcomes and dental records by month.');
?>
<section class="summary-report summary-report-page" aria-labelledby="summary-report-title">
    <div class="analytics-heading">
        <div>
            <p class="eyebrow">Clinic report</p>
            <h2 id="summary-report-title">Six-month summary</h2>
            <p><?php echo html_escape($reportPeriodStart); ?> to <?php echo html_escape($reportPeriodEnd); ?></p>
        </div>
        <button type="button" class="button-secondary no-print" onclick="window.print()"><i data-lucide="printer"></i>Print report</button>
    </div>

    <section class="summary-grid" aria-label="Six-month totals">
        <div class="summary-item"><strong><?php echo $reportTotals['appointments']; ?></strong><span>Appointments</span></div>
        <div class="summary-item"><strong><?php echo $reportTotals['completed']; ?></strong><span>Completed</span></div>
        <div class="summary-item"><strong><?php echo $reportTotals['cancelled']; ?></strong><span>Cancelled</span></div>
        <div class="summary-item"><strong><?php echo $reportTotals['records']; ?></strong><span>Dental records</span></div>
    </section>

    <div class="table-scroll">
        <table class="report-table">
            <thead><tr><th>Month</th><th>Appointments</th><th>Pending</th><th>Confirmed</th><th>Completed</th><th>Cancelled</th><th>Dental records</th></tr></thead>
            <tbody>
                <?php foreach ($monthlyReport as $monthData): ?>
                    <tr>
                        <td><?php echo html_escape($monthData['label']); ?></td>
                        <td><?php echo $monthData['appointments']; ?></td>
                        <td><?php echo $monthData['pending']; ?></td>
                        <td><?php echo $monthData['confirmed']; ?></td>
                        <td><?php echo $monthData['completed']; ?></td>
                        <td><?php echo $monthData['cancelled']; ?></td>
                        <td><?php echo $monthData['records']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot><tr class="summary-total"><th>Period total</th><th><?php echo $reportTotals['appointments']; ?></th><th><?php echo $reportTotals['pending']; ?></th><th><?php echo $reportTotals['confirmed']; ?></th><th><?php echo $reportTotals['completed']; ?></th><th><?php echo $reportTotals['cancelled']; ?></th><th><?php echo $reportTotals['records']; ?></th></tr></tfoot>
        </table>
    </div>
</section>
<?php render_dentist_layout_end(); ?>