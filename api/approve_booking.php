<?php
session_start();
include('../includes/db.php');
require_once('../includes/auth.php');
checkAccess(['Provost']);

if (isset($_GET['id']) && isset($_GET['action'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];

    if ($action == 'approve') {
        // 1. Get Booking details
        $stmt = $conn->prepare("SELECT student_id, room_id FROM Bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();

        // 2. Update Booking Status
        $conn->query("UPDATE Bookings SET status = 'Approved' WHERE booking_id = $booking_id");

        // 3. Update Student Record with Room and Hostel info
        $room_id = $booking['room_id'];
        $hostel_query = $conn->query("SELECT hostel_id FROM Rooms WHERE room_id = $room_id")->fetch_assoc();
        $hostel_id = $hostel_query['hostel_id'];

        $update_student = $conn->prepare("UPDATE Students SET room_id = ?, hostel_id = ? WHERE student_id = ?");
        $update_student->bind_param("iii", $room_id, $hostel_id, $booking['student_id']);
        $update_student->execute();

        // 4. Mark Room as Occupied
        $conn->query("UPDATE Rooms SET status = 'Occupied' WHERE room_id = $room_id");

    } else {
        $conn->query("UPDATE Bookings SET status = 'Rejected' WHERE booking_id = $booking_id");
    }

    header("Location: ../templates/dashboard/provost.php?msg=updated");
    exit();
}
?>