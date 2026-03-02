<?php
session_start();
include('../includes/db.php');
require_once('../includes/auth.php');
checkAccess(['Student']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../modules/payment.php");
    exit();
}

$user_id   = $_SESSION['user_id'];
$type      = $_POST['payment_type'];
$month     = $_POST['month'];
$amount    = floatval($_POST['amount']);
$method    = $_POST['payment_method'];
$txn_id    = trim($_POST['transaction_id']);
$note      = trim($_POST['note'] ?? '');

// Get student_id
$st = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
$st->bind_param("i", $user_id);
$st->execute();
$student = $st->get_result()->fetch_assoc();

if (!$student) {
    header("Location: ../modules/payment.php?error=Student+profile+not+found.");
    exit();
}
$student_id = $student['student_id'];

// Check duplicate transaction ID
$dup = $conn->prepare("SELECT payment_id FROM Payments WHERE transaction_id = ?");
$dup->bind_param("s", $txn_id);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    header("Location: ../modules/payment.php?error=Duplicate+transaction+ID+detected.");
    exit();
}

// Generate unique receipt number
$receipt_no = 'BUHMS-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

$ins = $conn->prepare("INSERT INTO payments 
    (student_id, payment_type, month, amount, payment_date, payment_method, transaction_id, receipt_no, note, status) 
    VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, 'Pending')");

$ins->bind_param("issdssss", $student_id, $type, $month, $amount, $method, $txn_id, $receipt_no, $note);
$ins->bind_param("issdssss", $student_id, $type, $month, $amount, $method, $txn_id, $receipt_no, $note);

if ($ins->execute()) {
    // Log action
    $log = $conn->prepare("INSERT INTO Logs (user_id, action) VALUES (?, ?)");
    $action = "Submitted payment: $type ৳$amount via $method (TXN: $txn_id)";
    $log->bind_param("is", $user_id, $action);
    $log->execute();

    header("Location: ../modules/payment.php?success=1");
} else {
    header("Location: ../modules/payment.php?error=Payment+submission+failed.+Please+try+again.");
}
exit();