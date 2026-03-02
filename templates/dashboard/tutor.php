<?php 
/**
 * tutor.php  —  House Tutor Dashboard
 * Place at:  /hostel-management-system/templates/dashboard/tutor.php
 */
require_once "../../includes/auth.php";
checkAccess(['HouseTutor']);
include "../../includes/header.php"; 
include "../../includes/db.php";

$tutor_id = $_SESSION['user_id'];

// ── Stats ──────────────────────────────────────────────────────────────────
$total_c    = $conn->query("SELECT COUNT(*) AS c FROM Complaints")->fetch_assoc()['c'];
$open_c     = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'Open'")->fetch_assoc()['c'];
$inprog_c   = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'InProgress'")->fetch_assoc()['c'];
$resolved_c = $conn->query("SELECT COUNT(*) AS c FROM Complaints WHERE status = 'Resolved'")->fetch_assoc()['c'];

// ── Fetch complaints ────────────────────────────────────────────────────────
// FIX 1: Also join Hostels on Rooms so we get hall info even when
//         s.hostel_id is NULL (student applied but not yet allocated).
//         We also pull s.student_reg_id so the modal can show it.
$result = $conn->query("
    SELECT 
        c.complaint_id,
        c.description,
        c.status,
        c.created_at,
        u.name        AS student_name,
        u.email       AS student_email,
        s.student_reg_id,
        s.room_id,
        s.department,
        s.contact_no,
        s.hostel_id,
        COALESCE(h_student.name, h_room.name) AS hostel_name,
        tu.name       AS assigned_tutor
    FROM Complaints c
    JOIN  Students s        ON c.student_id  = s.student_id
    JOIN  Users    u        ON s.user_id     = u.user_id
    LEFT JOIN Hostels h_student ON s.hostel_id   = h_student.hostel_id
    LEFT JOIN Rooms   r         ON s.room_id     = r.room_id
    LEFT JOIN Hostels h_room    ON r.hostel_id   = h_room.hostel_id
    LEFT JOIN Users   tu        ON c.tutor_id    = tu.user_id
    ORDER BY FIELD(c.status,'Open','InProgress','Resolved'), c.created_at DESC
");
$rows = [];
while ($r = $result->fetch_assoc()) $rows[] = $r;

$msg = $_GET['msg'] ?? '';
?>

<div class="container-fluid py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-semibold mb-0">House Tutor Dashboard</h4>
            <p class="text-muted small mb-0">Review complaints and update their status</p>
        </div>
        <span class="badge bg-primary px-3 py-2">
            <i class="fas fa-user-tie me-1"></i><?php echo htmlspecialchars($_SESSION['name']); ?>
        </span>
    </div>

    <?php if ($msg === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>Complaint status updated successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif ($msg === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>Something went wrong. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats ---------------------------------------------------------------->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center">
                <div class="text-muted small mb-1">Total</div>
                <div class="fs-4 fw-bold"><?php echo $total_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left:3px solid #dc3545 !important;">
                <div class="text-muted small mb-1">Open</div>
                <div class="fs-4 fw-bold text-danger"><?php echo $open_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left:3px solid #ffc107 !important;">
                <div class="text-muted small mb-1">In Progress</div>
                <div class="fs-4 fw-bold text-warning"><?php echo $inprog_c; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border p-3 text-center" style="border-left:3px solid #198754 !important;">
                <div class="text-muted small mb-1">Resolved</div>
                <div class="fs-4 fw-bold text-success"><?php echo $resolved_c; ?></div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs ---------------------------------------------------------->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link active small" data-filter="all"        href="#">All</a></li>
        <li class="nav-item"><a class="nav-link small"        data-filter="Open"       href="#">Open</a></li>
        <li class="nav-item"><a class="nav-link small"        data-filter="InProgress" href="#">In Progress</a></li>
        <li class="nav-item"><a class="nav-link small"        data-filter="Resolved"   href="#">Resolved</a></li>
    </ul>

    <!-- Table ---------------------------------------------------------------->
    <div class="card border">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" id="complaintsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 text-muted small fw-semibold" style="width:40px;">#</th>
                            <th class="text-muted small fw-semibold">Student</th>
                            <th class="text-muted small fw-semibold">Preview</th>
                            <th class="text-muted small fw-semibold" style="width:90px;">Room</th>
                            <th class="text-muted small fw-semibold" style="width:105px;">Filed On</th>
                            <th class="text-muted small fw-semibold text-center" style="width:110px;">Status</th>
                            <th class="text-muted small fw-semibold text-center" style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($rows) > 0): $i = 1;
                        foreach ($rows as $row):
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
                            <div class="text-muted" style="font-size:0.68rem;">
                                <?php echo htmlspecialchars($row['hostel_name'] ?? '—'); ?>
                            </div>
                        </td>
                        <td class="small text-muted">
                            <?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 55, '…')); ?>
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
                            <button class="btn btn-outline-primary btn-sm py-0 px-2"
                                    onclick='openModal(<?php echo json_encode($row, JSON_HEX_APOS|JSON_HEX_QUOT); ?>)'>
                                <i class="fas fa-eye me-1"></i>View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clipboard fa-2x mb-2 d-block opacity-25"></i>No complaints found.
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════  MODAL  ═══════════════════════════════════════ -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-semibold">
                    <i class="fas fa-file-lines me-2 text-primary"></i>Full Complaint Detail
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <!-- Student Info Card -->
                <div class="bg-light border rounded p-3 mb-3">
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Student:</span>&nbsp;
                            <strong id="m-student"></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Contact:</span>&nbsp;
                            <span id="m-contact"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Hall:</span>&nbsp;
                            <span id="m-hall"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Room:</span>&nbsp;
                            <span id="m-room"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Department:</span>&nbsp;
                            <span id="m-dept"></span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Filed:</span>&nbsp;
                            <span id="m-date"></span>
                        </div>
                    </div>
                </div>

                <!-- Full Description -->
                <label class="form-label small fw-semibold text-muted text-uppercase mb-1">
                    Full Complaint
                </label>
                <div id="m-desc"
                     class="border rounded p-3 small bg-white mb-3"
                     style="min-height:90px; line-height:1.75; white-space:pre-wrap;"></div>

                <!-- Status + assigned tutor -->
                <div class="d-flex align-items-center gap-2 small">
                    <span class="text-muted">Status:</span>
                    <span id="m-badge"></span>
                    <span class="text-muted" id="m-tutor"></span>
                </div>
            </div>

            <!-- FIX 2: Action buttons point to the NEW dedicated API -->
            <div class="modal-footer border-top" id="m-actions"></div>

        </div>
    </div>
