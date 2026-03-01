<?php
include "../includes/db.php";

$id = $_GET['id'];

$conn->query("UPDATE Bookings SET status='Rejected' WHERE booking_id='$id'");

header("Location: ../templates/dashboard/admin.php");
?>