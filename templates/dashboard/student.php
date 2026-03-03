<?php
require_once "../../includes/auth.php";
checkAccess(['Student']);
include "../../includes/header.php";
include "../../includes/db.php";

$user_id = $_SESSION['user_id'];

// Get student + room + hostel info
$query = "SELECT s.*, r.room_id, r.floor, h.name as hostel_name
          FROM students s
          LEFT JOIN rooms r ON s.room_id = r.room_id
          LEFT JOIN hostels h ON s.hostel_id = h.hostel_id
          WHERE s.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_data = $stmt->get_result()->fetch_assoc();

if (!$student_data) {
    $student_data = [
        'room_id'      => null,
        'hostel_name'  => 'Profile Not Found',
        'student_id'   => null,
        'hostel_id'    => null,
    ];
}

// ── PAYMENT STATUS ────────────────────────────────────────────────────────────
$payment_status_label = 'No Payments';
$payment_status_color = 'secondary';
$payment_status_note  = 'No payment records found';
$active_complaints    = 0;

if (!empty($student_data['student_id'])) {
    $student_id = $student_data['student_id'];

    $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM Payments WHERE student_id = ? AND status = 'Pending'");
    $q->bind_param("i", $student_id);
    $q->execute();
    $pending_count = $q->get_result()->fetch_assoc()['cnt'] ?? 0;

    $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM Payments WHERE student_id = ? AND status = 'Rejected'");
    $q->bind_param("i", $student_id);
    $q->execute();
    $rejected_count = $q->get_result()->fetch_assoc()['cnt'] ?? 0;

    $q = $conn->prepare("SELECT status, payment_date, payment_type FROM Payments WHERE student_id = ? ORDER BY payment_date DESC LIMIT 1");
    $q->bind_param("i", $student_id);
    $q->execute();
    $latest_payment = $q->get_result()->fetch_assoc();

    if ($rejected_count > 0) {
        $payment_status_label = 'Payment Rejected';
        $payment_status_color = 'danger';
        $payment_status_note  = "$rejected_count payment(s) rejected — please resubmit";
    } elseif ($pending_count > 0) {
        $payment_status_label = 'Pending Verification';
        $payment_status_color = 'warning';
        $payment_status_note  = "$pending_count payment(s) awaiting admin approval";
    } elseif (!empty($latest_payment) && $latest_payment['status'] === 'Verified') {
        $payment_status_label = 'Clear';
        $payment_status_color = 'success';
        $payment_status_note  = 'Last verified: ' . date('d M Y', strtotime($latest_payment['payment_date']));
    }

    $q = $conn->prepare("SELECT COUNT(*) AS cnt FROM Complaints WHERE student_id = ? AND status != 'Resolved'");
    $q->bind_param("i", $student_id);
    $q->execute();
    $active_complaints = $q->get_result()->fetch_assoc()['cnt'] ?? 0;
}

// ── LATEST NOTICE (for student's hostel OR all halls) ─────────────────────────
$latest_notice    = null;
$hostel_id_notice = $student_data['hostel_id'] ?? null;

$nq = $conn->prepare("
    SELECT title, priority, created_at
    FROM notices
    WHERE hostel_id = ? OR hostel_id IS NULL
    ORDER BY created_at DESC
    LIMIT 1
");
$nq->bind_param("i", $hostel_id_notice);
$nq->execute();
$latest_notice = $nq->get_result()->fetch_assoc();
?>

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold">Student Dashboard</h3>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>
    </div>

    <!-- ── LATEST NOTICE BANNER ── -->
    <?php if ($latest_notice): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info d-flex align-items-center gap-3 border-0 shadow-sm mb-0" role="alert">
                <i class="fas fa-bullhorn fa-lg flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong>New Notice:</strong>
                    <?php echo htmlspecialchars($latest_notice['title']); ?>
                    <small class="text-muted ms-2">
                        <?php echo date('d M Y', strtotime($latest_notice['created_at'])); ?>
                    </small>
                </div>
                <a href="../../modules/notifications.php"
                   class="btn btn-sm btn-outline-info flex-shrink-0">
                    View All
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── STAT CARDS ── -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-primary border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Room Status</h6>
                    <h4 class="mb-0">
                        <?php echo $student_data['room_id'] ? "Room " . $student_data['room_id'] : "Not Allocated"; ?>
                    </h4>
                    <small class="text-primary">
                        <?php echo htmlspecialchars($student_data['hostel_name'] ?? "No Hostel Assigned"); ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-<?php echo $payment_status_color; ?> border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Payment Status</h6>
                    <h4 class="mb-0 text-<?php echo $payment_status_color; ?>">
                        <?php echo htmlspecialchars($payment_status_label); ?>
                    </h4>
                    <small class="text-muted"><?php echo htmlspecialchars($payment_status_note); ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-<?php echo $active_complaints > 0 ? 'danger' : 'warning'; ?> border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Active Complaints</h6>
                    <h4 class="mb-0 text-<?php echo $active_complaints > 0 ? 'danger' : 'success'; ?>">
                        <?php echo $active_complaints; ?>
                    </h4>
                    <small class="text-muted">
                        <?php echo $active_complaints > 0 ? "$active_complaints unresolved issue(s)" : "All issues resolved"; ?>
                    </small>
                </div>
            </div>
        </div>

    </div>

    <!-- ── QUICK ACTION CARDS ── -->
    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 mx-auto mb-3" style="width:70px;">
                    <i class="fas fa-bed fa-2x text-primary"></i>
                </div>
                <h5>Room Booking</h5>
                <p class="small text-muted">Apply for a room or check allocation status.</p>
                <a href="../../modules/booking.php" class="btn btn-outline-primary btn-sm mt-auto stretched-link">
                    Manage Booking
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 mx-auto mb-3" style="width:70px;">
                    <i class="fas fa-file-invoice-dollar fa-2x text-success"></i>
                </div>
                <h5>Payments</h5>
                <p class="small text-muted">Pay hostel fees and view transaction history.</p>
                <a href="../../modules/payment.php" class="btn btn-outline-success btn-sm mt-auto stretched-link">
                    View Receipts
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 mx-auto mb-3" style="width:70px;">
                    <i class="fas fa-tools fa-2x text-warning"></i>
                </div>
                <h5>Complaints</h5>
                <p class="small text-muted">Report issues with furniture or electricity.</p>
                <a href="../../modules/complaints.php" class="btn btn-outline-warning btn-sm mt-auto stretched-link">
                    File Complaint
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm text-center p-3 hover-lift">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 mx-auto mb-3" style="width:70px;">
                    <i class="fas fa-bullhorn fa-2x text-info"></i>
                </div>
                <h5>Notices</h5>
                <p class="small text-muted">Latest updates from the Admin office.</p>
                <a href="../../modules/notifications.php" class="btn btn-outline-info btn-sm mt-auto stretched-link">
                    View All
                </a>
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
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
</style>

<?php include "../../includes/footer.php"; ?>