<?php
require_once "../includes/auth.php";
checkAccess(['Provost', 'AdminOfficer', 'HouseTutor']);
include "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../modules/notifications.php");
    exit();
}

$posted_by  = $_SESSION['user_id'];
$title      = trim($conn->real_escape_string($_POST['title']));
$body       = trim($conn->real_escape_string($_POST['body']));
$priority   = in_array($_POST['priority'], ['Low', 'Medium', 'High']) ? $_POST['priority'] : 'Medium';
$hostel_id  = !empty($_POST['hostel_id']) ? (int)$_POST['hostel_id'] : null;

if (empty($title) || empty($body)) {
    header("Location: ../modules/notifications.php?error=missing_fields");
    exit();
}

$hostel_val = $hostel_id ? $hostel_id : 'NULL';

$stmt = $conn->prepare("INSERT INTO notices (posted_by, hostel_id, title, body, priority) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $posted_by, $hostel_id, $title, $body, $priority);

if ($stmt->execute()) {
    // Log the action
    $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $log_action = "Posted a notice: $title";
    $log_stmt->bind_param("is", $posted_by, $log_action);
    $log_stmt->execute();

    header("Location: ../modules/notifications.php?success=1");
} else {
    header("Location: ../modules/notifications.php?error=db_error");
}
exit();
