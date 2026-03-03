<?php
/**
 * profile_api.php
 * Place at: /hostel-management-system/api/profile_api.php
 */
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /hostel-management-system/templates/login.php?error=unauthorized");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /hostel-management-system/modules/profile.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'];

// ── Sanitise common fields ─────────────────────────────────────────────────
$name              = trim($_POST['name']              ?? '');
$phone             = trim($_POST['phone']             ?? '') ?: null;
$date_of_birth     = trim($_POST['date_of_birth']     ?? '') ?: null;
$blood_group       = trim($_POST['blood_group']       ?? '') ?: null;
$gender            = trim($_POST['gender']            ?? '') ?: null;
$permanent_address = trim($_POST['permanent_address'] ?? '') ?: null;
$bio               = trim($_POST['bio']               ?? '') ?: null;

// ── Staff-only fields ──────────────────────────────────────────────────────
$designation        = trim($_POST['designation']        ?? '') ?: null;
$employee_id        = trim($_POST['employee_id']        ?? '') ?: null;
$assigned_hostel_id = !empty($_POST['assigned_hostel_id']) ? (int)$_POST['assigned_hostel_id'] : null;
$office_room        = trim($_POST['office_room']        ?? '') ?: null;
$join_date          = trim($_POST['join_date']          ?? '') ?: null;
$nid_number         = trim($_POST['nid_number']         ?? '') ?: null;

// ── Student-only extra fields ──────────────────────────────────────────────
$department      = trim($_POST['department']      ?? '') ?: null;
$student_reg_id  = trim($_POST['student_reg_id']  ?? '') ?: null;
$year            = !empty($_POST['year']) ? (int)$_POST['year'] : null;
$father_name     = trim($_POST['father_name']     ?? '') ?: null;
$father_contact  = trim($_POST['father_contact']  ?? '') ?: null;
$mother_name     = trim($_POST['mother_name']     ?? '') ?: null;
$mother_contact  = trim($_POST['mother_contact']  ?? '') ?: null;
$reason_for_stay = trim($_POST['reason_for_stay'] ?? '') ?: null;

// ── Profile photo upload ───────────────────────────────────────────────────
$photo_filename = null;
if (!empty($_FILES['profile_photo']['name'])) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/hostel-management-system/assets/uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $ext     = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $maxsize = 2 * 1024 * 1024;

    if (!in_array($ext, $allowed)) {
        header("Location: /hostel-management-system/modules/profile.php?error=Invalid+photo+type.+Use+JPG+or+PNG.");
        exit();
    }
    if ($_FILES['profile_photo']['size'] > $maxsize) {
        header("Location: /hostel-management-system/modules/profile.php?error=Photo+must+be+under+2MB.");
        exit();
    }
    $photo_filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $photo_filename);
}

// ── DB transaction ─────────────────────────────────────────────────────────
$conn->begin_transaction();
try {

    // 1. Update name in users table
    if ($name !== '') {
        $s = $conn->prepare("UPDATE users SET name = ? WHERE user_id = ?");
        $s->bind_param("si", $name, $user_id);
        $s->execute();
        $_SESSION['name'] = $name;
    }

    // 2. Check existing profile row
    $chk = $conn->prepare("SELECT profile_id, profile_photo FROM user_profiles WHERE user_id = ?");
    $chk->bind_param("i", $user_id);
    $chk->execute();
    $existing = $chk->get_result()->fetch_assoc();

    // Keep old photo if no new one uploaded
    $final_photo = $photo_filename ?? ($existing['profile_photo'] ?? null);

    if ($existing) {
        // UPDATE existing row
        // params: phone,dob,blood,gender,addr,bio,desig,emp_id = 8s
        //         hostel_id = i, office,join,nid,photo = 4s, user_id = i  → total 14
        $s = $conn->prepare("
            UPDATE user_profiles SET
                phone=?, date_of_birth=?, blood_group=?, gender=?,
                permanent_address=?, bio=?,
                designation=?, employee_id=?, assigned_hostel_id=?,
                office_room=?, join_date=?, nid_number=?,
                profile_photo=?
            WHERE user_id=?
        ");
        $s->bind_param(
            "ssssssssissssi",
            $phone, $date_of_birth, $blood_group, $gender,
            $permanent_address, $bio,
            $designation, $employee_id, $assigned_hostel_id,
            $office_room, $join_date, $nid_number,
            $final_photo, $user_id
        );
    } else {
        // INSERT new row
        $s = $conn->prepare("
            INSERT INTO user_profiles
                (user_id, phone, date_of_birth, blood_group, gender,
                 permanent_address, bio,
                 designation, employee_id, assigned_hostel_id,
                 office_room, join_date, nid_number, profile_photo)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $s->bind_param(
            "issssssssissss",
            $user_id,
            $phone, $date_of_birth, $blood_group, $gender,
            $permanent_address, $bio,
            $designation, $employee_id, $assigned_hostel_id,
            $office_room, $join_date, $nid_number, $final_photo
        );
    }
    $s->execute();

    // 3. For Student: also update students table
    if ($role === 'Student') {
        $s = $conn->prepare("
            UPDATE students SET
                contact_no=?, blood_group=?,
                department=?, student_reg_id=?, year=?,
                father_name=?, father_contact=?,
                mother_name=?, mother_contact=?,
                permanent_address=?, reason_for_stay=?
            WHERE user_id=?
        ");
        $s->bind_param(
            "ssssissssssi",
            $phone, $blood_group,
            $department, $student_reg_id, $year,
            $father_name, $father_contact,
            $mother_name, $mother_contact,
            $permanent_address, $reason_for_stay,
            $user_id
        );
        $s->execute();
    }

    $conn->commit();
    header("Location: /hostel-management-system/modules/profile.php?saved=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $err = urlencode('Save failed: ' . $e->getMessage());
    header("Location: /hostel-management-system/modules/profile.php?error=$err");
    exit();
}
