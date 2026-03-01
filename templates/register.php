<?php include "../includes/header.php"; ?>

<div class="container d-flex justify-content-center align-items-center py-5" style="min-height: 90vh;">
    <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%; border-radius: 15px;">
        <div class="card-header text-center bg-white pt-4 border-0">
            <img src="../assets/images/logo.png" alt="BU Logo" style="height: 70px;">
            <h4 class="fw-bold mt-3 text-primary">Create Account</h4>
            <p class="text-muted small">Join the University Hostel Management System</p>
        </div>

        <div class="card-body px-4 pb-4">
            <form action="../api/register_api.php" method="POST">

                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fas fa-user text-muted"></i></span>
                        <input type="text" name="name" class="form-control border-start-0 ps-0 bg-light"
                            placeholder="Arafat Rahaman" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">University Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0 bg-light"
                            placeholder="arahaman@bu.ac.bd" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0 bg-light"
                            placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Designation / Role</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fas fa-user-tag text-muted"></i></span>
                        <select name="role" class="form-select border-start-0 ps-0 bg-light" required>
                            <option value="" selected disabled>Select your role</option>
                            <option value="Student">Student</option>
                            <option value="HouseTutor">House Tutor</option>
                            <option value="Provost">Provost</option>
                            <option value="AdminOfficer">Administrative Officer</option>
                            <option value="AssistantRegistrar">Assistant Registrar</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="border-radius: 8px;">
                    Register Now <i class="fas fa-user-plus ms-2"></i>
                </button>
            </form>
        </div>

        <div class="card-footer bg-light border-0 text-center py-3"
            style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;">
            <p class="mb-0 small">Already have an account? <a href="login.php"
                    class="text-decoration-none fw-bold">Login here</a></p>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>