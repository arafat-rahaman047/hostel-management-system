<?php include "../includes/header.php"; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg border-0" style="max-width: 400px; width: 100%; border-radius: 15px;">
        <div class="card-header text-center bg-white pt-4 border-0">
            <img src="../assets/images/logo.png" alt="BU Logo" style="height: 80px;">
            <h4 class="fw-bold mt-3 text-primary">Portal Login</h4>
            <p class="text-muted small">Access the Hostel Management System</p>
        </div>

        <div class="card-body px-4 pb-4">
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 small text-center" role="alert">
                    Invalid email or password.
                </div>
            <?php endif; ?>

            <form action="../api/login_api.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0 bg-light" placeholder="e.g. arahaman@bu.ac.bd" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0 bg-light" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="border-radius: 8px;">
                    Log In <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </form>
        </div>

        <div class="card-footer bg-light border-0 text-center py-3" style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
            <p class="mb-0 small">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register here</a></p>
            <a href="#" class="text-decoration-none small text-muted">Forgot password?</a>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>