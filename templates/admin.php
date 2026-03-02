<!-- =================== PAYMENT MANAGEMENT =================== -->
<hr>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Payment Management</h3>
    <a href="../../api/payment_report.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-download me-1"></i> Export CSV
    </a>
</div>

<!-- Filter Tabs -->
<ul class="nav nav-tabs mb-3" id="paymentTabs">
    <li class="nav-item"><a class="nav-link <?php echo (!isset($_GET['pstatus']) || $_GET['pstatus']==='Pending') ? 'active' : ''; ?>" href="?pstatus=Pending">⏳ Pending</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (($_GET['pstatus'] ?? '')  === 'Verified') ? 'active' : ''; ?>" href="?pstatus=Verified">✅ Verified</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (($_GET['pstatus'] ?? '') === 'Rejected') ? 'active' : ''; ?>" href="?pstatus=Rejected">❌ Rejected</a></li>
    <li class="nav-item"><a class="nav-link <?php echo (($_GET['pstatus'] ?? '') === 'all') ? 'active' : ''; ?>" href="?pstatus=all">📋 All</a></li>
</ul>

<?php
$pstatus = $_GET['pstatus'] ?? 'Pending';
$where_clause = ($pstatus !== 'all') ? "WHERE p.status = '$pstatus'" : "";

$payments = $conn->query("
    SELECT p.payment_id, p.receipt_no, p.payment_type, p.month, p.amount,
           p.payment_method, p.transaction_id, p.payment_date, p.status,
           p.note, u.name AS student_name, s.student_reg_id
    FROM Payments p
    JOIN Students s ON p.student_id = s.student_id
    JOIN Users u ON s.user_id = u.user_id
    $where_clause
    ORDER BY p.payment_date DESC
");
?>

<div class="table-responsive">
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Receipt No.</th>
            <th>Student</th>
            <th>Type</th>
            <th>Month</th>
            <th>Amount</th>
            <th>Method / TXN</th>
            <th>Date</th>
            <th>Status</th>
            <th class="text-center">Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($payments && $payments->num_rows > 0):
        while ($p = $payments->fetch_assoc()):
            $badge = match($p['status']) {
                'Verified' => 'success', 'Pending' => 'warning',
                'Rejected' => 'danger',  default   => 'secondary'
            };
    ?>
        <tr>
            <td><code><?php echo htmlspecialchars($p['receipt_no'] ?? '-'); ?></code></td>
            <td>
                <strong><?php echo htmlspecialchars($p['student_name']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($p['student_reg_id'] ?? ''); ?></small>
            </td>
            <td><?php echo htmlspecialchars($p['payment_type']); ?></td>
            <td><?php echo htmlspecialchars($p['month'] ?? '-'); ?></td>
            <td class="fw-bold text-success">৳<?php echo number_format($p['amount'], 2); ?></td>
            <td>
                <?php echo htmlspecialchars($p['payment_method'] ?? '-'); ?><br>
                <small class="text-muted"><code><?php echo htmlspecialchars($p['transaction_id']); ?></code></small>
            </td>
            <td><?php echo date('d M Y', strtotime($p['payment_date'])); ?></td>
            <td><span class="badge bg-<?php echo $badge; ?>"><?php echo $p['status']; ?></span></td>
            <td class="text-center">
                <?php if ($p['status'] === 'Pending'): ?>
                    <a href="../../api/verify_payment.php?id=<?php echo $p['payment_id']; ?>&action=verify"
                       class="btn btn-success btn-sm"
                       onclick="return confirm('Verify this payment?')">✅ Verify</a>
                    <a href="../../api/verify_payment.php?id=<?php echo $p['payment_id']; ?>&action=reject"
                       class="btn btn-outline-danger btn-sm"
                       onclick="return confirm('Reject this payment?')">❌ Reject</a>
                <?php elseif ($p['status'] === 'Verified'): ?>
                    <a href="../../api/payment_receipt.php?id=<?php echo $p['payment_id']; ?>"
                       class="btn btn-outline-success btn-sm" target="_blank">🧾 Receipt</a>
                <?php else: ?>
                    <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; else: ?>
        <tr><td colspan="9" class="text-center py-4 text-muted">No <?php echo $pstatus; ?> payments found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>