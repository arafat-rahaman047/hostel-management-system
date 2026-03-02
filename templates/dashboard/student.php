<?php
require_once "../../includes/auth.php";
checkAccess(['Student']);
include "../../includes/header.php";
include "../../includes/db.php";

$user_id = $_SESSION['user_id'];
$query = "SELECT s.*, r.room_id, r.floor, h.name as hostel_name
FROM Students s
LEFT JOIN Rooms r ON s.room_id = r.room_id
LEFT JOIN Hostels h ON s.hostel_id = h.hostel_id
WHERE s.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$student_data = $result->fetch_assoc();

if (!$student_data) {
$student_data = [
'room_id' => null,
'hostel_name' => 'Profile Not Found',
'student_id' => null
];
}
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold">Student Dashboard</h3>
            <p class="text-muted">Welcome back, <?php echo $_SESSION['name']; ?>!</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Room Status</h6>
                    <h4 class="mb-0">
                        <?php echo $student_data['room_id'] ? "Room " . $student_data['room_id'] : "Not Allocated"; ?>
                    </h4>
                    <small
                        class="text-primary"><?php echo $student_data['hostel_name'] ?? "No Hostel Assigned"; ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Payment Status</h6>
                    <h4 class="mb-0 text-success">Clear</h4>
                    <small class="text-muted">Next due: N/A</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Active Complaints</h6>
                    <h4 class="mb-0">0</h4>
                    <small class="text-muted">All issues resolved</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width: 70px;">
                    <i class="fas fa-bed fa-2x text-primary"></i>
                </div>
                <h5>Room Booking</h5>
                <p class="small text-muted">Apply for a room or check allocation status.</p>
                <a href="../../modules/booking.php" class="btn btn-outline-primary btn-sm mt-auto stretched-link">Manage
                    Booking</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width: 70px;">
                    <i class="fas fa-file-invoice-dollar fa-2x text-success"></i>
                </div>
                <h5>Payments</h5>
                <p class="small text-muted">Pay hostel fees and view transaction history.</p>
                <a href="../../modules/payment.php" class="btn btn-outline-success btn-sm mt-auto stretched-link">View
                    Receipts</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-3" style="width: 70px;">
                    <i class="fas fa-tools fa-2x text-warning"></i>
                </div>
                <h5>Complaints</h5>
                <p class="small text-muted">Report issues with furniture or electricity.</p>
                <a href="../../modules/complaints.php"
                    class="btn btn-outline-warning btn-sm mt-auto stretched-link">File Complaint</a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width: 70px;">
                    <i class="fas fa-bullhorn fa-2x text-info"></i>
                </div>
                <h5>Notices</h5>
                <p class="small text-muted">Latest updates from the Provost office.</p>
                <a href="../../modules/notifications.php"
                    class="btn btn-outline-info btn-sm mt-auto stretched-link">View All</a>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<?php include "../../includes/footer.php"; ?>