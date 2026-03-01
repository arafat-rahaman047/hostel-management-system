<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $hostel_id = $_POST['hostel_id'];

    // 1. Get Student ID
    $stmt = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student_id = $stmt->get_result()->fetch_assoc()['student_id'];

    // 2. Find an available room in the chosen hostel
    // This implements "Automated Allocation" logic by finding the first available room
    $room_stmt = $conn->prepare("SELECT room_id FROM Rooms WHERE hostel_id = ? AND status = 'Available' LIMIT 1");
    $room_stmt->bind_param("i", $hostel_id);
    $room_stmt->execute();
    $room_res = $room_stmt->get_result();

    if ($room_res->num_rows > 0) {
        $room_id = $room_res->fetch_assoc()['room_id'];

        // 3. Create a Booking Request
        $book_stmt = $conn->prepare("INSERT INTO Bookings (student_id, room_id, status) VALUES (?, ?, 'Pending')");
        $book_stmt->bind_param("ii", $student_id, $room_id);
        
        if ($book_stmt->execute()) {
            // Log the action
            $log_stmt = $conn->prepare("INSERT INTO Logs (user_id, action) VALUES (?, 'Applied for room booking')");
            $log_stmt->bind_param("i", $user_id);
            $log_stmt->execute();

            header("Location: ../templates/dashboard/student.php?success=applied");
        }
    } else {
        header("Location: ../modules/booking.php?error=no_rooms");
    }
    exit();
}
?>