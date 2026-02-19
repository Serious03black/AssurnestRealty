<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch Sales with joins to properties and sellers (employees/drivers)
$sql = "
    SELECT 
        s.sale_id, s.sale_date, s.sale_price,
        p.property_name, p.property_type, p.location_city, p.commission,
        e.emp_name, d.driver_name,
        COALESCE(e.emp_name, d.driver_name) as seller_name,
        CASE WHEN e.emp_id IS NOT NULL THEN 'Employee' ELSE 'Driver' END as seller_role
    FROM property_sales s
    JOIN properties p ON s.property_id = p.property_id
    LEFT JOIN employees e ON s.emp_id = e.emp_id
    LEFT JOIN cab_drivers d ON s.driver_id = d.driver_id
    ORDER BY s.sale_date DESC
";
$sales = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$total_sales = count($sales);
$total_commission = 0;
foreach($sales as $sale) {
    // Commission is based on sale_price * commission %
    $comm_amount = ($sale['sale_price'] * $sale['commission']) / 100;
    $total_commission += $comm_amount;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .sidebar { width: 250px; background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; }
        .main-content { margin-left: 250px; padding: 2rem; }
        
        .summary-box { display: flex; gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 10px; flex: 1; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .card h3 { margin: 0; color: #777; font-size: 1rem; }
        .card .val { font-size: 2rem; font-weight: bold; color: #2a5bd7; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2a5bd7; color: white; }
    </style>
</head>
<body>

<nav class="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</nav>

<div class="main-content">
    <h1>Sales Reports</h1>
    
    <div class="summary-box">
        <div class="card">
            <h3>Total Properties Sold</h3>
            <div class="val"><?= $total_sales ?></div>
        </div>
        <div class="card">
            <h3>Total Commission Payout</h3>
            <div class="val">₹ <?= number_format($total_commission, 2) ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Property</th>
                <th>Price</th>
                <th>Seller</th>
                <th>Role</th>
                <th>Commission</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $row): 
                $comm = ($row['sale_price'] * $row['commission']) / 100;
            ?>
                <tr>
                    <td><?= date('d M Y', strtotime($row['sale_date'])) ?></td>
                    <td>
                        <?= htmlspecialchars($row['property_name']) ?><br>
                        <small><?= htmlspecialchars($row['location_city']) ?></small>
                    </td>
                    <td>₹ <?= number_format($row['sale_price'], 2) ?></td>
                    <td><?= htmlspecialchars($row['seller_name']) ?></td>
                    <td><?= $row['seller_role'] ?></td>
                    <td>₹ <?= number_format($comm, 2) ?> (<?= $row['commission'] ?>%)</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>