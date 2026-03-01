<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../templates/register.php?error=invalid_email");
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    $conn->begin_transaction();

    try {
        $checkEmail = $conn->prepare("SELECT user_id FROM Users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            throw new Exception("Email already registered");
        }

        $stmt = $conn->prepare("INSERT INTO Users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password_hash, $role);
        $stmt->execute();

        $user_id = $conn->insert_id;

        if ($role === 'Student') {
            $stmtStudent = $conn->prepare("INSERT INTO Students (user_id) VALUES (?)");
            $stmtStudent->bind_param("i", $user_id);
            $stmtStudent->execute();
        }
        $conn->commit();
        header("Location: ../templates/login.php?success=registered");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_msg = urlencode($e->getMessage());
        header("Location: ../templates/register.php?error=$error_msg");
        exit();
    }
}
?>