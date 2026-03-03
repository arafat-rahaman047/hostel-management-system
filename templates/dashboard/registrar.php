<?php
require_once "../../includes/auth.php";
checkAccess(['AssistantRegistrar']);
include "../../includes/header.php";
include "../../includes/db.php";

$total_students   = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$allocated        = $conn->query("SELECT COUNT(*) AS c FROM students WHERE room_id IS NOT NULL")->fetch_assoc()['c'];
$pending_bookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='Pending'")->fetch_assoc()['c'];
$total_revenue    = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM payments WHERE status='Verified'")->fetch_assoc()['s'];

$msg = $_GET['msg'] ?? '';
?>

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-start">
            <div>
                <h3 class="fw-bold text-primary mb-1">
                    <i class="fas fa-user-shield me-2"></i>Assistant Registrar Portal
                </h3>
                <p class="text-muted mb-0">University of Barishal — Hall Management System</p>
            </div>
            <span class="badge bg-primary px-3 py-2 fs-6">
                <i class="fas fa-id-badge me-1"></i>
                <?php echo htmlspecialchars($_SESSION['name']); ?>
            </span>
        </div>
    </div>

    <?php if ($msg === 'booking_approved'): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fa-lg"></i>
            <div><strong>Booking approved!</strong></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($msg === 'booking_rejected'): ?>
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="fas fa-times-circle fa-lg"></i>
            <div><strong>Booking rejected.</strong></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-5">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="small text-uppercase mb-1 opacity-75">Total Students</p>
                    <h3 class="fw-bold mb-0"><?php echo $total_students; ?></h3></div>
                    <i class="fas fa-user-graduate fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="small text-uppercase mb-1 opacity-75">Allocated</p>
                    <h3 class="fw-bold mb-0"><?php echo $allocated; ?></h3></div>
                    <i class="fas fa-bed fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="small text-uppercase mb-1 opacity-75">Pending Bookings</p>
                    <h3 class="fw-bold mb-0"><?php echo $pending_bookings; ?></h3></div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm bg-info text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div><p class="small text-uppercase mb-1 opacity-75">Total Revenue</p>
                    <h3 class="fw-bold mb-0">৳<?php echo number_format($total_revenue, 0); ?></h3></div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOKING APPLICATIONS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Booking Applications — Eligibility Review</h5>
            <?php if ($pending_bookings > 0): ?>
                <span class="badge bg-warning text-dark"><?php echo $pending_bookings; ?> Pending</span>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Student</th><th>Reg. ID</th><th>Department</th><th>Year</th>
                            <th>Hall</th><th>Room</th><th>Status</th><th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bookings = $conn->query("
                            SELECT b.booking_id, b.status, b.room_id,
                                   u.name, u.email,
                                   s.student_reg_id, s.department, s.year,
                                   h.name AS hostel_name
                            FROM bookings b
                            JOIN students s ON b.student_id = s.student_id
                            JOIN users u ON s.user_id = u.user_id
                            LEFT JOIN rooms r ON b.room_id = r.room_id
                            LEFT JOIN hostels h ON r.hostel_id = h.hostel_id
                            ORDER BY FIELD(b.status,'Pending','Approved','Rejected'), b.booking_id DESC
                        ");
                        if ($bookings && $bookings->num_rows > 0):
                            while ($b = $bookings->fetch_assoc()):
                                $sbadge = match($b['status']) {
                                    'Approved' => 'success', 'Rejected' => 'danger',
                                    'Pending' => 'warning', default => 'secondary'
                                };
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($b['name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($b['email']); ?></small></td>
                                <td><code><?php echo htmlspecialchars($b['student_reg_id'] ?? '—'); ?></code></td>
                                <td class="small"><?php echo htmlspecialchars($b['department'] ?? '—'); ?></td>
                                <td><?php echo $b['year'] ? $b['year'].' yr' : '—'; ?></td>
                                <td><?php echo htmlspecialchars($b['hostel_name'] ?? '—'); ?></td>
                                <td>Room #<?php echo $b['room_id'] ?? '—'; ?></td>
                                <td><span class="badge bg-<?php echo $sbadge; ?>"><?php echo $b['status']; ?></span></td>
                                <td class="text-center">
                                    <?php if ($b['status'] === 'Pending'): ?>
                                        <a href="../../api/registrar_booking.php?id=<?php echo $b['booking_id']; ?>&action=approve"
                                           class="btn btn-success btn-sm me-1"
                                           onclick="return confirm('Approve this booking?')">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </a>
                                        <a href="../../api/registrar_booking.php?id=<?php echo $b['booking_id']; ?>&action=reject"
                                           class="btn btn-outline-danger btn-sm"
                                           onclick="return confirm('Reject this booking?')">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </a>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No booking applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STUDENT RECORDS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Student Records</h5>
            <a href="../../api/student_report.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr><th>Name</th><th>Reg. ID</th><th>Email</th><th>Department</th>
                        <th>Year</th><th>Blood Group</th><th>Hall</th><th>Room</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $students = $conn->query("
                            SELECT u.name, u.email, s.student_reg_id, s.department,
                                   s.year, s.blood_group, s.room_id, h.name AS hostel_name
                            FROM students s
                            JOIN users u ON s.user_id = u.user_id
                            LEFT JOIN hostels h ON s.hostel_id = h.hostel_id
                            ORDER BY s.student_id DESC
                        ");
                        if ($students && $students->num_rows > 0):
                            while ($st = $students->fetch_assoc()):
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($st['name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($st['student_reg_id'] ?? '—'); ?></code></td>
                                <td class="small text-muted"><?php echo htmlspecialchars($st['email']); ?></td>
                                <td class="small"><?php echo htmlspecialchars($st['department'] ?? '—'); ?></td>
                                <td><?php echo $st['year'] ? $st['year'].' yr' : '—'; ?></td>
                                <td><span class="badge bg-danger bg-opacity-75"><?php echo $st['blood_group'] ?? '—'; ?></span></td>
                                <td><?php echo htmlspecialchars($st['hostel_name'] ?? '—'); ?></td>
                                <td><?php echo $st['room_id'] ? 'Room #'.$st['room_id'] : '—'; ?></td>
                                <td>
                                    <?php if ($st['room_id']): ?>
                                        <span class="badge bg-success">Allocated</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Allocated</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="9" class="text-center py-4 text-muted">No student records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAYMENT RECORDS (Read-only) -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-file-invoice-dollar me-2 text-success"></i>Payment Records
                <small class="text-muted fw-normal fs-6 ms-2">(View Only)</small>
            </h5>
            <a href="../../api/payment_report.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr><th>Receipt No.</th><th>Student</th><th>Reg. ID</th><th>Type</th>
                        <th>Month</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $payments = $conn->query("
                            SELECT p.receipt_no, p.payment_type, p.month, p.amount,
                                   p.payment_method, p.payment_date, p.status,
                                   u.name AS student_name, s.student_reg_id
                            FROM payments p
                            LEFT JOIN students s ON p.student_id = s.student_id
                            LEFT JOIN users u ON s.user_id = u.user_id
                            ORDER BY p.payment_date DESC LIMIT 50
                        ");
                        if ($payments && $payments->num_rows > 0):
                            while ($p = $payments->fetch_assoc()):
                                $badge = match($p['status']) {
                                    'Verified' => 'success', 'Pending' => 'warning',
                                    'Rejected' => 'danger', default => 'secondary'
                                };
                        ?>
                            <tr>
                                <td><code class="small"><?php echo htmlspecialchars($p['receipt_no'] ?? '—'); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($p['student_name'] ?? '—'); ?></strong></td>
                                <td><small><?php echo htmlspecialchars($p['student_reg_id'] ?? '—'); ?></small></td>
                                <td><?php echo htmlspecialchars($p['payment_type']); ?></td>
                                <td><?php echo htmlspecialchars($p['month'] ?? '—'); ?></td>
                                <td class="fw-bold text-success">৳<?php echo number_format($p['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($p['payment_method'] ?? '—'); ?></td>
                                <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                                <td><span class="badge bg-<?php echo $badge; ?> px-2 py-1"><?php echo $p['status']; ?></span></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="9" class="text-center py-4 text-muted">No payment records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => bootstrap.Alert.getOrCreateInstance(a).close());
    }, 5000);
</script>

<?php include "../../includes/footer.php"; ?>