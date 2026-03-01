<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAccess($allowed_roles = [])
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: /hostel-management-system/templates/login.php?error=unauthorized");
        exit();
    }

    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: /hostel-management-system/templates/error.php?type=access_denied");
        exit();
    }
}
function redirectIfLoggedIn()
{
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['role'];
        $redirects = [
            'Student' => '/hostel-management-system/templates/dashboard/student.php',
            'Provost' => '/hostel-management-system/templates/dashboard/provost.php',
            'AdminOfficer' => '/hostel-management-system/templates/dashboard/admin.php',
            'AssistantRegistrar' => '/hostel-management-system/templates/dashboard/registrar.php',
            'HouseTutor' => '/hostel-management-system/templates/dashboard/tutor.php'
        ];
        header("Location: " . ($redirects[$role] ?? '/hostel-management-system/index.php'));
        exit();
    }
}
?>