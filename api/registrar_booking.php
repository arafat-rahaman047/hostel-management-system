<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('../includes/db.php');
require_once('../includes/auth.php');
checkAccess(['AssistantRegistrar']);

if (!isset($_GET['id'], $_GET['action'])) {
    header("Location: /hostel-management-system/templates/dashboard/registrar.php");
    exit();
}

$booking_id = intval($_GET['id']);
$action     = $_GET['action'];
$user_id    = $_SESSION['user_id'];

if ($action === 'approve') {
    // Get booking details
    $b = $conn->prepare("SELECT student_id, room_id FROM bookings WHERE booking_id = ?");
    $b->bind_param("i", $booking_id);
    $b->execute();
    $booking = $b->get_result()->fetch_assoc();

    $conn->begin_transaction();
    try {
        // Approve booking
        $conn->prepare("UPDATE bookings SET status='Approved' WHERE booking_id=?")->bind_param("i",$booking_id) && $conn->prepare("UPDATE bookings SET status='Approved' WHERE booking_id=?")->execute();

        $upd = $conn->prepare("UPDATE bookings SET status='Approved' WHERE booking_id=?");
        $upd->bind_param("i", $booking_id);
        $upd->execute();

        // Get hostel from room
        $r = $conn->prepare("SELECT hostel_id FROM rooms WHERE room_id=?");
        $r->bind_param("i", $booking['room_id']);
        $r->execute();
        $hostel_id = $r->get_result()->fetch_assoc()['hostel_id'];

        // Update student record
        $s = $conn->prepare("UPDATE students SET room_id=?, hostel_id=? WHERE student_id=?");
        $s->bind_param("iii", $booking['room_id'], $hostel_id, $booking['student_id']);
        $s->execute();

        // Mark room occupied
        $ro = $conn->prepare("UPDATE rooms SET status='Occupied' WHERE room_id=?");
        $ro->bind_param("i", $booking['room_id']);
        $ro->execute();

        // Log
        $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?,?)");
        $act = "Registrar approved booking #$booking_id";
        $log->bind_param("is", $user_id, $act);
        $log->execute();

        $conn->commit();
        header("Location: /hostel-management-system/templates/dashboard/registrar.php?msg=booking_approved");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: /hostel-management-system/templates/dashboard/registrar.php?msg=error");
    }

} elseif ($action === 'reject') {
    $upd = $conn->prepare("UPDATE bookings SET status='Rejected' WHERE booking_id=?");
    $upd->bind_param("i", $booking_id);
    $upd->execute();

    $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?,?)");
    $act = "Registrar rejected booking #$booking_id";
    $log->bind_param("is", $user_id, $act);
    $log->execute();

    header("Location: /hostel-management-system/templates/dashboard/registrar.php?msg=booking_rejected");
}
exit();