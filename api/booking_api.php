<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../templates/login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

    $reg_id = $_POST['student_reg_id'];
    $dept = $_POST['department'];
    $blood = $_POST['blood_group'];
    $contact = $_POST['contact_no'];
    $f_name = $_POST['father_name'];
    $f_contact = $_POST['father_contact'];
    $m_name = $_POST['mother_name'];
    $m_contact = $_POST['mother_contact'];
    $address = $_POST['permanent_address'];
    $hostel_id = $_POST['hostel_id'];
    $reason = $_POST['reason_for_stay'];

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_data = $result->fetch_assoc();
    if (!$student_data) {
        $ins = $conn->prepare("INSERT INTO students (user_id) VALUES (?)");
        $ins->bind_param("i", $user_id);
        $ins->execute();

        $student_id = $conn->insert_id;
    } else {
        $student_id = $student_data['student_id'];
    }

    $update_sql = "UPDATE students SET 
                    student_reg_id = ?, department = ?, blood_group = ?, 
                    contact_no = ?, father_name = ?, father_contact = ?, 
                    mother_name = ?, mother_contact = ?, permanent_address = ?, 
                    reason_for_stay = ? 
                   WHERE student_id = ?";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param(
        "ssssssssssi",
        $reg_id,
        $dept,
        $blood,
        $contact,
        $f_name,
        $f_contact,
        $m_name,
        $m_contact,
        $address,
        $reason,
        $student_id
    );
    $update_stmt->execute();

    $room_stmt = $conn->prepare("SELECT room_id FROM rooms WHERE hostel_id = ? AND status = 'Available' LIMIT 1");
    $room_stmt->bind_param("i", $hostel_id);
    $room_stmt->execute();
    $room_res = $room_stmt->get_result();

    if ($room_res->num_rows > 0) {
        $room_id = $room_res->fetch_assoc()['room_id'];

        $book_stmt = $conn->prepare("INSERT INTO bookings (student_id, room_id, status) VALUES (?, ?, 'Pending')");
        $book_stmt->bind_param("ii", $student_id, $room_id);

        if ($book_stmt->execute()) {
            $log_stmt = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, 'Submitted Hall Admission Form')");
            $log_stmt->bind_param("i", $user_id);
            $log_stmt->execute();

            header("Location: ../templates/dashboard/student.php?success=applied");
        } else {
            echo "Error creating booking: " . $conn->error;
        }
    } else {
        header("Location: ../modules/booking.php?error=no_rooms");
    }
    exit();
}
?>