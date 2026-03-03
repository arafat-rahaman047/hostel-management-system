<?php
require_once "../includes/auth.php";
checkAccess(['AdminOfficer', 'HouseTutor']);
include "../includes/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../modules/notifications.php");
    exit();
}

$posted_by = $_SESSION['user_id'];
$title     = trim($conn->real_escape_string($_POST['title']));
$body      = trim($conn->real_escape_string($_POST['body']));
$hostel_id = !empty($_POST['hostel_id']) ? (int)$_POST['hostel_id'] : null;

if (empty($title) || empty($body)) {
    header("Location: ../modules/notifications.php?error=missing_fields");
    exit();
}

$stmt = $conn->prepare("INSERT INTO notices (posted_by, hostel_id, title, body) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiss", $posted_by, $hostel_id, $title, $body);

$success = $stmt->execute();

if ($success) {
    // Log the action
    $log_stmt   = $conn->prepare("INSERT INTO logs (user_id, action) VALUES (?, ?)");
    $log_action = "Posted a notice: $title";
    $log_stmt->bind_param("is", $posted_by, $log_action);
    $log_stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Notice Posted' : 'Error'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .result-card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 460px;
            width: 100%;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
        }
        .countdown {
            font-size: 0.85rem;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="card result-card p-5 text-center">
        <?php if ($success): ?>
            <div class="icon-circle bg-success bg-opacity-10">
                <i class="fas fa-check-circle fa-3x text-success"></i>
            </div>
            <h4 class="fw-bold text-success mb-2">Notice Posted!</h4>
            <p class="text-muted mb-1">
                <strong><?php echo htmlspecialchars($title); ?></strong> has been published successfully.
            </p>
            <p class="text-muted small mb-4">Students can now see this notice on their dashboard.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="javascript:history.back()" class="btn btn-outline-secondary px-4">
                    <i class="fas fa-arrow-left me-2"></i>Go Back
                </a>
                <a href="../modules/notifications.php" class="btn btn-primary px-4">
                    <i class="fas fa-bullhorn me-2"></i>View Notices
                </a>
            </div>
            <p class="countdown mt-3 mb-0" id="countdown">Redirecting in <span id="sec">5</span>s...</p>
            <script>
                let s = 5;
                const el = document.getElementById('sec');
                const interval = setInterval(() => {
                    s--;
                    el.textContent = s;
                    if (s <= 0) {
                        clearInterval(interval);
                        window.location.href = '../modules/notifications.php';
                    }
                }, 1000);
            </script>
        <?php else: ?>
            <div class="icon-circle bg-danger bg-opacity-10">
                <i class="fas fa-times-circle fa-3x text-danger"></i>
            </div>
            <h4 class="fw-bold text-danger mb-2">Something Went Wrong</h4>
            <p class="text-muted mb-4">The notice could not be posted. Please try again.</p>
            <a href="javascript:history.back()" class="btn btn-outline-danger px-4">
                <i class="fas fa-arrow-left me-2"></i>Go Back
            </a>
        <?php endif; ?>
    </div>
</body>
</html>