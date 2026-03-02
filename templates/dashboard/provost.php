<?php
require_once "../../includes/auth.php";
checkAccess(['Provost']);
include "../../includes/header.php";
include "../../includes/db.php";

$total_students = $conn->query("SELECT COUNT(*) as count FROM Students WHERE room_id IS NOT NULL")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM Bookings WHERE status = 'Pending'")->fetch_assoc()['count'];
$open_complaints = $conn->query("SELECT COUNT(*) as count FROM Complaints WHERE status = 'Open'")->fetch_assoc()['count'];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-primary">Provost Administration Portal</h3>
            <p class="text-muted">University of Barishal Hall Management</p>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="small text-uppercase">Total Residents</h6>
                        <h2 class="fw-bold mb-0"><?php echo $total_students; ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning text-dark p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="small text-uppercase">Pending Requests</h6>
                        <h2 class="fw-bold mb-0"><?php echo $pending_bookings; ?></h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="small text-uppercase">Open Complaints</h6>
                        <h2 class="fw-bold mb-0"><?php echo $open_complaints; ?></h2>
                    </div>
                    <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Pending Booking Applications</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Student Name</th>
                            <th>Requested Hall</th>
                            <th>Room assigned</th>
                            <th>Date</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $requests = $conn->query("SELECT 
        b.booking_id, 
        u.name, 
        h.name as hostel_name, 
        b.room_id, 
        b.status 
    FROM Bookings b 
    JOIN Students s ON b.student_id = s.student_id 
    JOIN Users u ON s.user_id = u.user_id 
    LEFT JOIN Rooms r ON b.room_id = r.room_id 
    LEFT JOIN Hostels h ON r.hostel_id = h.hostel_id 
    WHERE b.status = 'Pending'");

                        if ($requests && $requests->num_rows > 0):
                            while ($row = $requests->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><span class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['hostel_name'] ?? 'Not Specified'); ?></td>
                                    <td>Room #<?php echo htmlspecialchars($row['room_id'] ?? 'TBD'); ?></td>
                                    <td>Just now</td>
                                    <td class="text-center">
                                        <a href="../../api/approve_booking.php?id=<?php echo $row['booking_id']; ?>&action=approve"
                                            class="btn btn-success btn-sm px-3">Approve</a>
                                        <a href="../../api/approve_booking.php?id=<?php echo $row['booking_id']; ?>&action=reject"
                                            class="btn btn-outline-danger btn-sm px-3">Reject</a>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No pending applications found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>