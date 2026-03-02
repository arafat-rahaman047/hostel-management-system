<?php
require_once "../includes/auth.php";
checkAccess(['Student', 'Provost', 'AdminOfficer', 'AssistantRegistrar', 'HouseTutor']);
include "../includes/header.php";
include "../includes/db.php";

// Fetch notices with hostel name, poster name and role
$notices = $conn->query("
    SELECT 
        n.notice_id,
        n.title,
        n.body,
        n.priority,
        n.created_at,
        u.name AS posted_by,
        u.role AS poster_role,
        h.name AS hostel_name
    FROM notices n
    JOIN users u ON n.posted_by = u.user_id
    LEFT JOIN hostels h ON n.hostel_id = h.hostel_id
    ORDER BY n.created_at DESC
");
?>

<div class="container py-5">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">
                <i class="fas fa-bullhorn text-primary me-2"></i> Hall Notices
            </h3>
            <p class="text-muted small mb-0">Latest announcements from your Hall of Residence</p>
        </div>

        <!-- Post Notice Button: visible only to staff roles -->
        <?php if (in_array($_SESSION['role'], ['Provost', 'AdminOfficer', 'HouseTutor'])): ?>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#postNoticeModal">
                <i class="fas fa-plus me-2"></i> Post Notice
            </button>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 bg-light"
                            placeholder="Search notices..."
                            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select bg-light">
                        <option value="">All Priorities</option>
                        <option value="High" <?php echo (($_GET['priority'] ?? '') === 'High') ? 'selected' : ''; ?>>🔴 High</option>
                        <option value="Medium" <?php echo (($_GET['priority'] ?? '') === 'Medium') ? 'selected' : ''; ?>>🟡 Medium</option>
                        <option value="Low" <?php echo (($_GET['priority'] ?? '') === 'Low') ? 'selected' : ''; ?>>🟢 Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
                <?php if (!empty($_GET['search']) || !empty($_GET['priority'])): ?>
                    <div class="col-md-2">
                        <a href="notifications.php" class="btn btn-outline-secondary w-100">Clear</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Notices List -->
    <?php
    // Re-run query with filters if provided
    $search = $conn->real_escape_string($_GET['search'] ?? '');
    $priority_filter = $conn->real_escape_string($_GET['priority'] ?? '');

    $where = "WHERE 1=1";
    if ($search)         $where .= " AND (n.title LIKE '%$search%' OR n.body LIKE '%$search%')";
    if ($priority_filter) $where .= " AND n.priority = '$priority_filter'";

    $notices = $conn->query("
        SELECT 
            n.notice_id,
            n.title,
            n.body,
            n.priority,
            n.created_at,
            u.name AS posted_by,
            u.role AS poster_role,
            h.name AS hostel_name
        FROM notices n
        JOIN users u ON n.posted_by = u.user_id
        LEFT JOIN hostels h ON n.hostel_id = h.hostel_id
        $where
        ORDER BY 
            FIELD(n.priority, 'High', 'Medium', 'Low'),
            n.created_at DESC
    ");
    ?>

    <?php if ($notices && $notices->num_rows > 0): ?>
        <div class="row g-3">
            <?php while ($notice = $notices->fetch_assoc()):
                $priorityConfig = [
                    'High'   => ['badge' => 'danger',  'icon' => 'fa-exclamation-circle', 'border' => 'border-danger'],
                    'Medium' => ['badge' => 'warning',  'icon' => 'fa-exclamation-triangle', 'border' => 'border-warning'],
                    'Low'    => ['badge' => 'success',  'icon' => 'fa-info-circle', 'border' => 'border-success'],
                ];
                $p = $priorityConfig[$notice['priority']] ?? $priorityConfig['Low'];
                $posted_at = date('d M Y, h:i A', strtotime($notice['created_at']));
                $role_label = str_replace(['AdminOfficer', 'AssistantRegistrar', 'HouseTutor'], ['Admin Officer', 'Asst. Registrar', 'House Tutor'], $notice['poster_role']);
            ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm border-start border-4 <?php echo $p['border']; ?> notice-card">
                        <div class="card-body py-3 px-4">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="notice-icon rounded-circle bg-<?php echo $p['badge']; ?> bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width:46px; height:46px;">
                                        <i class="fas <?php echo $p['icon']; ?> text-<?php echo $p['badge']; ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($notice['title']); ?></h5>
                                        <p class="text-muted mb-2" style="line-height:1.6;">
                                            <?php echo nl2br(htmlspecialchars($notice['body'])); ?>
                                        </p>
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
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo $posted_at; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-<?php echo $p['badge']; ?> bg-opacity-90 align-self-start px-3 py-2">
                                    <?php echo $notice['priority']; ?> Priority
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-4"
                style="width:100px; height:100px;">
                <i class="fas fa-bell-slash fa-3x text-muted opacity-50"></i>
            </div>
            <h5 class="text-muted fw-bold">No Notices Found</h5>
            <p class="text-muted small">There are no notices matching your criteria at this time.<br>Check back later for updates from your hall.</p>
            <?php if (!empty($_GET['search']) || !empty($_GET['priority'])): ?>
                <a href="notifications.php" class="btn btn-outline-primary mt-2">View All Notices</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Post Notice Modal (Staff only) -->
<?php if (in_array($_SESSION['role'], ['Provost', 'AdminOfficer', 'HouseTutor'])): ?>
    <div class="modal fade" id="postNoticeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-bullhorn me-2"></i>Post a New Notice
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/notice_api.php" method="POST">
                    <div class="modal-body p-4">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notice Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Water Supply Disruption" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notice Body</label>
                            <textarea name="body" class="form-control" rows="4"
                                placeholder="Write the full notice here..." required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="Low">🟢 Low</option>
                                    <option value="Medium" selected>🟡 Medium</option>
                                    <option value="High">🔴 High</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Hall / Hostel</label>
                                <select name="hostel_id" class="form-select">
                                    <option value="">All Halls</option>
                                    <?php
                                    $hostels = $conn->query("SELECT * FROM Hostels");
                                    while ($h = $hostels->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $h['hostel_id']; ?>">
                                            <?php echo htmlspecialchars($h['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
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

<style>
    .notice-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .notice-card:hover {
        transform: translateX(4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<?php include "../includes/footer.php"; ?>