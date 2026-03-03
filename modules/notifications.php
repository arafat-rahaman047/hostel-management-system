<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: /hostel-management-system/templates/login.php");
    exit();
}

$allowed = ['Student', 'Provost', 'AdminOfficer', 'AssistantRegistrar', 'HouseTutor'];
if (!in_array($_SESSION['role'], $allowed)) {
    header("Location: /hostel-management-system/index.php");
    exit();
}

include "../includes/header.php";
include "../includes/db.php";

$search = $conn->real_escape_string($_GET['search'] ?? '');
$where  = "WHERE 1=1";
if ($search) $where .= " AND (n.title LIKE '%$search%' OR n.body LIKE '%$search%')";

$notices = $conn->query("
    SELECT n.notice_id, n.title, n.body, n.created_at,
           u.name AS posted_by, u.role AS poster_role,
           h.name AS hostel_name
    FROM notices n
    JOIN users u ON n.posted_by = u.user_id
    LEFT JOIN hostels h ON n.hostel_id = h.hostel_id
    $where
    ORDER BY n.created_at DESC
");
?>

<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-bullhorn text-primary me-2"></i>Hall Notices</h4>
            <p class="text-muted small mb-0">Latest announcements from your Hall of Residence</p>
        </div>
        <?php if (in_array($_SESSION['role'], ['AdminOfficer', 'HouseTutor'])): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postNoticeModal">
                <i class="fas fa-plus me-2"></i>Post Notice
            </button>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>Notice posted successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $_GET['error'] === 'missing_fields' ? 'Please fill in all required fields.' : 'Something went wrong.'; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light"
                               placeholder="Search notices..."
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                </div>
                <?php if ($search): ?>
                <div class="col-md-2">
                    <a href="notifications.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Notices -->
    <?php if ($notices && $notices->num_rows > 0): ?>
    <div class="row g-3">
        <?php while ($notice = $notices->fetch_assoc()):
            $posted_at  = date('d M Y, h:i A', strtotime($notice['created_at']));
            $role_label = str_replace(
                ['AdminOfficer','AssistantRegistrar','HouseTutor'],
                ['Admin Officer','Asst. Registrar','House Tutor'],
                $notice['poster_role']
            );
            $preview = mb_strlen($notice['body']) > 120
                ? mb_substr($notice['body'], 0, 120) . '...'
                : $notice['body'];
        ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($notice['title']); ?></h6>
                            <p class="text-muted small mb-2"><?php echo htmlspecialchars($preview); ?></p>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <small class="text-muted">
                                    <i class="fas fa-user-tie me-1"></i>
                                    <?php echo htmlspecialchars($notice['posted_by']); ?>
                                    <span class="text-secondary">(<?php echo $role_label; ?>)</span>
                                </small>
                                <?php if ($notice['hostel_name']): ?>
                                <small class="text-muted">
                                    <i class="fas fa-building me-1"></i>
                                    <?php echo htmlspecialchars($notice['hostel_name']); ?>
                                </small>
                                <?php endif; ?>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i><?php echo $posted_at; ?>
                                </small>
                            </div>
                        </div>
                        <button class="btn btn-outline-primary btn-sm flex-shrink-0"
                                data-bs-toggle="modal"
                                data-bs-target="#noticeModal"
                                data-title="<?php echo htmlspecialchars($notice['title']); ?>"
                                data-body="<?php echo htmlspecialchars($notice['body']); ?>"
                                data-by="<?php echo htmlspecialchars($notice['posted_by']); ?>"
                                data-role="<?php echo htmlspecialchars($role_label); ?>"
                                data-hostel="<?php echo htmlspecialchars($notice['hostel_name'] ?? 'All Halls'); ?>"
                                data-date="<?php echo $posted_at; ?>">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-3x text-muted opacity-50 mb-3 d-block"></i>
        <h5 class="text-muted fw-bold">No Notices Found</h5>
        <p class="text-muted small">There are no notices at this time.</p>
        <?php if ($search): ?>
            <a href="notifications.php" class="btn btn-outline-primary mt-2">View All Notices</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- View Notice Modal -->
<div class="modal fade" id="noticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p id="modalBody" class="mb-3" style="white-space:pre-wrap;line-height:1.7;"></p>
                <hr>
                <div class="d-flex flex-wrap gap-3">
                    <small class="text-muted"><i class="fas fa-user-tie me-1"></i><span id="modalBy"></span></small>
                    <small class="text-muted"><i class="fas fa-building me-1"></i><span id="modalHostel"></span></small>
                    <small class="text-muted"><i class="fas fa-clock me-1"></i><span id="modalDate"></span></small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Post Notice Modal -->
<?php if (in_array($_SESSION['role'], ['AdminOfficer', 'HouseTutor'])): ?>
<div class="modal fade" id="postNoticeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullhorn me-2"></i>Post a New Notice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../api/notice_api.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notice Title</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="e.g. Water Supply Disruption" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notice Body</label>
                        <textarea name="body" class="form-control" rows="5"
                                  placeholder="Write the full notice here..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hall / Hostel</label>
                        <select name="hostel_id" class="form-select">
                            <option value="">All Halls</option>
                            <?php
                            $hostels = $conn->query("SELECT * FROM hostels");
                            while ($h = $hostels->fetch_assoc()):
                            ?>
                                <option value="<?php echo $h['hostel_id']; ?>">
                                    <?php echo htmlspecialchars($h['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="fas fa-paper-plane me-2"></i>Post Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('noticeModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modalTitle').textContent  = btn.getAttribute('data-title');
    document.getElementById('modalBody').textContent   = btn.getAttribute('data-body');
    document.getElementById('modalBy').textContent     = btn.getAttribute('data-by') + ' (' + btn.getAttribute('data-role') + ')';
    document.getElementById('modalHostel').textContent = btn.getAttribute('data-hostel');
    document.getElementById('modalDate').textContent   = btn.getAttribute('data-date');
});

setTimeout(() => {
    document.querySelectorAll('.alert').forEach(a => bootstrap.Alert.getOrCreateInstance(a).close());
}, 4000);
</script>

<?php include "../includes/footer.php"; ?>