<?php
include "../../includes/auth.php";
include "../../includes/db.php";
include "../../includes/header.php";

if ($_SESSION['role'] != "AdminOfficer") {
    die("Access Denied");
}

// Statistics
$totalStudents       = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='Student'")->fetch_assoc()['total'];
$totalRooms          = $conn->query("SELECT COUNT(*) AS total FROM rooms")->fetch_assoc()['total'];
$pendingBookings     = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE status='Pending'")->fetch_assoc()['total'];
$totalComplaints     = $conn->query("SELECT COUNT(*) AS total FROM complaints WHERE status!='Resolved'")->fetch_assoc()['total'];
$totalRevenueResult  = $conn->query("SELECT SUM(amount) AS total FROM payments WHERE status='Verified'")->fetch_assoc();
$totalRevenue        = $totalRevenueResult['total'] ?? 0;
$pendingPaymentsResult = $conn->query("SELECT COUNT(*) AS total FROM payments WHERE status='Pending'")->fetch_assoc();
$pendingPayments     = $pendingPaymentsResult['total'] ?? 0;

// Total notices count
$totalNoticesResult  = $conn->query("SELECT COUNT(*) AS total FROM notices");
$totalNotices        = $totalNoticesResult ? $totalNoticesResult->fetch_assoc()['total'] : 0;
?>

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-primary">Admin Control Panel</h3>
            <?php
            $msg = $_GET['msg'] ?? '';
            if ($msg === 'room_added'): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div><strong>Room added successfully!</strong> The new room is now available for booking.</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($msg === 'notice_posted'): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-bullhorn fa-lg"></i>
                    <div><strong>Notice posted!</strong> Students can now see it on their dashboard.</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($msg === 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div><strong>Success!</strong> Operation completed.</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($msg === 'error'): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div><strong>Error!</strong> Something went wrong. Please try again.</div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <p class="text-muted">University of Barishal — Hall Management System</p>
        </div>
    </div>

    <!-- ===== STAT CARDS ===== -->
    <div class="row g-3 mb-5">

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Students</p>
                        <h3 class="fw-bold mb-0"><?php echo $totalStudents; ?></h3>
                    </div>
                    <i class="fas fa-user-graduate fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-info text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Total Rooms</p>
                        <h3 class="fw-bold mb-0"><?php echo $totalRooms; ?></h3>
                    </div>
                    <i class="fas fa-door-open fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Pending Bookings</p>
                        <h3 class="fw-bold mb-0"><?php echo $pendingBookings; ?></h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-danger text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Open Complaints</p>
                        <h3 class="fw-bold mb-0"><?php echo $totalComplaints; ?></h3>
                    </div>
                    <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Total Revenue</p>
                        <h3 class="fw-bold mb-0">৳<?php echo number_format($totalRevenue, 0); ?></h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-2 col-sm-4">
            <div class="card border-0 shadow-sm bg-secondary text-white p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="small text-uppercase mb-1 opacity-75">Pending Payments</p>
                        <h3 class="fw-bold mb-0"><?php echo $pendingPayments; ?></h3>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- ===== MANAGE ROOMS ===== -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-door-open me-2 text-primary"></i>Manage Rooms</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="../../api/add_room.php" class="row g-2">
                <div class="col-md-4">
                    <select name="hostel_id" class="form-select" required>
                        <option value="" disabled selected>Select Hostel</option>
                        <?php
                        $hlist = $conn->query("SELECT * FROM hostels");
                        while ($h = $hlist->fetch_assoc()):
                        ?>
                            <option value="<?php echo $h['hostel_id']; ?>">
                                <?php echo htmlspecialchars($h['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="floor" class="form-control" placeholder="Floor" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="capacity" class="form-control" placeholder="Capacity" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus me-1"></i> Add Room
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== PENDING BOOKINGS ===== -->
    <div class="card border-0 shadow-sm mb-4" id="bookingSection">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-bed me-2 text-primary"></i>Pending Bookings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Student</th>
                            <th>Room</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bookings = $conn->query("
                            SELECT b.booking_id, u.name, b.room_id
                            FROM bookings b
                            JOIN students s ON b.student_id = s.student_id
                            JOIN users u ON s.user_id = u.user_id
                            WHERE b.status='Pending'
                        ");
                        if ($bookings->num_rows > 0):
                            while ($b = $bookings->fetch_assoc()):
                        ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($b['name']); ?></strong></td>
                                    <td>Room #<?php echo $b['room_id']; ?></td>
                                    <td class="text-center">
                                        <a href="../../api/approve_booking.php?id=<?php echo $b['booking_id']; ?>&action=approve"
                                            class="btn btn-success btn-sm px-3">Approve</a>
                                        <a href="../../api/approve_booking.php?id=<?php echo $b['booking_id']; ?>&action=reject"
                                            class="btn btn-outline-danger btn-sm px-3">Reject</a>
                                    </td>
                                </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No pending bookings.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== PAYMENT MANAGEMENT ===== -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave me-2 text-success"></i>Payment Management</h5>
            <a href="../../api/payment_report.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
        <div class="card-body">

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo (!isset($_GET['pstatus']) || $_GET['pstatus'] === 'Pending') ? 'active' : ''; ?>"
                        href="?pstatus=Pending">
                        <span class="text-warning">⏳</span> Pending
                        <?php
                        $pc = $conn->query("SELECT COUNT(*) AS c FROM payments WHERE status='Pending'")->fetch_assoc()['c'];
                        if ($pc > 0) echo "<span class='badge bg-warning text-dark ms-1'>$pc</span>";
                        ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (($_GET['pstatus'] ?? '') === 'Verified') ? 'active' : ''; ?>"
                        href="?pstatus=Verified">
                        <span class="text-success">✅</span> Verified
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (($_GET['pstatus'] ?? '') === 'Rejected') ? 'active' : ''; ?>"
                        href="?pstatus=Rejected">
                        <span class="text-danger">❌</span> Rejected
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo (($_GET['pstatus'] ?? '') === 'all') ? 'active' : ''; ?>"
                        href="?pstatus=all">
                        📋 All
                    </a>
                </li>
            </ul>

            <?php
            $pstatus = $_GET['pstatus'] ?? 'Pending';
            if ($pstatus === 'all') {
                $where_clause = "";
            } else {
                $safe_status  = $conn->real_escape_string($pstatus);
                $where_clause = "WHERE p.status = '$safe_status'";
            }

            $payments_sql = "
                SELECT p.payment_id, p.receipt_no, p.payment_type, p.month,
                       p.amount, p.payment_method, p.transaction_id,
                       p.payment_date, p.status,
                       u.name AS student_name, s.student_reg_id
                FROM payments p
                LEFT JOIN students s ON p.student_id = s.student_id
                LEFT JOIN users u ON s.user_id = u.user_id
                $where_clause
                ORDER BY p.payment_date DESC
            ";
            $payments = $conn->query($payments_sql);
            if (!$payments) {
                echo "<div class='alert alert-danger'>Query Error: " . $conn->error . "</div>";
            }
            ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Receipt No.</th>
                            <th>Student</th>
                            <th>Type</th>
                            <th>Month</th>
                            <th>Amount</th>
                            <th>Method / TXN ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($payments && $payments->num_rows > 0):
                            while ($p = $payments->fetch_assoc()):
                                $badge = match ($p['status']) {
                                    'Verified' => 'success',
                                    'Pending'  => 'warning',
                                    'Rejected' => 'danger',
                                    default    => 'secondary'
                                };
                        ?>
                                <tr>
                                    <td><code class="small"><?php echo htmlspecialchars($p['receipt_no'] ?? '-'); ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($p['student_name'] ?? '-'); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($p['student_reg_id'] ?? ''); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['payment_type']); ?></td>
                                    <td><?php echo htmlspecialchars($p['month'] ?? '-'); ?></td>
                                    <td class="fw-bold text-success">৳<?php echo number_format($p['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($p['payment_method'] ?? '-'); ?>
                                        </span><br>
                                        <small><code><?php echo htmlspecialchars($p['transaction_id'] ?? '-'); ?></code></small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $badge; ?> px-3 py-2">
                                            <?php echo htmlspecialchars($p['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['status'] === 'Pending'): ?>
                                            <a href="../../api/verify_payment.php?id=<?php echo $p['payment_id']; ?>&action=verify"
                                                class="btn btn-success btn-sm mb-1"
                                                onclick="return confirm('Verify this payment?')">
                                                ✅ Verify
                                            </a>
                                            <a href="../../api/verify_payment.php?id=<?php echo $p['payment_id']; ?>&action=reject"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick="return confirm('Reject this payment?')">
                                                ❌ Reject
                                            </a>
                                        <?php elseif ($p['status'] === 'Verified'): ?>
                                            <a href="../../api/payment_receipt.php?id=<?php echo $p['payment_id']; ?>"
                                                class="btn btn-outline-success btn-sm" target="_blank">
                                                🧾 Receipt
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    No <strong><?php echo htmlspecialchars($pstatus); ?></strong> payments found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== COMPLAINT MANAGEMENT ===== -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-tools me-2 text-danger"></i>Manage Complaints</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Student</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $complaints = $conn->query("
                            SELECT c.complaint_id, c.description, c.status, u.name
                            FROM complaints c
                            JOIN students s ON c.student_id = s.student_id
                            JOIN users u ON s.user_id = u.user_id
                        ");
                        if ($complaints->num_rows > 0):
                            while ($c = $complaints->fetch_assoc()):
                                $cbadge = match ($c['status']) {
                                    'Resolved'   => 'success',
                                    'InProgress' => 'warning',
                                    default      => 'danger'
                                };
                        ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['description']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $cbadge; ?>">
                                            <?php echo htmlspecialchars($c['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="../../api/update_complaint.php?id=<?php echo $c['complaint_id']; ?>&status=InProgress"
                                            class="btn btn-warning btn-sm">In Progress</a>
                                        <a href="../../api/update_complaint.php?id=<?php echo $c['complaint_id']; ?>&status=Resolved"
                                            class="btn btn-success btn-sm">Resolve</a>
                                    </td>
                                </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No complaints found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ===== NOTICE MANAGEMENT ===== -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-bullhorn me-2 text-primary"></i>Post a Notice
                <?php if ($totalNotices > 0): ?>
                    <span class="badge bg-primary ms-2"><?php echo $totalNotices; ?> total</span>
                <?php endif; ?>
            </h5>
            <a href="../../modules/notifications.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-eye me-1"></i> View All Notices
            </a>
        </div>
        <div class="card-body">

            <!-- Post Notice Form -->
            <form method="POST" action="../../api/notice_api.php" class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Notice Title</label>
                    <input type="text" name="title" class="form-control"
                        placeholder="e.g. Water Supply Disruption" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hall / Hostel</label>
                    <select name="hostel_id" class="form-select">
                        <option value="">All Halls</option>
                        <?php
                        $hlist2 = $conn->query("SELECT * FROM hostels");
                        while ($h2 = $hlist2->fetch_assoc()):
                        ?>
                            <option value="<?php echo $h2['hostel_id']; ?>">
                                <?php echo htmlspecialchars($h2['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Notice Body</label>
                    <textarea name="body" class="form-control" rows="3"
                        placeholder="Write the full notice here..." required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-paper-plane me-2"></i>Post Notice
                    </button>
                </div>
            </form>

            <!-- Recent Notices Preview -->
            <hr class="mt-4">
            <h6 class="fw-bold text-muted mb-3">
                <i class="fas fa-history me-1"></i> Recently Posted Notices
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Hall</th>
                            <th>Posted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $recent_notices = $conn->query("
                            SELECT n.title, n.created_at, h.name AS hostel_name
                            FROM notices n
                            LEFT JOIN hostels h ON n.hostel_id = h.hostel_id
                            ORDER BY n.created_at DESC
                            LIMIT 5
                        ");
                        if ($recent_notices && $recent_notices->num_rows > 0):
                            while ($rn = $recent_notices->fetch_assoc()):
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rn['title']); ?></td>
                                <td><?php echo htmlspecialchars($rn['hostel_name'] ?? 'All Halls'); ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($rn['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">
                                    <i class="fas fa-bell-slash me-2 opacity-50"></i>No notices posted yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script>
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            bootstrap.Alert.getOrCreateInstance(a).close();
        });
    }, 4000);
</script>

<?php include "../../includes/footer.php"; ?>