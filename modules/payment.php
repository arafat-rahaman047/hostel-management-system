<?php
require_once "../includes/auth.php";
checkAccess(['Student']);
include "../includes/header.php";
include "../includes/db.php";

$user_id = $_SESSION['user_id'];

// Get student record
$st = $conn->prepare("SELECT s.student_id, s.hostel_id, h.name AS hostel_name
                      FROM students s
                      LEFT JOIN hostels h ON s.hostel_id = h.hostel_id
                      WHERE s.user_id = ?");
$st->bind_param("i", $user_id);
$st->execute();
$student = $st->get_result()->fetch_assoc();
$student_id = $student['student_id'] ?? null;

// Fee structure for student's hostel
$fees = [];
if (!empty($student['hostel_id'])) {
    $fee_q = $conn->prepare("SELECT * FROM fee_structure WHERE hostel_id = ? AND academic_year = '2025-2026'");
    if ($fee_q) {
        $fee_q->bind_param("i", $student['hostel_id']);
        $fee_q->execute();
        $fees = $fee_q->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

$history = [];
if ($student_id) {
    $hist_q = $conn->prepare("
        SELECT p.*, u.name AS verified_by_name
        FROM payments p
        LEFT JOIN users u ON p.verified_by = u.user_id
        WHERE p.student_id = ?
        ORDER BY p.payment_date DESC
    ");

    if ($hist_q) {
        $hist_q->bind_param("i", $student_id);
        $hist_q->execute();
        $history = $hist_q->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        die("SQL Error: " . $conn->error);
    }
}

// Summary stats
$total_paid = array_sum(array_column(
    array_filter($history, fn($p) => $p['status'] === 'Verified'),
    'amount'
));
$pending_count = count(array_filter($history, fn($p) => $p['status'] === 'Pending'));
?>

<div class="container py-4">

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Hall Payment Portal
            </h3>
            <p class="text-muted small mb-0">
                <?php echo htmlspecialchars($student['hostel_name'] ?? 'No Hostel Assigned'); ?></p>
        </div>
        <?php if ($student_id): ?>
            <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-plus me-2"></i>Submit Payment
            </button>
        <?php endif; ?>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Total Verified Paid</h6>
                    <h3 class="fw-bold text-success mb-0">৳<?php echo number_format($total_paid, 2); ?></h3>
                    <small class="text-muted">Academic Year 2025-26</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Pending Verifications</h6>
                    <h3 class="fw-bold text-warning mb-0"><?php echo $pending_count; ?></h3>
                    <small class="text-muted">Awaiting admin approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-info border-4">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Total Transactions</h6>
                    <h3 class="fw-bold text-info mb-0"><?php echo count($history); ?></h3>
                    <small class="text-muted">All time records</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Structure -->
    <?php if (!empty($fees)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-list-alt me-2 text-primary"></i>Fee Structure 2025-2026</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Fee Type</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fee['payment_type']); ?></td>
                                <td class="text-end fw-bold text-success">৳<?php echo number_format($fee['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Payment History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Payment History</h5>
            <?php if (!empty($history)): ?>
                <a href="../api/payment_report.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-download me-1"></i>Export Report
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($history)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-receipt fa-3x opacity-25 mb-3"></i>
                    <p class="mb-0">No payment records found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Receipt No.</th>
                                <th>Type</th>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $p):
                                $badge = match ($p['status']) {
                                    'Verified' => 'success',
                                    'Pending' => 'warning',
                                    'Rejected' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($p['receipt_no'] ?? 'N/A'); ?></code></td>
                                    <td><?php echo htmlspecialchars($p['payment_type']); ?></td>
                                    <td><?php echo htmlspecialchars($p['month'] ?? '-'); ?></td>
                                    <td class="fw-bold">৳<?php echo number_format($p['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($p['payment_method'] ?? '-'); ?></td>
                                    <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
                                    <td>
                                        <span
                                            class="badge bg-<?php echo $badge; ?> px-3 py-2"><?php echo $p['status']; ?></span>
                                        <?php if ($p['status'] === 'Verified' && $p['verified_by_name']): ?>
                                            <br><small class="text-muted">by
                                                <?php echo htmlspecialchars($p['verified_by_name']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['status'] === 'Verified'): ?>
                                            <a href="../api/payment_receipt.php?id=<?php echo $p['payment_id']; ?>"
                                                class="btn btn-outline-success btn-sm" target="_blank">
                                                <i class="fas fa-receipt me-1"></i>Receipt
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Submit Payment Modal -->
<?php if ($student_id): ?>
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-paper-plane me-2"></i>Submit Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="../api/payment_api.php" method="POST">
                    <div class="modal-body p-4">

                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>
                            Pay via bKash/Nagad to <strong>01XXXXXXXXX</strong>, then submit the transaction ID here for
                            verification.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Type</label>
                                <select name="payment_type" class="form-select" required>
                                    <option value="" disabled selected>Select type</option>
                                    <option value="HallFee">Hall Fee</option>
                                    <option value="CanteenFee">Canteen Fee</option>
                                    <option value="Fine">Fine</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Month</label>
                                <input type="month" name="month" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Amount (৳)</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="1" required
                                    placeholder="e.g. 3000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="" disabled selected>Select method</option>
                                    <option value="bKash">bKash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Rocket">Rocket</option>
                                    <option value="BankTransfer">Bank Transfer</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Transaction ID</label>
                                <input type="text" name="transaction_id" class="form-control" required
                                    placeholder="e.g. 8N7KD3Q2L1">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Note (Optional)</label>
                                <textarea name="note" class="form-control" rows="2"
                                    placeholder="Any additional information..."></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success fw-bold px-4">
                            <i class="fas fa-check me-2"></i>Submit for Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Show success/error messages
if (isset($_GET['success'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div class="toast show bg-success text-white border-0 shadow">
            <div class="toast-body"><i class="fas fa-check-circle me-2"></i>Payment submitted successfully! Awaiting
                verification.</div>
        </div>
    </div>
<?php elseif (isset($_GET['error'])): ?>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index:9999">
        <div class="toast show bg-danger text-white border-0 shadow">
            <div class="toast-body"><i class="fas fa-times-circle me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>