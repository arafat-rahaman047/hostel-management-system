<?php
session_start();
include('../includes/db.php');
include('../includes/auth.php');

if ($_SESSION['role'] != "AdminOfficer") {
  die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hostel_id = intval($_POST['hostel_id']);
  $floor = intval($_POST['floor']);
  $capacity = intval($_POST['capacity']);
  $status = 'Available';

  $stmt = $conn->prepare("INSERT INTO Rooms (hostel_id, floor, capacity, status) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("iiis", $hostel_id, $floor, $capacity, $status);

  if ($stmt->execute()) {
    header("Location: ../templates/admin.php?success=Room+Added");
  } else {
    header("Location: ../templates/admin.php?error=" . urlencode($conn->error));
  }
  exit();
}