<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Fetch properties sold by this user
$stmt = $pdo->prepare("
    SELECT p.id, p.type, p.location, p.address, p.price, p.commission, p.image1, s.sale_date
    FROM properties p
    JOIN sales s ON p.id = s.property_id
    WHERE s.user_id = ?
    ORDER BY s.sale_date DESC
");
$stmt->execute([$user_id]);
$sold_properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_sales = count($sold_properties);
$total_commission = 0;
foreach ($sold_properties as $prop) {
    $total_commission += ($prop['price'] * $prop['commission'] / 100);
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
            /* --navbar-height: 70px; */
        }

        body {                 font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        /* .navbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--navbar-height); background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.1); z-index: 999; display: flex; align-items: center; padding: 0 20px; } */
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            /* margin-top: var(--navbar-height); */
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

        .summary-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .summary-label { color: var(--gray); font-size: 1rem; margin-top: 0.5rem; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th { background: var(--primary); color: white; font-weight: 600; }

        tr:hover { background: #f8f9fa; }

        .property-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .no-sales {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray);
        }

        .no-sales i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

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

        .commission-earned {
            color: var(--success);
            font-weight: 600;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead tr { position: absolute; top: -9999px; left: -9999px; }
            tr { margin-bottom: 1.5rem; border: 1px solid #ddd; border-radius: 12px; padding: 1rem; }
            td { border: none; position: relative; padding-left: 50%; }
            td:before {
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 1rem;
                white-space: nowrap;
                font-weight: 600;
                color: var(--gray);
            }
            td:nth-of-type(1):before { content: "Property"; }
            td:nth-of-type(2):before { content: "Price"; }
            td:nth-of-type(3):before { content: "Commission"; }
            td:nth-of-type(4):before { content: "Earned"; }
            td:nth-of-type(5):before { content: "Sold Date"; }
            td:nth-of-type(6):before { content: "Action"; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>

<div class="main-content">

    <h1>My Sales</h1>
    <!-- Summary Cards -->
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
            <p>Start exploring available properties and make your first sale!</p>
            <a href="available_properties.php" style="background:var(--primary); color:white; padding:0.8rem 1.5rem; border-radius:8px; text-decoration:none; margin-top:1rem; display:inline-block;">
                Browse Available Properties
            </a>
        </div>
    <?php else: ?>
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
                    $earned = ($prop['price'] * $prop['commission'] / 100);
                ?>
                    <tr>
                        <td>
                            <?php if (!empty($prop['image1'])): ?>
                                <img src="<?= htmlspecialchars($prop['image1']) ?>" alt="Property" class="property-image">
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($prop['type']) ?></strong><br>
                            <small style="color:var(--gray);"><?= htmlspecialchars($prop['location']) ?></small>
                        </td>
                        <td>₹ <?= number_format($prop['price'], 2) ?></td>
                        <td><?= $prop['commission'] ?>%</td>
                        <td class="commission-earned">₹ <?= number_format($earned, 2) ?></td>
                        <td><?= date('d M Y', strtotime($prop['sale_date'])) ?></td>
                        <td>
                            <a href="viewProperty.php?id=<?= $prop['id'] ?>" class="btn-view">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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