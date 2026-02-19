<?php
// pages/admin/manage_commissions.php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

// Fetch all Employees and Drivers
$users   = [];

// 1. Employees
$sql_emp = "
    SELECT e.emp_id AS user_id, e.emp_name AS name, 'employee' AS role, e.commission, e.prefix
    FROM employees e
    ORDER BY e.emp_name
";
$stmt_emp = $pdo->query($sql_emp);
$emps = $stmt_emp->fetchAll(PDO::FETCH_ASSOC);

// 2. Drivers
$sql_driver = "
    SELECT d.driver_id AS user_id, d.driver_name AS name, 'driver' AS role, d.referral_bonus, d.prefix
    FROM cab_drivers d
    ORDER BY d.driver_name
";
$stmt_driver = $pdo->query($sql_driver);
$drivers = $stmt_driver->fetchAll(PDO::FETCH_ASSOC);

$all_users = array_merge($emps, $drivers);

// Calculate Stats for each user
foreach ($all_users as &$u) {
    $uid = $u['user_id'];
    $role = $u['role'];
    
    // A. Total Earned
    $total_earned = 0;
    
    // Check Sales
    if ($role === 'employee') {
        $stmt_s = $pdo->prepare("SELECT p.price, p.commission, s.sale_price FROM property_sales s JOIN properties p ON s.property_id = p.property_id WHERE s.emp_id = ?");
    } else {
        $stmt_s = $pdo->prepare("SELECT p.price, p.commission, s.sale_price FROM property_sales s JOIN properties p ON s.property_id = p.property_id WHERE s.driver_id = ?");
    }
    $stmt_s->execute([$uid]);
    $sales = $stmt_s->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($sales as $s) {
        $price = $s['sale_price'] > 0 ? $s['sale_price'] : $s['price'];
        $total_earned += ($price * $s['commission'] / 100);
    }
    
    // Add Bonus (Drivers)
    if ($role === 'driver') {
        $total_earned += ($u['referral_bonus'] ?? 0);
    }
    
    // B. Total Received
    $stmt_p = $pdo->prepare("SELECT SUM(amount) FROM payments WHERE user_id = ? AND role = ?");
    $stmt_p->execute([$uid, $role]);
    $total_received = $stmt_p->fetchColumn() ?: 0;
    
    $u['total_earned'] = $total_earned;
    $u['total_received'] = $total_received;
    $u['pending'] = $total_earned - $total_received;
}
unset($u);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Commissions | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root { --primary: #2c3e50; --accent: #3498db; --light: #f4f6f9; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin:0; display:flex; }
        .sidebar { width: 250px; background: var(--primary); color:white; min-height:100vh; position:fixed; }
        .main { margin-left: 250px; padding: 2rem; width: 100%; }
        
        .header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
        h1 { margin:0; color:#333; }
        
        .card { background:white; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
        
        table { width:100%; border-collapse:collapse; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f8f9fa; font-weight:600; color:#555; }
        tr:hover { background:#f1f1f1; }
        
        .badge { padding:4px 8px; border-radius:4px; font-size:0.85rem; font-weight:600; }
        .badge-emp { background:#e3f2fd; color:#1565c0; }
        .badge-driver { background:#fff3e0; color:#e65100; }
        
        .btn { padding:6px 12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; font-size:0.9rem; display:inline-block; }
        .btn-pay { background: var(--success, #28a745); color:white; }
        .btn-print { background: var(--primary); color:white; margin-left:5px; }
        
        /* Modal */
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
        .modal-content { background:white; padding:2rem; border-radius:8px; width:400px; max-width:90%; position:relative; }
        .close { position:absolute; top:15px; right:15px; cursor:pointer; font-size:1.2rem; }
        
        .form-group { margin-bottom:15px; }
        .form-label { display:block; margin-bottom:5px; font-weight:600; }
        .form-control { width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; }
    </style>
</head>
<body>

<div class="sidebar">
    <?php include '../../includes/sidebar.php'; ?>
</div>

<div class="main">
    <div class="header">
        <h1>Manage Commissions</h1>
    </div>

    <?php if ($success): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:5px; margin-bottom:20px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Total Earned</th>
                    <th>Total Received</th>
                    <th>Pending</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($u['name']) ?></strong><br>
                            <small style="color:#777;"><?= htmlspecialchars($u['prefix']) ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $u['role'] === 'employee' ? 'badge-emp' : 'badge-driver' ?>">
                                <?= ucfirst($u['role']) ?>
                            </span>
                        </td>
                        <td style="color:#28a745; font-weight:600;">₹ <?= number_format($u['total_earned'], 2) ?></td>
                        <td style="color:#007bff;">₹ <?= number_format($u['total_received'], 2) ?></td>
                        <td style="color:#dc3545; font-weight:700;">₹ <?= number_format($u['pending'], 2) ?></td>
                        <td>
                            <button class="btn btn-pay" onclick="openPayModal('<?= $u['user_id'] ?>', '<?= $u['role'] ?>', '<?= addslashes($u['name']) ?>', '<?= $u['pending'] ?>')">
                                <i class="fas fa-money-bill-wave"></i> Pay
                            </button>
                            <a href="print_bill.php?id=<?= $u['user_id'] ?>&role=<?= $u['role'] ?>" target="_blank" class="btn btn-print">
                                <i class="fas fa-print"></i> Bill
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Payment Modal -->
<div id="payModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Record Payment</h2>
        <p>For: <span id="modalUser" style="font-weight:bold;"></span></p>
        
        <form action="process_payment.php" method="POST">
            <input type="hidden" name="user_id" id="modalUserId">
            <input type="hidden" name="role" id="modalRole">
            
            <div class="form-group">
                <label class="form-label">Pending Amount</label>
                <input type="text" id="modalPending" class="form-control" readonly style="background:#f9f9f9;">
            </div>

            <div class="form-group">
                <label class="form-label">Payment Amount (₹)</label>
                <input type="number" name="amount" step="0.01" class="form-control" required min="1">
            </div>
            
            <div class="form-group">
                <label class="form-label">Transaction ID / Ref (Optional)</label>
                <input type="text" name="transaction_id" class="form-control" placeholder="e.g. UPI-123456">
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            
            <button type="submit" class="btn btn-pay" style="width:100%;">Confirm Payment</button>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('payModal');
    
    function openPayModal(id, role, name, pending) {
        document.getElementById('modalUserId').value = id;
        document.getElementById('modalRole').value = role;
        document.getElementById('modalUser').textContent = name;
        document.getElementById('modalPending').value = pending;
        modal.style.display = 'flex';
    }
    
    function closeModal() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

</body>
</html>
