<?php
function get_dentist_analytics(PDO $pdo): array
{
    $summary = $pdo->query('SELECT status, COUNT(*) AS total FROM appointments GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
    $totalAppointments = array_sum(array_map('intval', $summary));
    $totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $totalRecords = (int) $pdo->query('SELECT COUNT(*) FROM dental_records')->fetchColumn();
    $maxStatusCount = max(1, ...array_values(array_map('intval', $summary ?: [0])));
    $reportPeriodStart = (new DateTimeImmutable('first day of this month'))->modify('-5 months')->format('Y-m-d');
    $reportPeriodEnd = date('Y-m-d');
    $monthlyReport = [];
    $reportMonthCursor = new DateTimeImmutable('first day of this month');

    for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
        $reportMonth = $reportMonthCursor->modify('-' . $monthOffset . ' months');
        $reportMonthKey = $reportMonth->format('Y-m');
        $monthlyReport[$reportMonthKey] = [
            'label' => $reportMonth->format('M Y'),
            'short_label' => $reportMonth->format('M'),
            'appointments' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'records' => 0,
        ];
    }

    $monthlyAppointmentsStmt = $pdo->prepare("SELECT DATE_FORMAT(appointment_date, '%Y-%m') AS month_key, status, COUNT(*) AS total FROM appointments WHERE appointment_date BETWEEN ? AND ? GROUP BY DATE_FORMAT(appointment_date, '%Y-%m'), status");
    $monthlyAppointmentsStmt->execute([$reportPeriodStart, $reportPeriodEnd]);
    foreach ($monthlyAppointmentsStmt->fetchAll() as $monthlyAppointment) {
        $monthKey = $monthlyAppointment['month_key'];
        if (isset($monthlyReport[$monthKey])) {
            $appointmentCount = (int) $monthlyAppointment['total'];
            $monthlyReport[$monthKey]['appointments'] += $appointmentCount;
            $statusKey = strtolower($monthlyAppointment['status']);
            if (isset($monthlyReport[$monthKey][$statusKey])) {
                $monthlyReport[$monthKey][$statusKey] = $appointmentCount;
            }
        }
    }

    $monthlyRecordsStmt = $pdo->prepare("SELECT DATE_FORMAT(checkup_date, '%Y-%m') AS month_key, COUNT(*) AS total FROM dental_records WHERE checkup_date BETWEEN ? AND ? GROUP BY DATE_FORMAT(checkup_date, '%Y-%m')");
    $monthlyRecordsStmt->execute([$reportPeriodStart, $reportPeriodEnd]);
    foreach ($monthlyRecordsStmt->fetchAll() as $monthlyRecord) {
        $monthKey = $monthlyRecord['month_key'];
        if (isset($monthlyReport[$monthKey])) {
            $monthlyReport[$monthKey]['records'] = (int) $monthlyRecord['total'];
        }
    }

    $reportTotals = ['appointments' => 0, 'pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0, 'records' => 0];
    $maxMonthlyAppointments = 1;
    $maxMonthlyRecords = 1;
    foreach ($monthlyReport as $monthData) {
        foreach ($reportTotals as $totalKey => $totalValue) {
            $reportTotals[$totalKey] += $monthData[$totalKey];
        }
        $maxMonthlyAppointments = max($maxMonthlyAppointments, $monthData['appointments']);
        $maxMonthlyRecords = max($maxMonthlyRecords, $monthData['records']);
    }

    return compact(
        'summary',
        'totalAppointments',
        'totalStudents',
        'totalRecords',
        'maxStatusCount',
        'reportPeriodStart',
        'reportPeriodEnd',
        'monthlyReport',
        'reportTotals',
        'maxMonthlyAppointments',
        'maxMonthlyRecords'
    );
}