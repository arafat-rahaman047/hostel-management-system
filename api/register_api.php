<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../templates/register.php");
    exit();
}

$name     = trim($_POST['name']);
$email    = trim($_POST['email']);
$password = $_POST['password'];
$role     = $_POST['role'] ?? '';

// ── Security: only Students may self-register ─────────────────────────────
// Staff accounts (Provost, AdminOfficer, etc.) must be created by an Admin.
if ($role !== 'Student') {
    header("Location: ../templates/register.php?error=Self-registration+is+only+available+for+Students.");
    exit();
}

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../templates/register.php?error=Invalid+email+address.");
    exit();
}

if (strlen($password) < 6) {
    header("Location: ../templates/register.php?error=Password+must+be+at+least+6+characters.");
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
        throw new Exception("This email is already registered.");
    }

    // Insert into users
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
    $stmt->execute();
    $user_id = $conn->insert_id;

    // Create matching students record
    $stmtStudent = $conn->prepare("INSERT INTO students (user_id) VALUES (?)");
    $stmtStudent->bind_param("i", $user_id);
    $stmtStudent->execute();

    $conn->commit();
    header("Location: ../templates/login.php?success=registered");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../templates/register.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>