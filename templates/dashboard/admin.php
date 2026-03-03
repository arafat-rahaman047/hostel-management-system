<?php
include "../../includes/db.php";
include "../../includes/auth.php";
include "../../includes/header.php";

// Access Control
if ($_SESSION['role'] != "AdminOfficer") {
    die("<div class='container mt-5'><div class='alert alert-danger'>Access Denied: Admin Officers Only.</div></div>");
}

// --- Statistics Logic ---
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM Users WHERE role='Student'")->fetch_assoc()['total'];
$totalRooms = $conn->query("SELECT COUNT(*) AS total FROM Rooms")->fetch_assoc()['total'];
$pendingBookings = $conn->query("SELECT COUNT(*) AS total FROM Bookings WHERE status='Pending'")->fetch_assoc()['total'];
$totalComplaints = $conn->query("SELECT COUNT(*) AS total FROM Complaints WHERE status!='Resolved'")->fetch_assoc()['total'];
$totalRevenue = $conn->query("SELECT SUM(amount) AS total FROM Payments WHERE status='Verified'")->fetch_assoc()['total'] ?? 0;

// --- Payment Filter Logic ---
$pstatus = $_GET['pstatus'] ?? 'Pending';
$where_clause = ($pstatus !== 'all') ? "WHERE p.status = '$pstatus'" : "";
$payments = $conn->query("
    SELECT p.*, u.name AS student_name, s.student_reg_id
    FROM Payments p
    JOIN Students s ON p.student_id = s.student_id
    JOIN Users u ON s.user_id = u.user_id
    $where_clause
    ORDER BY p.payment_date DESC
");
?>

<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4"><i class="fas fa-user-shield me-2 text-primary"></i>Admin Control Panel</h2>

    <div class="row g-3 mb-5">
        <div class="col-md-2.4 col-lg">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <small class="text-uppercase opacity-75">Students</small>
                <h2 class="fw-bold mb-0"><?php echo $totalStudents; ?></h2>
            </div>
        </div>
        <div class="col-md-2.4 col-lg">
            <div class="card border-0 shadow-sm bg-dark text-white p-3">
                <small class="text-uppercase opacity-75">Total Rooms</small>
                <h2 class="fw-bold mb-0"><?php echo $totalRooms; ?></h2>
            </div>
        </div>
        <div class="col-md-2.4 col-lg">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                <small class="text-uppercase opacity-75">Pending Bookings</small>
                <h2 class="fw-bold mb-0"><?php echo $pendingBookings; ?></h2>
            </div>
        </div>
        <div class="col-md-2.4 col-lg">
            <div class="card border-0 shadow-sm bg-danger text-white p-3">
                <small class="text-uppercase opacity-75">Open Complaints</small>
                <h2 class="fw-bold mb-0"><?php echo $totalComplaints; ?></h2>
            </div>
        </div>
        <div class="col-md-2.4 col-lg">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <small class="text-uppercase opacity-75">Verified Revenue</small>
                <h2 class="fw-bold mb-0">৳<?php echo number_format($totalRevenue); ?></h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Add New Hostel</div>
                <div class="card-body">
                    <form method="POST" action="../../api/add_hostel.php">
                        <input type="text" name="name" class="form-control mb-2" placeholder="Hostel Name" required>
                        <input type="text" name="location" class="form-control mb-2" placeholder="Location">
                        <input type="number" name="total_rooms" class="form-control mb-2" placeholder="Total Rooms">
                        <button type="submit" class="btn btn-primary w-100">Add Hostel</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Add New Room</div>
                <div class="card-body">
                    <form method="POST" action="../../api/add_room.php">
                        <select name="hostel_id" class="form-select mb-2">
                            <?php
                            $hlist = $conn->query("SELECT * FROM Hostels");
                            while ($h = $hlist->fetch_assoc())
                                echo "<option value='{$h['hostel_id']}'>{$h['name']}</option>";
                            ?>
                        </select>
                        <input type="number" name="floor" class="form-control mb-2" placeholder="Floor">
                        <input type="number" name="capacity" class="form-control mb-2" placeholder="Capacity">
                        <button type="submit" class="btn btn-dark w-100">Add Room</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold text-primary">Payment Management</h5>
                    <a href="../../api/payment_report.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-download me-1"></i> Export CSV
                    </a>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item"><a class="nav-link <?php echo ($pstatus === 'Pending') ? 'active' : ''; ?>"
                                href="?pstatus=Pending">⏳ Pending</a></li>
                        <li class="nav-item"><a
                                class="nav-link <?php echo ($pstatus === 'Verified') ? 'active' : ''; ?>"
                                href="?pstatus=Verified">✅ Verified</a></li>
                        <li class="nav-item"><a
                                class="nav-link <?php echo ($pstatus === 'Rejected') ? 'active' : ''; ?>"
                                href="?pstatus=Rejected">❌ Rejected</a></li>
                        <li class="nav-item"><a class="nav-link <?php echo ($pstatus === 'all') ? 'active' : ''; ?>"
                                href="?pstatus=all">📋 All</a></li>
                    </ul>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($p = $payments->fetch_assoc()):
                                    $badge = match ($p['status']) { 'Verified' => 'success', 'Pending' => 'warning', 'Rejected' => 'danger', default => 'secondary'};
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($p['student_name']); ?></strong><br>
                                            <small
                                                class="text-muted"><?php echo htmlspecialchars($p['transaction_id']); ?></small>
                                        </td>
                                        <td><?php echo $p['payment_type']; ?></td>
                                        <td class="fw-bold text-success">৳<?php echo number_format($p['amount']); ?></td>
                                        <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $p['status']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($p['status'] === 'Pending'): ?>
                                                <a href="../../api/verify_payment.php?id=<?php echo $p['payment_id']; ?>&action=verify"
                                                    class="btn btn-success btn-sm">Verify</a>
                                            <?php else: ?>
                                                <span class="text-muted small">Processed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>