<?php 
require_once "../../includes/auth.php";
checkAccess(['HouseTutor']);
include "../../includes/header.php"; 
include "../../includes/db.php";

$tutor_id = $_SESSION['user_id'];

// Stats
$total_c    = $conn->query("SELECT COUNT(*) AS c FROM Complaints")->fetch_assoc()['c'];
$open_c     = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'Open'")->fetch_assoc()['c'];
$inprog_c   = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'InProgress'")->fetch_assoc()['c'];
$resolved_c = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'Resolved'")->fetch_assoc()['c'];

// Fetch all complaints with student info
$complaints = $conn->query("
    SELECT 
        c.complaint_id,
        c.description,
        c.status,
        c.created_at,
        u.name  AS student_name,
        u.email AS student_email,
        s.room_id,
        s.department,
        h.name  AS hostel_name,
        tu.name AS assigned_tutor
    FROM Complaints c
    JOIN Students s  ON c.student_id  = s.student_id
    JOIN Users u     ON s.user_id     = u.user_id
    LEFT JOIN Hostels h  ON s.hostel_id  = h.hostel_id
    LEFT JOIN Users tu   ON c.tutor_id   = tu.user_id
    ORDER BY 
        FIELD(c.status, 'Open', 'InProgress', 'Resolved'),
        c.created_at DESC
");

// Flash message from redirect
$msg = $_GET['msg'] ?? '';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-semibold mb-0">House Tutor Dashboard</h4>
            <p class="text-muted small mb-0">Manage and resolve student complaints</p>
        </div>
        <span class="badge bg-primary">
            <i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
        </span>
    </div>

    <!-- Flash message -->
    <?php if ($msg === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Complaint status updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($msg === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>Something went wrong. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center">
                <div class="text-muted small mb-1">Total</div>
                <div class="fs-4 fw-bold"><?php echo $total_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left: 3px solid #dc3545 !important;">
                <div class="text-muted small mb-1">Open</div>
                <div class="fs-4 fw-bold text-danger"><?php echo $open_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left: 3px solid #ffc107 !important;">
                <div class="text-muted small mb-1">In Progress</div>
                <div class="fs-4 fw-bold text-warning"><?php echo $inprog_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left: 3px solid #198754 !important;">
                <div class="text-muted small mb-1">Resolved</div>
                <div class="fs-4 fw-bold text-success"><?php echo $resolved_c; ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-3" id="complaintTabs">
        <li class="nav-item">
            <a class="nav-link active small" data-filter="all" href="#">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small text-danger" data-filter="Open" href="#">Open</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small text-warning" data-filter="InProgress" href="#">In Progress</a>
        </li>
        <li class="nav-item">
            <a class="nav-link small text-success" data-filter="Resolved" href="#">Resolved</a>
        </li>
    </ul>

    <!-- Complaints Table -->
    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" id="complaintsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-muted small fw-semibold" style="width:40px;">#</th>
                            <th class="text-muted small fw-semibold">Student</th>
                            <th class="text-muted small fw-semibold">Description</th>
                            <th class="text-muted small fw-semibold" style="width:95px;">Room</th>
                            <th class="text-muted small fw-semibold" style="width:105px;">Filed On</th>
                            <th class="text-muted small fw-semibold text-center" style="width:110px;">Status</th>
                            <th class="text-muted small fw-semibold text-center" style="width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($complaints->num_rows > 0):
                        $i = 1;
                        while ($row = $complaints->fetch_assoc()):
                            $badge = match($row['status']) {
                                'Open'       => 'danger',
                                'InProgress' => 'warning',
                                'Resolved'   => 'success',
                                default      => 'secondary'
                            };
                    ?>
                    <tr data-status="<?php echo $row['status']; ?>">
                        <td class="ps-3 text-muted small"><?php echo $i++; ?></td>
                        <td>
                            <div class="small fw-semibold"><?php echo htmlspecialchars($row['student_name']); ?></div>
                            <div class="text-muted" style="font-size:0.68rem;"><?php echo htmlspecialchars($row['hostel_name'] ?? 'N/A'); ?></div>
                        </td>
                        <td>
                            <span class="small" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($row['description']); ?>">
                                <?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 60, '…')); ?>
                            </span>
                        </td>
                        <td class="small text-muted">
                            <?php echo $row['room_id'] ? 'Room ' . $row['room_id'] : '—'; ?>
                        </td>
                        <td class="small text-muted">
                            <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge text-bg-<?php echo $badge; ?> fw-normal">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($row['status'] === 'Open'): ?>
                                <a href="../../api/complaint_api.php?action=inprogress&id=<?php echo $row['complaint_id']; ?>&tutor_id=<?php echo $tutor_id; ?>"
                                   class="btn btn-warning btn-sm py-0 px-2"
                                   title="Mark as In Progress">
                                    <i class="fas fa-rotate me-1"></i>In Progress
                                </a>

                            <?php elseif ($row['status'] === 'InProgress'): ?>
                                <a href="../../api/complaint_api.php?action=resolve&id=<?php echo $row['complaint_id']; ?>&tutor_id=<?php echo $tutor_id; ?>"
                                   class="btn btn-success btn-sm py-0 px-2"
                                   title="Mark as Resolved">
                                    <i class="fas fa-circle-check me-1"></i>Resolve
                                </a>

                            <?php else: ?>
                                <span class="text-muted small"><i class="fas fa-check me-1"></i>Done</span>

                            <?php endif; ?>

                            <!-- Always show reopen for non-open -->
                            <?php if ($row['status'] !== 'Open'): ?>
                                <a href="../../api/complaint_api.php?action=reopen&id=<?php echo $row['complaint_id']; ?>&tutor_id=<?php echo $tutor_id; ?>"
                                   class="btn btn-outline-secondary btn-sm py-0 px-2 ms-1"
                                   title="Reopen complaint">
                                    <i class="fas fa-arrow-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard fa-2x mb-2 d-block opacity-25"></i>
                            No complaints found.
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
// Tooltip init
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // Tab filter logic
    document.querySelectorAll('[data-filter]').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('[data-filter]').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            document.querySelectorAll('#complaintsTable tbody tr[data-status]').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        });
    });
});
</script>

<?php include "../../includes/footer.php"; ?>
