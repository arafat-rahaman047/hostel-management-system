<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db.php');
require_once('../includes/auth.php');

if ($_SESSION['role'] != "AdminOfficer") {
    die("Unauthorized Access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /hostel-management-system/templates/dashboard/admin.php");
    exit();
}

$hostel_id = intval($_POST['hostel_id']);
$floor     = intval($_POST['floor']);
$capacity  = intval($_POST['capacity']);

if ($hostel_id <= 0 || $floor <= 0 || $capacity <= 0) {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error");
    exit();
}

$stmt = $conn->prepare("INSERT INTO rooms (hostel_id, floor, capacity, status) VALUES (?, ?, ?, 'Available')");

if (!$stmt) {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error");
    exit();
}

$stmt->bind_param("iii", $hostel_id, $floor, $capacity);

if ($stmt->execute()) {
    $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    if ($log) {
        $action = "Added new room — Hostel ID: $hostel_id, Floor: $floor, Capacity: $capacity";
        $log->bind_param("is", $_SESSION['user_id'], $action);
        $log->execute();
    }
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=room_added");
} else {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error");
}
exit();