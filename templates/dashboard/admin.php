<?php
include "../../includes/auth.php";
include "../../includes/db.php";
include "../../includes/header.php";

if($_SESSION['role'] != "AdminOfficer"){
    die("Access Denied");
}

// Statistics
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM Users WHERE role='Student'")->fetch_assoc()['total'];
$totalRooms = $conn->query("SELECT COUNT(*) AS total FROM Rooms")->fetch_assoc()['total'];
$pendingBookings = $conn->query("SELECT COUNT(*) AS total FROM Bookings WHERE status='Pending'")->fetch_assoc()['total'];
$totalComplaints = $conn->query("SELECT COUNT(*) AS total FROM Complaints WHERE status!='Resolved'")->fetch_assoc()['total'];
$totalRevenue = $conn->query("SELECT SUM(amount) AS total FROM Payments WHERE status='Completed'")->fetch_assoc()['total'] ?? 0;
?>

<h2>Admin Control Panel</h2>

<div class="dashboard-container">

    <div class="card">
        <h3>Total Students</h3>
        <p><?php echo $totalStudents; ?></p>
    </div>

    <div class="card">
        <h3>Total Rooms</h3>
        <p><?php echo $totalRooms; ?></p>
    </div>

    <div class="card">
        <h3>Pending Bookings</h3>
        <p><?php echo $pendingBookings; ?></p>
        <a href="#bookingSection" class="btn">Manage</a>
    </div>

    <div class="card">
        <h3>Open Complaints</h3>
        <p><?php echo $totalComplaints; ?></p>
    </div>

    <div class="card">
        <h3>Total Revenue</h3>
        <p>৳<?php echo $totalRevenue; ?></p>
    </div>

</div>

<hr>

<!-- Manage Hostels -->
<h3>Manage Hostels</h3>

<form method="POST" action="../../api/add_hostel.php">
    <input type="text" name="name" placeholder="Hostel Name" required>
    <input type="text" name="location" placeholder="Location">
    <input type="number" name="total_rooms" placeholder="Total Rooms">
    <button type="submit">Add Hostel</button>
</form>

<?php
$hostels = $conn->query("SELECT * FROM Hostels");
while($h = $hostels->fetch_assoc()):
?>
<p>
<?php echo $h['name']; ?> (<?php echo $h['location']; ?>)
</p>
<?php endwhile; ?>

<hr>

<!-- Manage Rooms -->
<h3>Manage Rooms</h3>

<form method="POST" action="../../api/add_room.php">
    <select name="hostel_id">
        <?php
        $hlist = $conn->query("SELECT * FROM Hostels");
        while($h = $hlist->fetch_assoc()):
        ?>
        <option value="<?php echo $h['hostel_id']; ?>">
            <?php echo $h['name']; ?>
        </option>
        <?php endwhile; ?>
    </select>
    <input type="number" name="floor" placeholder="Floor">
    <input type="number" name="capacity" placeholder="Capacity">
    <button type="submit">Add Room</button>
</form>

<hr>

<!-- Pending Bookings -->
<h3 id="bookingSection">Pending Bookings</h3>

<?php
$bookings = $conn->query("
SELECT B.booking_id, U.name, B.room_id
FROM Bookings B
JOIN Students S ON B.student_id=S.student_id
JOIN Users U ON S.user_id=U.user_id
WHERE B.status='Pending'
");

while($b = $bookings->fetch_assoc()):
?>
<p>
Student: <?php echo $b['name']; ?> |
Room: <?php echo $b['room_id']; ?>

<a href="../../api/approve_booking.php?id=<?php echo $b['booking_id']; ?>">Approve</a> |
<a href="../../api/reject_booking.php?id=<?php echo $b['booking_id']; ?>">Reject</a>
</p>
<?php endwhile; ?>

<hr>

<!-- Payment Monitoring -->
<h3>Recent Payments</h3>

<?php
$payments = $conn->query("
SELECT P.amount, P.status, U.name
FROM Payments P
JOIN Students S ON P.student_id=S.student_id
JOIN Users U ON S.user_id=U.user_id
ORDER BY P.payment_date DESC
LIMIT 5
");

while($p = $payments->fetch_assoc()):
?>
<p>
<?php echo $p['name']; ?> |
৳<?php echo $p['amount']; ?> |
<?php echo $p['status']; ?>
</p>
<?php endwhile; ?>

<hr>

<!-- Complaint Monitoring -->
<h3>Open Complaints</h3>

<?php
$complaints = $conn->query("
SELECT C.description, U.name
FROM Complaints C
JOIN Students S ON C.student_id=S.student_id
JOIN Users U ON S.user_id=U.user_id
WHERE C.status!='Resolved'
");

while($c = $complaints->fetch_assoc()):
?>
<p>
Student: <?php echo $c['name']; ?> |
<?php echo $c['description']; ?>
</p>
<?php endwhile; ?>

<?php include "../../includes/footer.php"; ?>
<h3>Manage Complaints</h3>

<?php
$complaints = $conn->query("
SELECT C.complaint_id, C.description, C.status, U.name
FROM Complaints C
JOIN Students S ON C.student_id=S.student_id
JOIN Users U ON S.user_id=U.user_id
");

while($c = $complaints->fetch_assoc()):
?>
<p>
Student: <?php echo $c['name']; ?> |
<?php echo $c['description']; ?> |
Status: <?php echo $c['status']; ?>

<a href="../../api/update_complaint.php?id=<?php echo $c['complaint_id']; ?>&status=InProgress">In Progress</a> |
<a href="../../api/update_complaint.php?id=<?php echo $c['complaint_id']; ?>&status=Resolved">Resolve</a>
</p>
<?php endwhile; ?>