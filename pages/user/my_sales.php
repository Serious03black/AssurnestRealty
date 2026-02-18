<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'driver'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Prepare query based on role
// We join property_sales with properties
if ($role === 'employee') {
    $sql = "
        SELECT p.property_id, p.property_type, p.location_city, p.full_location, 
               p.price, p.commission, p.image1, s.sale_date, s.sale_price
        FROM properties p
        JOIN property_sales s ON p.property_id = s.property_id
        WHERE s.emp_id = ?
        ORDER BY s.sale_date DESC
    ";
} else {
    // Driver
    $sql = "
        SELECT p.property_id, p.property_type, p.location_city, p.full_location, 
               p.price, p.commission, p.image1, s.sale_date, s.sale_price
        FROM properties p
        JOIN property_sales s ON p.property_id = s.property_id
        WHERE s.driver_id = ?
        ORDER BY s.sale_date DESC
    ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$sold_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_sales = count($sold_properties);
$total_commission = 0;
foreach ($sold_properties as $prop) {
    // Commission is calculated on the sale price (which usually matches property price but let's use sale_price from sales table if available, else property price)
    $price = $prop['sale_price'] > 0 ? $prop['sale_price'] : $prop['price'];
    $total_commission += ($price * $prop['commission'] / 100);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Sales | Assurnest Realty Agent</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --success: #28a745;
            --sidebar-width: 250px;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        h1 { text-align: center; margin-bottom: 1rem; color: var(--dark); font-size: 2.2rem; }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .summary-value { font-size: 2.2rem; font-weight: 700; color: var(--primary); }
        .summary-label { color: var(--gray); font-size: 1rem; margin-top: 0.5rem; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: var(--primary); color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }

        .property-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            background: #eee;
        }

        .no-sales { text-align: center; padding: 4rem 2rem; color: var(--gray); }
        .no-sales i { font-size: 4rem; color: #ddd; margin-bottom: 1rem; }

        .btn-view {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-view:hover { background: var(--primary-dark); }
        .commission-earned { color: var(--success); font-weight: 600; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }
    </style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>

<div class="main-content">
    
    <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()" style="border:none; background:none; margin-bottom:1rem;">
        <i class="fas fa-bars"></i> Menu
    </button>

    <h1>My Sales</h1>
    
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-value"><?= $total_sales ?></div>
            <div class="summary-label">Properties Sold</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">₹ <?= number_format($total_commission, 2) ?></div>
            <div class="summary-label">Total Commission Earned</div>
        </div>
    </div>

    <?php if (empty($sold_properties)): ?>
        <div class="no-sales">
            <i class="fas fa-handshake"></i>
            <h2>No Sales Yet</h2>
            <p>Start exploring available properties and mark items as sold to track them here!</p>
            <a href="availableProperty.php" style="background:var(--primary); color:white; padding:0.8rem 1.5rem; border-radius:8px; text-decoration:none; margin-top:1rem; display:inline-block;">
                Browse Available Properties
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Price</th>
                        <th>Commission</th>
                        <th>Earned</th>
                        <th>Sold Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sold_properties as $prop): 
                        $price = $prop['sale_price'] > 0 ? $prop['sale_price'] : $prop['price'];
                        $earned = ($price * $prop['commission'] / 100);
                    ?>
                        <tr>
                            <td>
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <?php if (!empty($prop['image1'])): ?>
                                        <img src="../../includes/view_image.php?id=<?= $prop['property_id'] ?>&num=1" class="property-image">
                                    <?php else: ?>
                                        <div class="property-image" style="display:flex; align-items:center; justify-content:center;"><i class="fas fa-home"></i></div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= htmlspecialchars($prop['property_type']) ?></strong><br>
                                        <small style="color:var(--gray);"><?= htmlspecialchars($prop['location_city']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>₹ <?= number_format($price, 2) ?></td>
                            <td><?= $prop['commission'] ?>%</td>
                            <td class="commission-earned">₹ <?= number_format($earned, 2) ?></td>
                            <td><?= date('d M Y', strtotime($prop['sale_date'])) ?></td>
                            <td>
                                <a href="viewProperty.php?id=<?= $prop['property_id'] ?>" class="btn-view">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    }
    document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);
</script>

</body>
</html>