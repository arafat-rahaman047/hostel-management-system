<?php
session_start();
include('../includes/db.php');
require_once('../includes/auth.php');
checkAccess(['Student', 'AdminOfficer', 'Provost', 'AssistantRegistrar']);

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

if ($role === 'Student') {
    // Student শুধু নিজের payments দেখবে
    $st = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
    $st->bind_param("i", $user_id);
    $st->execute();
    $student_id = $st->get_result()->fetch_assoc()['student_id'];

    $q = $conn->prepare("
        SELECT 
            p.receipt_no       AS 'Receipt No',
            p.payment_type     AS 'Payment Type',
            p.month            AS 'Month',
            p.amount           AS 'Amount (BDT)',
            p.payment_method   AS 'Method',
            p.transaction_id   AS 'Transaction ID',
            p.payment_date     AS 'Date',
            p.status           AS 'Status'
        FROM payments p 
        WHERE p.student_id = ? 
        ORDER BY p.payment_date DESC
    ");
    $q->bind_param("i", $student_id);

} else {
    // Admin/Staff সব payments দেখবে
    $q = $conn->prepare("
        SELECT 
            p.receipt_no       AS 'Receipt No',
            u.name             AS 'Student Name',
            s.student_reg_id   AS 'Reg ID',
            p.payment_type     AS 'Payment Type',
            p.month            AS 'Month',
            p.amount           AS 'Amount (BDT)',
            p.payment_method   AS 'Method',
            p.transaction_id   AS 'Transaction ID',
            p.payment_date     AS 'Date',
            p.status           AS 'Status',
            v.name             AS 'Verified By'
        FROM payments p
        JOIN students s ON p.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN users v ON p.verified_by = v.user_id
        ORDER BY p.payment_date DESC
    ");
}

$q->execute();
$rows = $q->get_result()->fetch_all(MYSQLI_ASSOC);

// CSV output
$filename = 'payment_report_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
// BOM for Excel Bengali support
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

if (!empty($rows)) {
    fputcsv($out, array_keys($rows[0]));
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
} else {
    fputcsv($out, ['No payment records found.']);
}

fclose($out);
exit();