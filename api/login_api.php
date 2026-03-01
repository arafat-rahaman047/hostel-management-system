<?php
session_start();
include('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Use Prepared Statements to prevent SQL Injection
    $stmt = $conn->prepare("SELECT user_id, name, password_hash, role FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // 2. Verify Password
        if (password_verify($password, $user['password_hash'])) {
            // Set Session Data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            // 3. Define Redirects
            $redirects = [
                'Student' => '../templates/dashboard/student.php',
                'Provost' => '../templates/dashboard/provost.php',
                'AdminOfficer' => '../templates/dashboard/admin.php',
                'AssistantRegistrar' => '../templates/dashboard/registrar.php',
                'HouseTutor' => '../templates/dashboard/tutor.php'
            ];

            // 4. Safely redirect or fallback to index
            $target = $redirects[$user['role']] ?? '../index.php';
            header("Location: " . $target);
            exit(); // Always exit after a header redirect
        } else {
            header("Location: ../templates/login.php?error=invalid_pass");
            exit();
        }
    } else {
        header("Location: ../templates/login.php?error=not_found");
        exit();
    }
}
?>