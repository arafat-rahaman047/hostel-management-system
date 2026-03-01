<?php
require_once "../includes/auth.php";
checkAccess(['Student']);
include "../includes/header.php";
include "../includes/db.php";

$user_id = $_SESSION['user_id'];
// Fetch basic user data
$u_query = $conn->prepare("SELECT name, email FROM Users WHERE user_id = ?");
$u_query->bind_param("i", $user_id);
$u_query->execute();
$user_info = $u_query->get_result()->fetch_assoc();

// Check for existing booking
$st_query = $conn->prepare("SELECT student_id FROM Students WHERE user_id = ?");
$st_query->bind_param("i", $user_id);
$st_query->execute();
$student_id = $st_query->get_result()->fetch_assoc()['student_id'];

$check_booking = $conn->prepare("SELECT status FROM Bookings WHERE student_id = ? AND status IN ('Pending', 'Approved', 'CheckedIn')");
$check_booking->bind_param("i", $student_id);
$check_booking->execute();
$existing_booking = $check_booking->get_result()->fetch_assoc();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow border-0" style="border-radius: 20px;">
                <div class="card-header bg-primary text-white text-center py-4" style="border-radius: 20px 20px 0 0;">
                    <h4 class="mb-0 fw-bold">Hall Admission Form</h4>
                    <small>University of Barishal</small>
                </div>
                <div class="card-body p-4 p-md-5">

                    <?php if ($existing_booking): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-check-circle me-2"></i>
                            You have an active application: <strong><?php echo $existing_booking['status']; ?></strong>
                        </div>
                    <?php else: ?>
                        <form action="../api/booking_api.php" method="POST">

                            <div class="mb-5">
                                <h5 class="text-primary border-bottom pb-2 mb-4"><i
                                        class="fas fa-user-graduate me-2"></i>Personal & Academic Information</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text" class="form-control bg-light"
                                        value="<?php echo $user_info['name']; ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Student ID / Registration No.</label>
                                    <input type="text" name="student_reg_id" class="form-control" required
                                        placeholder="e.g. 21CSE045">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Department</label>
                                    <input type="text" name="department" class="form-control" required
                                        placeholder="e.g. Computer Science and Engineering">
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Blood Group</label>
                                        <select name="blood_group" class="form-select" required>
                                            <option value="" disabled selected>Select Group</option>
                                            <option>A+</option>
                                            <option>A-</option>
                                            <option>B+</option>
                                            <option>B-</option>
                                            <option>O+</option>
                                            <option>O-</option>
                                            <option>AB+</option>
                                            <option>AB-</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Contact No.</label>
                                        <input type="text" name="contact_no" class="form-control" required
                                            placeholder="017xxxxxxxx">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">University Email</label>
                                    <input type="email" class="form-control bg-light"
                                        value="<?php echo $user_info['email']; ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h5 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-users me-2"></i>Guardian
                                    Information</h5>

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-semibold">Father's Name</label>
                                        <input type="text" name="father_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Father's Contact</label>
                                        <input type="text" name="father_contact" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-semibold">Mother's Name</label>
                                        <input type="text" name="mother_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Mother's Contact</label>
                                        <input type="text" name="mother_contact" class="form-control" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Permanent Address</label>
                                    <textarea name="permanent_address" class="form-control" rows="3" required
                                        placeholder="Village, Post, Upazila, District"></textarea>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h5 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-building me-2"></i>Hostel
                                    Selection</h5>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Hall of Residence</label>
                                    <select name="hostel_id" class="form-select border-primary shadow-sm" required>
                                        <option value="" selected disabled>-- Choose a Hall --</option>
                                        <?php
                                        $hostels = $conn->query("SELECT * FROM Hostels");
                                        while ($h = $hostels->fetch_assoc()) {
                                            echo "<option value='{$h['hostel_id']}'>{$h['name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Reason for Stay</label>
                                    <textarea name="reason_for_stay" class="form-control" rows="2" required
                                        placeholder="Briefly explain why you need hostel accommodation"></textarea>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg fw-bold shadow">
                                    Submit Application <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>