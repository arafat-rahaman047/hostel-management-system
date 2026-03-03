<?php
include "includes/header.php";
include "includes/db.php";

// Get hall name from URL
$hall_name = $_GET['name'] ?? '';

if (empty($hall_name)) {
    header("Location: /hostel-management-system/index.php");
    exit();
}

// Fetch hostel info
$stmt = $conn->prepare("SELECT * FROM hostels WHERE name = ?");
$stmt->bind_param("s", $hall_name);
$stmt->execute();
$hostel = $stmt->get_result()->fetch_assoc();

if (!$hostel) {
    header("Location: /hostel-management-system/index.php");
    exit();
}

$hostel_id = $hostel['hostel_id'];

// Room stats
$room_stats = $conn->prepare("
    SELECT
        COUNT(*) AS total_rooms,
        SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS available,
        SUM(CASE WHEN status = 'Occupied'  THEN 1 ELSE 0 END) AS occupied
    FROM rooms WHERE hostel_id = ?
");
$room_stats->bind_param("i", $hostel_id);
$room_stats->execute();
$stats = $room_stats->get_result()->fetch_assoc();

// Total resident students
$res_q = $conn->prepare("SELECT COUNT(*) AS cnt FROM students WHERE hostel_id = ?");
$res_q->bind_param("i", $hostel_id);
$res_q->execute();
$total_residents = $res_q->get_result()->fetch_assoc()['cnt'];

// ── Staff queries ─────────────────────────────────────────────────────────────
$provost       = $conn->query("SELECT name, email FROM users WHERE role = 'Provost' LIMIT 1")->fetch_assoc();
$admin_officer = $conn->query("SELECT name, email FROM users WHERE role = 'AdminOfficer' LIMIT 1")->fetch_assoc();
$dep_registrar = $conn->query("SELECT name, email FROM users WHERE role = 'AssistantRegistrar' LIMIT 1")->fetch_assoc();

$tutors_q = $conn->query("SELECT name, email FROM users WHERE role = 'HouseTutor' LIMIT 5");
$tutors   = [];
while ($t = $tutors_q->fetch_assoc()) $tutors[] = $t;

// Hall banner image mapping
$hall_images = [
    'Bijoy-24 Hall'             => 'assets/images/hall1.jpg',
    'Sher-E-Bangla Hall'        => 'assets/images/hall2.jpg',
    'Kabi Sufia Kamal Hall'     => 'assets/images/hall3.jpg',
    'Tapashi Rabeya Basri Hall'  => 'assets/images/hall1.jpg',
];
$banner_img = $hall_images[$hostel['name']] ?? 'assets/images/hall1.jpg';
?>

<!-- ── HERO BANNER ────────────────────────────────────────────────────────── -->
<div class="position-relative overflow-hidden" style="height:320px;">
    <img src="<?php echo $banner_img; ?>"
         class="w-100 h-100"
         style="object-fit:cover;"
         alt="<?php echo htmlspecialchars($hostel['name']); ?>">
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-end"
         style="background:linear-gradient(to top,rgba(0,30,80,.85) 40%,transparent 100%);">
        <div class="container pb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="/hostel-management-system/index.php"
                           class="text-white-50 text-decoration-none">Home</a>
                    </li>
                    <li class="breadcrumb-item text-white active">
                        <?php echo htmlspecialchars($hostel['name']); ?>
                    </li>
                </ol>
            </nav>
            <h1 class="fw-bold text-white mb-1">
                <?php echo htmlspecialchars($hostel['name']); ?>
            </h1>
            <p class="text-white-50 mb-0">
                <i class="fas fa-map-marker-alt me-1"></i>
                <?php echo htmlspecialchars($hostel['location'] ?? 'University of Barishal, Main Campus'); ?>
            </p>
        </div>
    </div>
</div>

<div class="container py-5">

    <!-- ── QUICK STATS ── -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <i class="fas fa-door-open fa-2x text-primary mb-2"></i>
                <h3 class="fw-bold mb-0 text-primary"><?php echo $stats['total_rooms'] ?? 0; ?></h3>
                <small class="text-muted">Total Rooms</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h3 class="fw-bold mb-0 text-success"><?php echo $stats['available'] ?? 0; ?></h3>
                <small class="text-muted">Available Rooms</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <i class="fas fa-users fa-2x text-warning mb-2"></i>
                <h3 class="fw-bold mb-0 text-warning"><?php echo $total_residents; ?></h3>
                <small class="text-muted">Current Residents</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3 h-100">
                <i class="fas fa-bed fa-2x text-info mb-2"></i>
                <h3 class="fw-bold mb-0 text-info">
                    <?php echo $hostel['total_rooms'] ?? $stats['total_rooms']; ?>
                </h3>
                <small class="text-muted">Capacity</small>
            </div>
        </div>
    </div>

    <div class="row g-4">

        <!-- ── LEFT: Hall Info + Facilities ── -->
        <div class="col-lg-4">

            <!-- About -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3" style="background:#003366;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-info-circle me-2"></i>About This Hall
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-borderless table-sm mb-0 px-3">
                        <tbody>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-3 py-2" style="width:45%">Hall Name</td>
                                <td class="py-2"><?php echo htmlspecialchars($hostel['name']); ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-3 py-2">Location</td>
                                <td class="py-2"><?php echo htmlspecialchars($hostel['location'] ?? 'Main Campus'); ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-3 py-2">Total Rooms</td>
                                <td class="py-2"><?php echo $stats['total_rooms'] ?? 0; ?></td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-3 py-2">Available</td>
                                <td class="py-2">
                                    <span class="badge bg-success"><?php echo $stats['available'] ?? 0; ?> rooms</span>
                                </td>
                            </tr>
                            <tr class="border-bottom">
                                <td class="text-muted fw-semibold ps-3 py-2">Occupied</td>
                                <td class="py-2">
                                    <span class="badge bg-secondary"><?php echo $stats['occupied'] ?? 0; ?> rooms</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold ps-3 py-2">Residents</td>
                                <td class="py-2"><?php echo $total_residents; ?> students</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Facilities -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-star me-2"></i>Facilities
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php
                        $facilities = [
                            ['fas fa-wifi',           'text-primary',   'High-Speed Wi-Fi'],
                            ['fas fa-tint',           'text-info',      '24/7 Water Supply'],
                            ['fas fa-bolt',           'text-warning',   'Electricity Backup'],
                            ['fas fa-shield-alt',     'text-success',   '24-Hour Security'],
                            ['fas fa-utensils',       'text-danger',    'Canteen / Dining Hall'],
                            ['fas fa-book-open',      'text-primary',   'Reading / Study Room'],
                            ['fas fa-pray',           'text-success',   'Prayer Room'],
                            ['fas fa-medkit',         'text-danger',    'First Aid Room'],
                            ['fas fa-broom',          'text-secondary', 'Housekeeping Service'],
                            ['fas fa-parking',        'text-dark',      'Parking Area'],
                        ];
                        foreach ($facilities as [$icon, $color, $label]):
                        ?>
                            <li class="d-flex align-items-center mb-2">
                                <i class="<?php echo $icon; ?> <?php echo $color; ?> me-2"
                                   style="width:18px;"></i>
                                <span class="small"><?php echo $label; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

        </div>

        <!-- ── RIGHT: Staff + Rules ── -->
        <div class="col-lg-8">

            <!-- Provost -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3" style="background:#003366;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-user-tie me-2"></i>Provost
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($provost): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                                        justify-content-center flex-shrink-0"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-user-tie fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($provost['name']); ?></h5>
                                <span class="badge bg-primary mb-1">Provost</span>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($provost['email']); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Position currently vacant.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deputy Registrar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3" style="background:#1a5276;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-user-shield me-2"></i>Deputy Registrar
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($dep_registrar): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center
                                        justify-content-center flex-shrink-0"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-user-shield fa-2x text-info"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($dep_registrar['name']); ?></h5>
                                <span class="badge bg-info text-dark mb-1">Assistant Registrar</span>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($dep_registrar['email']); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Position currently vacant.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Administrative Officer -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3" style="background:#1e8449;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-user-cog me-2"></i>Administrative Officer
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($admin_officer): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center
                                        justify-content-center flex-shrink-0"
                                 style="width:64px;height:64px;">
                                <i class="fas fa-user-cog fa-2x text-success"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($admin_officer['name']); ?></h5>
                                <span class="badge bg-success mb-1">Administrative Officer</span>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-envelope me-1"></i>
                                    <?php echo htmlspecialchars($admin_officer['email']); ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Position currently vacant.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- House Tutors (up to 5) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3" style="background:#6c3483;">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="fas fa-chalkboard-teacher me-2"></i>House Tutors
                        <span class="badge bg-white text-dark ms-2"><?php echo count($tutors); ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($tutors)): ?>
                        <div class="row g-3">
                            <?php foreach ($tutors as $i => $tutor): ?>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-3 rounded"
                                         style="background:#f8f0ff; border-left:4px solid #6c3483;">
                                        <div class="rounded-circle bg-white d-flex align-items-center
                                                    justify-content-center flex-shrink-0 shadow-sm"
                                             style="width:48px;height:48px;">
                                            <i class="fas fa-chalkboard-teacher" style="color:#6c3483;"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0">
                                                <?php echo htmlspecialchars($tutor['name']); ?>
                                            </h6>
                                            <span class="badge bg-light text-dark border small">
                                                House Tutor <?php echo $i + 1; ?>
                                            </span>
                                            <p class="text-muted small mb-0 mt-1">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($tutor['email']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No house tutors assigned yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rules & Regulations -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-gavel me-2"></i>Hall Rules &amp; Regulations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <?php
                        $rules = [
                            'Residents must carry their ID card at all times.',
                            'Visitors are allowed only in designated areas.',
                            'Quiet hours: 11:00 PM – 6:00 AM.',
                            'No cooking in rooms — use common kitchen only.',
                            'Ragging and misconduct are strictly prohibited.',
                            'Gate closes at 10:00 PM. Prior permission required for late entry.',
                            'Hall fees must be paid by the 10th of each month.',
                            'Residents are responsible for cleanliness of their rooms.',
                        ];
                        foreach ($rules as $i => $rule):
                        ?>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 small">
                                    <span class="badge bg-warning text-dark flex-shrink-0 mt-1">
                                        <?php echo $i + 1; ?>
                                    </span>
                                    <span><?php echo $rule; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->
    </div><!-- /row -->

    <!-- ── APPLY CTA ── -->
    <div class="text-center mt-5 pt-3 border-top">
        <p class="text-muted mb-3">Interested in staying at this hall?</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/hostel-management-system/templates/register.php"
               class="btn btn-primary btn-lg px-5 shadow me-2">
                <i class="fas fa-file-alt me-2"></i>Apply for a Room
            </a>
            <a href="/hostel-management-system/templates/login.php"
               class="btn btn-outline-primary btn-lg px-5">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
        <?php else: ?>
            <a href="/hostel-management-system/modules/booking.php"
               class="btn btn-primary btn-lg px-5 shadow">
                <i class="fas fa-bed me-2"></i>Apply for a Room in This Hall
            </a>
        <?php endif; ?>
        <div class="mt-3">
            <a href="/hostel-management-system/index.php"
               class="text-muted text-decoration-none small">
                <i class="fas fa-arrow-left me-1"></i>Back to all halls
            </a>
        </div>
    </div>

</div>

<?php include "includes/footer.php"; ?>