<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BU Hostel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/hostel-management-system/assets/css/style.css">
    <style>
        :root {
            --bu-dark-blue: #003366;
            --bu-gold: #ffcc00;
        }

        .navbar {
            background-color: var(--bu-dark-blue) !important;
            border-bottom: 3px solid var(--bu-gold);
        }

        .offcanvas {
            background-color: #f8f9fa;
            width: 280px !important;
        }

        .nav-link-custom {
            color: #333;
            padding: 10px 15px;
            border-radius: 5px;
            transition: 0.3s;
            display: block;
            text-decoration: none;
        }

        .nav-link-custom:hover {
            background-color: var(--bu-dark-blue);
            color: white;
        }

        .nav-link-custom i {
            width: 25px;
        }

        .logo-img {
            height: 50px;
            width: auto;
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark sticky-top">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light me-3" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarMenu">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand d-flex align-items-center" href="/hostel-management-system/index.php">
                    <img src="/hostel-management-system/assets/images/logo.png" alt="BU Logo" class="logo-img">
                    <div class="d-none d-md-block">
                        <span class="fw-bold d-block" style="line-height: 1;">University of Barishal</span>
                        <small style="font-size: 0.7rem; letter-spacing: 1px;">Hostel Management System</small>
                    </div>
                </a>
            </div>

            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="btn btn-link text-white text-decoration-none dropdown-toggle" type="button"
                            data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo explode(' ', $_SESSION['name'])[0]; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/hostel-management-system/modules/profile.php"><i class="fas fa-id-card me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger"
                                    href="/hostel-management-system/api/logout_api.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/hostel-management-system/templates/login.php"
                        class="btn btn-outline-light btn-sm px-3 me-2">Login</a>
                    <a href="/hostel-management-system/templates/register.php"
                        class="btn btn-warning btn-sm px-3 fw-bold">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- ===== OFFCANVAS SIDEBAR ===== -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold text-primary">Main Navigation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="list-group list-group-flush mt-3">

                <!-- Home -->
                <a href="/hostel-management-system/index.php"
                   class="nav-link-custom mx-3 mb-2">
                    <i class="fas fa-home"></i> Home
                </a>

                <!-- Hostels & Halls → index.php scrolled to #halls -->
                <a href="/hostel-management-system/index.php#halls"
                   class="nav-link-custom mx-3 mb-2"
                   data-bs-dismiss="offcanvas"
                   onclick="smoothScrollAfterNav(event)">
                    <i class="fas fa-hotel"></i> Hostels &amp; Halls
                </a>

                <!-- Notices -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/hostel-management-system/modules/notifications.php"
                       class="nav-link-custom mx-3 mb-2"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-bullhorn"></i> Notices
                    </a>
                <?php else: ?>
                    <a href="/hostel-management-system/templates/login.php"
                       class="nav-link-custom mx-3 mb-2"
                       data-bs-dismiss="offcanvas">
                        <i class="fas fa-bullhorn"></i> Notices
                    </a>
                <?php endif; ?>

                <hr class="mx-3">

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/hostel-management-system/templates/login.php"
                       class="nav-link-custom mx-3 mb-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php else: ?>
                    <a href="/hostel-management-system/templates/dashboard/<?php echo strtolower($_SESSION['role']); ?>.php"
                        class="nav-link-custom mx-3 mb-2 fw-bold text-primary">
                        <i class="fas fa-tachometer-alt"></i> My Dashboard
                    </a>
                <?php endif; ?>

                <a href="#" class="nav-link-custom mx-3 mb-2">
                    <i class="fas fa-phone-alt"></i> Contact Support
                </a>
                <a href="#" class="nav-link-custom mx-3 mb-2">
                    <i class="fas fa-question-circle"></i> FAQ
                </a>
            </div>

            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-light border-top text-center">
                <small class="text-muted">BU HMS v1.0 &copy; 2026</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Smooth scroll to #halls after sidebar closes (handles same-page and cross-page)
        function smoothScrollAfterNav(e) {
            const currentPage = window.location.pathname;
            const isIndex = currentPage.endsWith('index.php') || currentPage.endsWith('/hostel-management-system/') || currentPage === '/hostel-management-system';

            if (isIndex) {
                // Already on index.php — prevent navigation, just scroll
                e.preventDefault();
                const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('sidebarMenu'));
                if (offcanvas) offcanvas.hide();
                setTimeout(() => {
                    const target = document.getElementById('halls');
                    if (target) target.scrollIntoView({ behavior: 'smooth' });
                }, 300);
            }
            // If NOT on index.php, let the href navigate normally to index.php#halls
        }

        // If page loaded with #halls in URL, scroll to it smoothly
        window.addEventListener('load', () => {
            if (window.location.hash === '#halls') {
                setTimeout(() => {
                    const el = document.getElementById('halls');
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                }, 200);
            }
        });
    </script>