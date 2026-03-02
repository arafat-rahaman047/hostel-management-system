<?php
/**
 * update_complaint.php
 * Place this file in:  /hostel-management-system/api/update_complaint.php
 *
 * Handles tutor actions: inprogress | resolve | reopen
 * Called via GET:  update_complaint.php?action=inprogress&id=5
 */

require_once "../includes/auth.php";
checkAccess(['HouseTutor']);          // Only House Tutors may call this
include "../includes/db.php";

$tutor_id = $_SESSION['user_id'];
$action   = $_GET['action'] ?? '';
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Map action → new status
$statusMap = [
    'inprogress' => 'InProgress',
    'resolve'    => 'Resolved',
    'reopen'     => 'Open',
];

if (!$id || !array_key_exists($action, $statusMap)) {
    header("Location: ../templates/dashboard/tutor.php?msg=error");
    exit();
}

$newStatus = $statusMap[$action];

// Update status and assign tutor_id (so student can see who handled it)
$stmt = $conn->prepare(
    "UPDATE Complaints SET status = ?, tutor_id = ? WHERE complaint_id = ?"
);
$stmt->bind_param("sii", $newStatus, $tutor_id, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    // Log the action
    $logMsg  = "Updated complaint #$id to $newStatus";
    $logStmt = $conn->prepare("INSERT INTO Logs (user_id, action) VALUES (?, ?)");
    $logStmt->bind_param("is", $tutor_id, $logMsg);
    $logStmt->execute();

    header("Location: ../templates/dashboard/tutor.php?msg=updated");
} else {
    header("Location: ../templates/dashboard/tutor.php?msg=error");
}
exit();
?>
