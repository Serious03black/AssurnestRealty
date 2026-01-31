<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle date filter
$where_clause = "";
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date   = $_GET['end_date'];

    $where_clause = " WHERE s.sale_date BETWEEN ? AND ?";
    $params = [$start_date, $end_date];
}

// Fetch all sales
$query = "
    SELECT 
        s.id, 
        p.type, 
        p.location, 
        p.address, 
        p.price, 
        p.commission, 
        u.username AS agent, 
        s.sale_date,
        (p.price * p.commission / 100) AS commission_earned
    FROM sales s
    JOIN properties p ON s.property_id = p.id
    JOIN users u ON s.user_id = u.id
    $where_clause
    ORDER BY s.sale_date DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_sales = count($sales);
$total_commission = array_sum(array_column($sales, 'commission_earned'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --success: #28a745;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --sidebar-width: 250px;
            --navbar-height: 70px;
        }

        body {                 font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        .navbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--navbar-height); background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.1); z-index: 999; display: flex; align-items: center; padding: 0 20px; }
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        h1 { text-align: center; margin-bottom: 1.5rem; }
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
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .summary-value { font-size: 2.2rem; font-weight: 700; color: var(--primary); }
        .summary-label { color: var(--gray); font-size: 1rem; margin-top: 0.5rem; }

        .filter-form {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-form label { font-weight: 600; display: block; margin-bottom: 0.5rem; }
        .filter-form input, .filter-form button {
            padding: 0.8rem;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .filter-form button {
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
        }

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

        th { background: var(--primary); color: white; }

        tr:hover { background: #f8f9fa; }

        .export-btn {
            background: #28a745;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin: 1rem 0;
            font-weight: 600;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 768px) {
            .filter-form { flex-direction: column; }
            table { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</nav>

<!-- Navbar -->
<nav class="navbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include '../../includes/navbar.php'; ?>
</nav>

<div class="main-content">

    <h1>Sales Reports</h1>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-value"><?= $total_sales ?></div>
            <div class="summary-label">Total Sales</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">₹ <?= number_format($total_commission, 2) ?></div>
            <div class="summary-label">Total Commission Earned</div>
        </div>
    </div>

    <!-- Date Filter Form -->
    <form method="GET" class="filter-form">
        <div>
            <label for="start_date">Start Date</label>
            <input type="date" id="start_date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>">
        </div>
        <div>
            <label for="end_date">End Date</label>
            <input type="date" id="end_date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>">
        </div>
        <button type="submit">Filter</button>
        <?php if (!empty($_GET)): ?>
            <a href="sales_reports.php" style="color:var(--primary); text-decoration:none;">Clear Filter</a>
        <?php endif; ?>
    </form>

    <!-- Export Button -->
    <button class="export-btn" onclick="exportToCSV()">Export to CSV</button>

    <?php if (empty($sales)): ?>
        <p style="text-align:center; color:var(--gray); font-size:1.3rem; margin:3rem 0;">
            No sales recorded yet.
        </p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Agent</th>
                    <th>Price</th>
                    <th>Commission</th>
                    <th>Earned</th>
                    <th>Sale Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($sale['type']) ?><br>
                            <small style="color:var(--gray);"><?= htmlspecialchars($sale['location']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($sale['agent']) ?></td>
                        <td>₹ <?= number_format($sale['price'], 2) ?></td>
                        <td><?= $sale['commission'] ?>%</td>
                        <td class="commission-earned">₹ <?= number_format($sale['commission_earned'], 2) ?></td>
                        <td><?= date('d M Y', strtotime($sale['sale_date'])) ?></td>
                        <td>
                            <a href="viewProperty.php?id=<?= $sale['id'] ?>" style="color:var(--primary); font-weight:600;">
                                View
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

    function exportToCSV() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Property,Agent,Price,Commission,Earned,Sale Date\n";

        <?php foreach ($sales as $sale): ?>
            csvContent += "<?= addslashes($sale['type']) ?>,<?= addslashes($sale['agent']) ?>,<?= $sale['price'] ?>,<?= $sale['commission'] ?>,<?= $sale['commission_earned'] ?>,<?= $sale['sale_date'] ?>\n";
        <?php endforeach; ?>

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "sales_report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

</body>
</html>