</div>

<script>
// FIX 2: Point to the new dedicated tutor API (NOT complaint_api.php)
const API = '../../api/update_complaint.php';

function openModal(row) {
    // ── Student info ──────────────────────────────────────────────────────
    document.getElementById('m-student').textContent = row.student_name || '—';
    document.getElementById('m-contact').textContent = row.contact_no   || '—';
    document.getElementById('m-dept').textContent    = row.department   || '—';

    // FIX 1: hostel_name & room_id are now populated by the improved SQL query
    document.getElementById('m-hall').textContent =
        row.hostel_name ? row.hostel_name : '—';

    document.getElementById('m-room').textContent =
        row.room_id ? 'Room ' + row.room_id : '—';

    // Format date safely
    const d = new Date(row.created_at.replace(' ', 'T'));
    document.getElementById('m-date').textContent =
        d.toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'});

    // ── Full complaint text ───────────────────────────────────────────────
    document.getElementById('m-desc').textContent = row.description;

    // ── Status badge ──────────────────────────────────────────────────────
    const colorMap = { Open:'danger', InProgress:'warning', Resolved:'success' };
    document.getElementById('m-badge').innerHTML =
        `<span class="badge text-bg-${colorMap[row.status] || 'secondary'} fw-normal">${row.status}</span>`;

    document.getElementById('m-tutor').textContent =
        row.assigned_tutor ? '· Handled by: ' + row.assigned_tutor : '';

    // ── Action buttons ────────────────────────────────────────────────────
    // FIX 2: All three actions (InProgress, Resolved, Reopen) are wired up
    const id  = row.complaint_id;
    let html  = '';

    if (row.status === 'Open') {
        html += `<a href="${API}?action=inprogress&id=${id}"
                    class="btn btn-warning btn-sm">
                    <i class="fas fa-rotate me-1"></i>Mark as In Progress
                 </a>`;
    }

    if (row.status === 'InProgress') {
        html += `<a href="${API}?action=resolve&id=${id}"
                    class="btn btn-success btn-sm">
                    <i class="fas fa-circle-check me-1"></i>Mark as Resolved
                 </a>`;
    }

    if (row.status === 'Resolved' || row.status === 'InProgress') {
        html += `<a href="${API}?action=reopen&id=${id}"
                    class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-rotate-left me-1"></i>Reopen
                 </a>`;
    }

    html += `<button class="btn btn-light btn-sm ms-auto"
                     data-bs-dismiss="modal">Close</button>`;

    document.getElementById('m-actions').innerHTML = html;

    // Show modal
    new bootstrap.Modal(document.getElementById('complaintModal')).show();
}

// ── Filter tabs ───────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-filter]').forEach(tab => {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelectorAll('[data-filter]').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('#complaintsTable tbody tr[data-status]').forEach(row => {
                row.style.display =
                    (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        });
    });
});
</script>

<?php include "../../includes/footer.php"; ?>
