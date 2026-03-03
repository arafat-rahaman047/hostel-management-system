<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include('../includes/db.php');
require_once('../includes/auth.php');

// Only AdminOfficer can create staff accounts
if ($_SESSION['role'] !== 'AdminOfficer') {
    die("Unauthorized Access");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /hostel-management-system/templates/dashboard/admin.php");
    exit();
}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = trim($_POST['password']);
$role     = $_POST['role'];

// Allowed staff roles only — Students must self-register
$allowed_roles = ['Provost', 'AdminOfficer', 'AssistantRegistrar', 'HouseTutor'];

if (!in_array($role, $allowed_roles)) {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error&detail=invalid_role");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error&detail=invalid_email");
    exit();
}

if (strlen($password) < 6) {
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error&detail=short_password");
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

$conn->begin_transaction();

try {
    // Duplicate email check
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        throw new Exception("Email already registered.");
    }

    // Insert staff user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
    $stmt->execute();
    $user_id = $conn->insert_id;

    // Log the action
    $log = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $action = "Created staff account: $name ($role)";
    $log->bind_param("is", $_SESSION['user_id'], $action);
    $log->execute();

    $conn->commit();
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=staff_created");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: /hostel-management-system/templates/dashboard/admin.php?msg=error&detail=" . urlencode($e->getMessage()));
    exit();
}
?>