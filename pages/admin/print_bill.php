<?php
// pages/admin/print_bill.php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

$user_id = $_GET['id'] ?? '';
$role    = $_GET['role'] ?? '';

if (!$user_id || !in_array($role, ['employee', 'driver'])) {
    die("Invalid User");
}

// Fetch User Details
if ($role === 'employee') {
    $stmt = $pdo->prepare("SELECT emp_name as name, prefix as id_num, mobile_no, email FROM employees WHERE emp_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT driver_name as name, prefix as id_num, mobile_no, email, referral_bonus FROM cab_drivers WHERE driver_id = ?");
}
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("User not found");

// Fetch Earnings (Sales)
if ($role === 'employee') {
    $stmt_sales = $pdo->prepare("
        SELECT p.property_name, p.property_type, s.sale_date, s.sale_price, p.price, p.commission
        FROM property_sales s
        JOIN properties p ON s.property_id = p.property_id
        WHERE s.emp_id = ?
        ORDER BY s.sale_date DESC
    ");
} else {
    $stmt_sales = $pdo->prepare("
        SELECT p.property_name, p.property_type, s.sale_date, s.sale_price, p.price, p.commission
        FROM property_sales s
        JOIN properties p ON s.property_id = p.property_id
        WHERE s.driver_id = ?
        ORDER BY s.sale_date DESC
    ");
}
$stmt_sales->execute([$user_id]);
$sales = $stmt_sales->fetchAll(PDO::FETCH_ASSOC);

// Calculate Totals
$total_earned_sales = 0;
foreach ($sales as $s) {
    $price = $s['sale_price'] > 0 ? $s['sale_price'] : $s['price'];
    $total_earned_sales += ($price * $s['commission'] / 100);
}

$referral_bonus = ($role === 'driver') ? ($user['referral_bonus'] ?? 0) : 0;
$grand_total_earned = $total_earned_sales + $referral_bonus;

// Fetch Payments
$stmt_pay = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? AND role = ? ORDER BY payment_date DESC");
$stmt_pay->execute([$user_id, $role]);
$payments = $stmt_pay->fetchAll(PDO::FETCH_ASSOC);

$total_paid = 0;
foreach ($payments as $p) {
    $total_paid += $p['amount'];
}

$pending = $grand_total_earned - $total_paid;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement - <?= htmlspecialchars($user['name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; }
        .invoice-info { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: bold; background: #eee; padding: 5px 10px; margin: 20px 0 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f9f9f9; }
        .text-right { text-align: right; }
        .total-box { margin-top: 30px; border: 2px solid #333; padding: 15px; width: 300px; margin-left: auto; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print" style="padding:10px 20px; background:#333; color:white; border:none; cursor:pointer; margin-bottom:20px;">Print Statement</button>

    <div class="header">
        <div class="logo">Assurnest Realty</div>
        <div>Commission & Payment Statement</div>
        <div>Date: <?= date('d M Y') ?></div>
    </div>

    <div class="invoice-info">
        <div>
            <strong>To:</strong><br>
            <?= htmlspecialchars($user['name']) ?><br>
            ID: <?= htmlspecialchars($user['id_num']) ?><br>
            <?= htmlspecialchars($user['email']) ?>
        </div>
        <div class="text-right">
            <strong>Statement Period:</strong><br>
            All Time
        </div>
    </div>

    <div class="section-title">Earnings Summary (Sales)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Property</th>
                <th>Sale Price</th>
                <th>Commission %</th>
                <th>Amount Earned</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sales)): ?>
                <tr><td colspan="5" style="text-align:center;">No sales records found.</td></tr>
            <?php else: ?>
                <?php foreach ($sales as $s): 
                    $price = $s['sale_price'] > 0 ? $s['sale_price'] : $s['price'];
                    $comm_amt = ($price * $s['commission'] / 100);
                ?>
                <tr>
                    <td><?= date('d M Y', strtotime($s['sale_date'])) ?></td>
                    <td><?= htmlspecialchars($s['property_name']) ?></td>
                    <td><?= number_format($price, 2) ?></td>
                    <td><?= $s['commission'] ?>%</td>
                    <td class="text-right"><?= number_format($comm_amt, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($role === 'driver' && $referral_bonus > 0): ?>
        <div class="section-title">Other Earnings</div>
        <table>
            <tr>
                <td>Referral Bonus</td>
                <td class="text-right"><?= number_format($referral_bonus, 2) ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <div class="section-title">Payment History</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>Notes</th>
                <th>Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="4" style="text-align:center;">No payments recorded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= date('d M Y H:i', strtotime($p['payment_date'])) ?></td>
                    <td><?= htmlspecialchars($p['transaction_id']) ?></td>
                    <td><?= htmlspecialchars($p['notes']) ?></td>
                    <td class="text-right"><?= number_format($p['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-box">
        <div class="total-row">
            <span>Total Earned:</span>
            <span>₹ <?= number_format($grand_total_earned, 2) ?></span>
        </div>
        <div class="total-row">
            <span>Total Received:</span>
            <span>₹ <?= number_format($total_paid, 2) ?></span>
        </div>
        <div class="total-row" style="border-top: 1px solid #333; padding-top: 10px; font-size: 1.2em;">
            <span>Pending Balance:</span>
            <span>₹ <?= number_format($pending, 2) ?></span>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; color: #777; font-size: 12px;">
        <p>This is a computer-generated statement.</p>
        <p>Assurnest Realty</p>
    </div>

    <script>
        // Auto-print option commented out to let user decide
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
