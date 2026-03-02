<?php
session_start();
include('../includes/db.php');
require_once('../includes/auth.php');
checkAccess(['AdminOfficer', 'Provost', 'AssistantRegistrar']);

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: ../templates/dashboard/admin.php");
    exit();
}

$payment_id = intval($_GET['id']);
$action     = $_GET['action'];
$verifier   = $_SESSION['user_id'];

if ($action === 'verify') {
    $stmt = $conn->prepare("UPDATE Payments SET status='Verified', verified_by=?, verified_at=NOW() WHERE payment_id=?");
    $stmt->bind_param("ii", $verifier, $payment_id);
    $stmt->execute();

    // Log
    $log = $conn->prepare("INSERT INTO Logs (user_id, action) VALUES (?, ?)");
    $a = "Verified payment ID: $payment_id";
    $log->bind_param("is", $verifier, $a);
    $log->execute();

} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE Payments SET status='Rejected', verified_by=?, verified_at=NOW() WHERE payment_id=?");
    $stmt->bind_param("ii", $verifier, $payment_id);
    $stmt->execute();
}

// Redirect back to referring page
$ref = $_SERVER['HTTP_REFERER'] ?? '../templates/dashboard/admin.php';
header("Location: $ref?msg=payment_updated");
exit();