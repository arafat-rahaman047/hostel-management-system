<?php 
require_once "../includes/auth.php";
checkAccess(['Student']); 
include "../includes/header.php"; 
include "../includes/db.php";

$user_id    = $_SESSION['user_id'];
$success_msg = '';
$error_msg   = '';

// Get student_id
$stmt = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student    = $stmt->get_result()->fetch_assoc();
$student_id = $student['student_id'] ?? null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $student_id) {
    $description = trim($_POST['description']);
    if (!empty($description)) {
        $ins = $conn->prepare("INSERT INTO Complaints (student_id, description, status) VALUES (?, ?, 'Open')");
        $ins->bind_param("is", $student_id, $description);
        if ($ins->execute()) {
            $log = $conn->prepare("INSERT INTO Logs (user_id, action) VALUES (?, 'Filed a complaint')");
            $log->bind_param("i", $user_id);
            $log->execute();
            $success_msg = "Complaint submitted successfully.";
        } else {
            $error_msg = "Something went wrong. Please try again.";
        }
    } else {
        $error_msg = "Please describe your issue before submitting.";
    }
}

// Fetch complaints
if ($student_id) {
    $cq = $conn->prepare("SELECT complaint_id, description, status, created_at FROM Complaints WHERE student_id = ? ORDER BY created_at DESC");
    $cq->bind_param("i", $student_id);
    $cq->execute();
    $complaints_result = $cq->get_result();
}

// Stats
$total_c    = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE student_id = $student_id")->fetch_assoc()['c'] ?? 0;
$open_c     = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE student_id = $student_id AND status = 'Open'")->fetch_assoc()['c'] ?? 0;
$inprog_c   = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE student_id = $student_id AND status = 'InProgress'")->fetch_assoc()['c'] ?? 0;
$resolved_c = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE student_id = $student_id AND status = 'Resolved'")->fetch_assoc()['c'] ?? 0;
?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="../templates/dashboard/student.php" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Complaints</li>
        </ol>
    </nav>

    <!-- Page Title -->
    <div class="mb-4">
        <h4 class="fw-semibold mb-0">Complaints &amp; Maintenance</h4>
        <p class="text-muted small mb-0">Report issues with your room, furniture, electricity, or other facilities.</p>
    </div>

    <!-- Alerts -->
    <?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error_msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border py-3">
                <div class="text-muted small mb-1">Total</div>
                <div class="fs-4 fw-bold"><?php echo $total_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border py-3 border-danger border-top border-top-0" style="border-left: 3px solid #dc3545 !important;">
                <div class="text-muted small mb-1">Open</div>
                <div class="fs-4 fw-bold text-danger"><?php echo $open_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border py-3" style="border-left: 3px solid #ffc107 !important;">
                <div class="text-muted small mb-1">In Progress</div>
                <div class="fs-4 fw-bold text-warning"><?php echo $inprog_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border py-3" style="border-left: 3px solid #198754 !important;">
                <div class="text-muted small mb-1">Resolved</div>
                <div class="fs-4 fw-bold text-success"><?php echo $resolved_c; ?></div>
            </div>
        </div>
    </div>

    <!-- Main Row -->
    <div class="row g-4">

        <!-- Form -->
        <div class="col-lg-4">
            <div class="card border h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-1">File a New Complaint</h6>
                    <p class="text-muted small mb-3">The House Tutor will review your issue.</p>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">
                                Description <span class="text-danger">*</span>
                            </label>
                            <textarea
                                name="description"
                                rows="7"
                                class="form-control form-control-sm"
                                placeholder="Describe your issue — include room number, location, and when it started."
                                required></textarea>
                        </div>

                        <div class="alert alert-light border small py-2 mb-3">
                            <i class="fas fa-info-circle text-primary me-1"></i>
                            Your complaint will be assigned to the House Tutor on duty once submitted.
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                            <i class="fas fa-paper-plane me-1"></i> Submit Complaint
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History Table -->
        <div class="col-lg-8">
            <div class="card border h-100">
                <div class="card-body pb-0">
                    <h6 class="fw-semibold mb-1">My Complaint History</h6>
                    <p class="text-muted small mb-3">Track the status of all your submitted complaints.</p>
                </div>

                <?php if (!empty($complaints_result) && $complaints_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-muted small fw-semibold" style="width:40px;">#</th>
                                <th class="text-muted small fw-semibold">Description</th>
                                <th class="text-muted small fw-semibold" style="width:100px;">Date</th>
                                <th class="text-muted small fw-semibold text-center" style="width:115px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $i = 1;
                        while ($c = $complaints_result->fetch_assoc()):
                            $badge = match($c['status']) {
                                'Open'       => 'danger',
                                'InProgress' => 'warning',
                                'Resolved'   => 'success',
                                default      => 'secondary'
                            };
                            $icon = match($c['status']) {
                                'Open'       => 'fas fa-circle-exclamation',
                                'InProgress' => 'fas fa-rotate',
                                'Resolved'   => 'fas fa-circle-check',
                                default      => 'fas fa-circle'
                            };
                            $short = mb_strimwidth($c['description'], 0, 65, '…');
                            $date  = date('d M Y', strtotime($c['created_at']));
                        ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?php echo $i++; ?></td>
                            <td>
                                <span class="small" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($c['description']); ?>">
                                    <?php echo htmlspecialchars($short); ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo $date; ?></td>
                            <td class="text-center">
                                <span class="badge text-bg-<?php echo $badge; ?> fw-normal">
                                    <i class="<?php echo $icon; ?> me-1"></i><?php echo $c['status']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard fa-2x mb-3 d-block opacity-25"></i>
                    <p class="small mb-0">No complaints filed yet.</p>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div><!-- end row -->
</div><!-- end container -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});
</script>

<?php include "../includes/footer.php"; ?>
