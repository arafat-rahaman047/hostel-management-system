<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db.php');
require_once('../includes/auth.php');

// 1. Check Permissions
if ($_SESSION['role'] != "AdminOfficer") {
    die("Unauthorized Access");
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: ../templates/dashboard/admin.php");
    exit();
}

$payment_id = intval($_GET['id']);
$action = $_GET['action'];
$verifier = $_SESSION['user_id'];

// 2. Determine Status
$status = ($action === 'verify') ? 'Verified' : (($action === 'reject') ? 'Rejected' : die("Invalid Action"));

// 3. Prepare and Execute Update
$sql = "UPDATE payments SET status=?, verified_by=?, verified_at=NOW() WHERE payment_id=?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // This will print the exact database error (e.g., 'Unknown column verified_by')
    die("Database Error: " . $conn->error);
}

$stmt->bind_param("sii", $status, $verifier, $payment_id);

if ($stmt->execute()) {
    $log_msg = "$status payment ID: $payment_id";
    $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    if ($log) {
        $log->bind_param("is", $verifier, $log_msg);
        $log->execute();
    }
    // Redirect to the tab matching the action just taken
    header("Location: /hostel-management-system/templates/dashboard/admin.php?pstatus=" . urlencode($status) . "&msg=success");
} else {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?pstatus=Pending&msg=error");
}
exit();