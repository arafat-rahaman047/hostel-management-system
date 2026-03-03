<?php
/**
 * profile.php  —  Universal Profile Page for ALL roles
 * Place at: /hostel-management-system/modules/profile.php
 */
require_once "../includes/auth.php";
checkAccess();
include "../includes/db.php";
include "../includes/header.php";

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

$u = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$u->bind_param("i", $user_id);
$u->execute();
$user = $u->get_result()->fetch_assoc();

$p = $conn->prepare("SELECT up.*, h.name AS assigned_hostel_name FROM user_profiles up LEFT JOIN hostels h ON up.assigned_hostel_id = h.hostel_id WHERE up.user_id = ?");
$p->bind_param("i", $user_id);
$p->execute();
$profile = $p->get_result()->fetch_assoc();

$student = null;
if ($role === 'Student') {
    $sq = $conn->prepare("SELECT s.*, h.name AS hostel_name, r.floor FROM students s LEFT JOIN hostels h ON s.hostel_id = h.hostel_id LEFT JOIN rooms r ON s.room_id = r.room_id WHERE s.user_id = ?");
    $sq->bind_param("i", $user_id);
    $sq->execute();
    $student = $sq->get_result()->fetch_assoc();
}

$stats = [];
if ($role === 'Student' && $student) {
    $sid = (int)$student['student_id'];
    $stats = [
        ['label' => 'Bookings',   'val' => (int)$conn->query("SELECT COUNT(*) c FROM bookings   WHERE student_id=$sid")->fetch_assoc()['c'], 'color' => 'primary'],
        ['label' => 'Payments',   'val' => (int)$conn->query("SELECT COUNT(*) c FROM payments   WHERE student_id=$sid")->fetch_assoc()['c'], 'color' => 'success'],
        ['label' => 'Complaints', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM complaints WHERE student_id=$sid")->fetch_assoc()['c'], 'color' => 'warning'],
    ];
}
if ($role === 'HouseTutor') {
    $stats = [
        ['label' => 'Assigned',    'val' => (int)$conn->query("SELECT COUNT(*) c FROM complaints WHERE tutor_id=$user_id")->fetch_assoc()['c'],                         'color' => 'primary'],
        ['label' => 'In Progress', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM complaints WHERE tutor_id=$user_id AND status='InProgress'")->fetch_assoc()['c'], 'color' => 'warning'],
        ['label' => 'Resolved',    'val' => (int)$conn->query("SELECT COUNT(*) c FROM complaints WHERE tutor_id=$user_id AND status='Resolved'")->fetch_assoc()['c'],   'color' => 'success'],
    ];
}
if ($role === 'AdminOfficer') {
    $stats = [
        ['label' => 'Payments Verified', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM payments WHERE verified_by=$user_id")->fetch_assoc()['c'], 'color' => 'success'],
        ['label' => 'Notices Posted',    'val' => (int)$conn->query("SELECT COUNT(*) c FROM notices  WHERE posted_by=$user_id")->fetch_assoc()['c'],   'color' => 'info'],
    ];
}
if ($role === 'Provost') {
    $stats = [
        ['label' => 'Pending',  'val' => (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Pending'")->fetch_assoc()['c'],  'color' => 'warning'],
        ['label' => 'Approved', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Approved'")->fetch_assoc()['c'], 'color' => 'success'],
        ['label' => 'Rejected', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Rejected'")->fetch_assoc()['c'], 'color' => 'danger'],
    ];
}
if ($role === 'AssistantRegistrar') {
    $stats = [
        ['label' => 'Total Students', 'val' => (int)$conn->query("SELECT COUNT(*) c FROM users WHERE role='Student'")->fetch_assoc()['c'], 'color' => 'primary'],
        ['label' => 'Total Hostels',  'val' => (int)$conn->query("SELECT COUNT(*) c FROM hostels")->fetch_assoc()['c'],                   'color' => 'info'],
    ];
}

$hostels = $conn->query("SELECT hostel_id, name FROM hostels ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$photo_url = '/hostel-management-system/assets/images/default_avatar.png';
if (!empty($profile['profile_photo'])) {
    $photo_url = '/hostel-management-system/assets/uploads/profiles/' . htmlspecialchars($profile['profile_photo']);
}

function calcCompleteness($profile, $role, $student): int {
    $checks = [
        !empty($profile['phone']),
        !empty($profile['date_of_birth']),
        !empty($profile['blood_group']),
        !empty($profile['gender']),
        !empty($profile['permanent_address']),
        !empty($profile['profile_photo']),
    ];
    if ($role === 'Student') {
        $checks[] = !empty($student['student_reg_id']);
        $checks[] = !empty($student['department']);
        $checks[] = !empty($student['year']);
        $checks[] = !empty($student['father_name']);
        $checks[] = !empty($student['mother_name']);
    } else {
        $checks[] = !empty($profile['designation']);
        $checks[] = !empty($profile['employee_id']);
        $checks[] = !empty($profile['join_date']);
    }
    $filled = count(array_filter($checks));
    return (int) round($filled / count($checks) * 100);
}
$completeness = calcCompleteness($profile, $role, $student);
$profileEmpty = empty($profile);
$saved        = isset($_GET['saved']);
$error        = $_GET['error'] ?? '';

$roleLabels = [
    'Student'            => 'Student',
    'Provost'            => 'Provost',
    'AdminOfficer'       => 'Admin Officer',
    'HouseTutor'         => 'House Tutor',
    'AssistantRegistrar' => 'Assistant Registrar',
];
?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-1">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="../templates/dashboard/<?php echo strtolower($role); ?>.php" class="text-decoration-none">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">My Profile</li>
        </ol>
    </nav>

    <!-- Page title -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-id-card text-primary me-2"></i>My Profile</h4>
            <p class="text-muted small mb-0"><?php echo $roleLabels[$role] ?? $role; ?> — <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <button class="btn btn-primary" id="editToggleBtn" onclick="toggleMode()">
            <i class="fas fa-pen me-2" id="editBtnIcon"></i><span id="editBtnText">Edit Profile</span>
        </button>
    </div>

    <!-- Alerts -->
    <?php if ($saved): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><strong>Profile saved successfully!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($profileEmpty): ?>
    <div class="alert alert-warning" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Profile incomplete.</strong> Please fill in your details below and click <strong>Save Profile</strong>.
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars(urldecode($error)); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- STAT CARDS -->
    <?php if (!empty($stats)): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
        <div class="col-6 col-md-3">
            <div class="card text-center border py-3" style="border-left: 3px solid var(--bs-<?php echo $s['color']; ?>) !important;">
                <div class="text-muted small mb-1"><?php echo $s['label']; ?></div>
                <div class="fs-4 fw-bold text-<?php echo $s['color']; ?>"><?php echo $s['val']; ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>


    <!-- VIEW MODE -->
    <div id="viewMode" <?php echo $profileEmpty ? 'style="display:none;"' : ''; ?>>
        <div class="row g-4">

            <div class="col-12">

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center gap-3">
                        <img src="<?php echo $photo_url; ?>" alt="Profile Photo"
                             class="rounded-circle"
                             style="width:48px;height:48px;object-fit:cover;border:2px solid #dee2e6;">
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($user['name']); ?></h6>
                            <small class="text-muted"><?php echo $roleLabels[$role] ?? $role; ?>
                                <?php if ($role === 'Student' && !empty($student['student_reg_id'])): ?>
                                · <?php echo htmlspecialchars($student['student_reg_id']); ?>
                                <?php endif; ?>
                                <?php if (!empty($profile['designation'])): ?>
                                · <?php echo htmlspecialchars($profile['designation']); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $phone_val = $profile['phone'] ?? ($student['contact_no'] ?? null);
                            $bg_val    = $profile['blood_group'] ?? ($student['blood_group'] ?? null);
                            $addr_val  = $profile['permanent_address'] ?? ($student['permanent_address'] ?? null);
                            $personal  = [
                                ['Phone',         $phone_val, 'fa-phone'],
                                ['Date of Birth', !empty($profile['date_of_birth']) ? date('d M Y', strtotime($profile['date_of_birth'])) : null, 'fa-calendar'],
                                ['Blood Group',   $bg_val,    'fa-tint'],
                                ['Gender',        $profile['gender'] ?? null, 'fa-venus-mars'],
                                ['Address',       $addr_val,  'fa-map-marker-alt'],
                            ];
                            foreach ($personal as [$lbl, $val, $ico]):
                            ?>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0"><i class="fas <?php echo $ico; ?> me-1"></i><?php echo $lbl; ?></p>
                                <p class="fw-semibold mb-2"><?php echo $val ? htmlspecialchars((string)$val) : '<span class="text-muted fst-italic small">Not provided</span>'; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'Student'): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-graduation-cap me-2 text-primary"></i>Academic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $academic = [
                                ['Reg. ID',        $student['student_reg_id']  ?? null, 'fa-id-badge'],
                                ['Department',     $student['department']       ?? null, 'fa-university'],
                                ['Batch Year',     $student['year']             ?? null, 'fa-calendar-alt'],
                                ['Hostel',         $student['hostel_name']      ?? null, 'fa-hotel'],
                                ['Room',           !empty($student['room_id']) ? 'Room #'.$student['room_id'].' (Floor '.($student['floor']??'?').')' : null, 'fa-bed'],
                                ['Reason for Stay',$student['reason_for_stay']  ?? null, 'fa-comment'],
                            ];
                            foreach ($academic as [$lbl, $val, $ico]):
                            ?>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0"><i class="fas <?php echo $ico; ?> me-1"></i><?php echo $lbl; ?></p>
                                <p class="fw-semibold mb-2"><?php echo $val ? htmlspecialchars((string)$val) : '<span class="text-muted fst-italic small">Not provided</span>'; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Guardian Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $guardian = [
                                ["Father's Name",    $student['father_name']    ?? null, 'fa-male'],
                                ["Father's Contact", $student['father_contact'] ?? null, 'fa-phone'],
                                ["Mother's Name",    $student['mother_name']    ?? null, 'fa-female'],
                                ["Mother's Contact", $student['mother_contact'] ?? null, 'fa-phone'],
                            ];
                            foreach ($guardian as [$lbl, $val, $ico]):
                            ?>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0"><i class="fas <?php echo $ico; ?> me-1"></i><?php echo $lbl; ?></p>
                                <p class="fw-semibold mb-2"><?php echo $val ? htmlspecialchars((string)$val) : '<span class="text-muted fst-italic small">Not provided</span>'; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase me-2 text-primary"></i>Official Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <?php
                            $official = [
                                ['Designation',     $profile['designation']          ?? null, 'fa-id-card'],
                                ['Employee ID',     $profile['employee_id']          ?? null, 'fa-barcode'],
                                ['Assigned Hostel', $profile['assigned_hostel_name'] ?? null, 'fa-hotel'],
                                ['Office Room',     $profile['office_room']          ?? null, 'fa-door-open'],
                                ['Join Date',       !empty($profile['join_date']) ? date('d M Y', strtotime($profile['join_date'])) : null, 'fa-calendar-check'],
                                ['NID Number',      $profile['nid_number']           ?? null, 'fa-address-card'],
                            ];
                            foreach ($official as [$lbl, $val, $ico]):
                            ?>
                            <div class="col-sm-6">
                                <p class="text-muted small mb-0"><i class="fas <?php echo $ico; ?> me-1"></i><?php echo $lbl; ?></p>
                                <p class="fw-semibold mb-2"><?php echo $val ? htmlspecialchars((string)$val) : '<span class="text-muted fst-italic small">Not provided</span>'; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- /col-12 -->
        </div><!-- /row -->
    </div><!-- /viewMode -->


    <!-- EDIT MODE -->
    <div id="editMode" <?php echo $profileEmpty ? '' : 'style="display:none;"'; ?>>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-pen me-2 text-primary"></i>Edit Profile</h5>
            </div>
            <div class="card-body">

                <form action="/hostel-management-system/api/profile_api.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Profile Photo</label><br>
                        <img src="<?php echo $photo_url; ?>" alt="Current Photo"
                             class="rounded-circle me-2"
                             style="width:48px;height:48px;object-fit:cover;border:2px solid #dee2e6;">
                        <input type="file" name="profile_photo" class="form-control form-control-sm w-auto d-inline-block"
                               accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted ms-1">JPG or PNG, max 2MB. Leave blank to keep current.</small>
                    </div>

                    <ul class="nav nav-tabs mb-4" id="profileTabs">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" onclick="switchTab('personal',this)">
                                <i class="fas fa-user me-1"></i>Personal
                            </button>
                        </li>
                        <?php if ($role === 'Student'): ?>
                        <li class="nav-item">
                            <button type="button" class="nav-link" onclick="switchTab('academic',this)">
                                <i class="fas fa-graduation-cap me-1"></i>Academic
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" onclick="switchTab('guardian',this)">
                                <i class="fas fa-users me-1"></i>Guardian
                            </button>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <button type="button" class="nav-link" onclick="switchTab('official',this)">
                                <i class="fas fa-briefcase me-1"></i>Official
                            </button>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Personal Tab -->
                    <div id="tab-personal">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="text" name="phone" class="form-control" placeholder="01XXXXXXXXX"
                                       value="<?php echo htmlspecialchars($profile['phone'] ?? $student['contact_no'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Blood Group</label>
                                <select name="blood_group" class="form-select">
                                    <option value="">-- Select --</option>
                                    <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg):
                                        $sel = ($profile['blood_group'] ?? $student['blood_group'] ?? '') === $bg ? 'selected' : ''; ?>
                                    <option value="<?php echo $bg; ?>" <?php echo $sel; ?>><?php echo $bg; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">-- Select --</option>
                                    <?php foreach (['Male','Female','Other'] as $g):
                                        $sel = ($profile['gender'] ?? '') === $g ? 'selected' : ''; ?>
                                    <option value="<?php echo $g; ?>" <?php echo $sel; ?>><?php echo $g; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea name="permanent_address" class="form-control" rows="2"
                                          placeholder="Village, Upazila, District"><?php echo htmlspecialchars($profile['permanent_address'] ?? $student['permanent_address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Short Bio</label>
                                <textarea name="bio" class="form-control" rows="2"
                                          placeholder="A few words about yourself..."><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php if ($role === 'Student'): ?>

                    <!-- Academic Tab -->
                    <div id="tab-academic" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Student Registration ID</label>
                                <input type="text" name="student_reg_id" class="form-control" placeholder="e.g. 22CSE047"
                                       value="<?php echo htmlspecialchars($student['student_reg_id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Department</label>
                                <input type="text" name="department" class="form-control"
                                       placeholder="e.g. Computer Science and Engineering"
                                       value="<?php echo htmlspecialchars($student['department'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Batch / Academic Year</label>
                                <input type="number" name="year" class="form-control" placeholder="e.g. 2022"
                                       min="2000" max="2099"
                                       value="<?php echo htmlspecialchars($student['year'] ?? ''); ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Reason for Staying in Hostel</label>
                                <input type="text" name="reason_for_stay" class="form-control"
                                       placeholder="e.g. Financial Inability, Distance from Home"
                                       value="<?php echo htmlspecialchars($student['reason_for_stay'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Tab -->
                    <div id="tab-guardian" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Father's Full Name</label>
                                <input type="text" name="father_name" class="form-control"
                                       value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Father's Contact Number</label>
                                <input type="text" name="father_contact" class="form-control"
                                       value="<?php echo htmlspecialchars($student['father_contact'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mother's Full Name</label>
                                <input type="text" name="mother_name" class="form-control"
                                       value="<?php echo htmlspecialchars($student['mother_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mother's Contact Number</label>
                                <input type="text" name="mother_contact" class="form-control"
                                       value="<?php echo htmlspecialchars($student['mother_contact'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <?php else: ?>

                    <!-- Official Tab -->
                    <div id="tab-official" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Designation / Title</label>
                                <input type="text" name="designation" class="form-control"
                                       placeholder="e.g. House Tutor, Admin Officer"
                                       value="<?php echo htmlspecialchars($profile['designation'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control"
                                       value="<?php echo htmlspecialchars($profile['employee_id'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Assigned Hostel</label>
                                <select name="assigned_hostel_id" class="form-select">
                                    <option value="">-- All Hostels / Not Assigned --</option>
                                    <?php foreach ($hostels as $h):
                                        $sel = ($profile['assigned_hostel_id'] ?? '') == $h['hostel_id'] ? 'selected' : ''; ?>
                                    <option value="<?php echo $h['hostel_id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($h['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Office Room</label>
                                <input type="text" name="office_room" class="form-control" placeholder="e.g. Room 101"
                                       value="<?php echo htmlspecialchars($profile['office_room'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Join Date</label>
                                <input type="date" name="join_date" class="form-control"
                                       value="<?php echo htmlspecialchars($profile['join_date'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NID Number</label>
                                <input type="text" name="nid_number" class="form-control"
                                       value="<?php echo htmlspecialchars($profile['nid_number'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <?php endif; ?>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Profile
                        </button>
                        <?php if (!$profileEmpty): ?>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleMode()">Cancel</button>
                        <?php endif; ?>
                    </div>

                </form>
            </div>
        </div>
    </div><!-- /editMode -->

</div><!-- /container -->

<script>
function toggleMode() {
    const view    = document.getElementById('viewMode');
    const edit    = document.getElementById('editMode');
    const btnText = document.getElementById('editBtnText');
    const btnIcon = document.getElementById('editBtnIcon');
    const editing = edit.style.display !== 'none';
    edit.style.display = editing ? 'none' : 'block';
    view.style.display = editing ? 'block' : 'none';
    btnText.textContent = editing ? 'Edit Profile' : 'Cancel';
    btnIcon.className   = editing ? 'fas fa-pen me-2' : 'fas fa-times me-2';
    if (!editing) edit.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function switchTab(name, btn) {
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
    document.querySelectorAll('#profileTabs .nav-link').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).style.display = 'block';
    btn.classList.add('active');
}

</script>

<?php include "../includes/footer.php"; ?>