<?php
session_start();
include '../../includes/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit();
}

// Safe defaults
$admin = ['username' => 'Admin'];
$stats = [
    'total_properties'      => 0,
    'sold_properties'       => 0,
    'available_properties'  => 0,
    'total_commission'      => 0,
    'total_employees'       => 0,
    'total_drivers'         => 0,
    'pending_employees'     => 0,
    'pending_drivers'       => 0,
    'top_earners'           => []
];

// Fetch admin name
try {
    $stmt = $pdo->prepare("SELECT admin_name FROM admins WHERE admin_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC) ?: $admin;
} catch (Exception $e) {
    error_log("Admin fetch error: " . $e->getMessage());
}

// Handle approval (employees & cab drivers)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['approve'])) {
    $id     = (int)$_GET['approve'];
    $type   = $_GET['type'] ?? '';

    if ($type === 'employee') {
        $pdo->prepare("UPDATE employees SET status = 'active' WHERE emp_id = ?")
            ->execute([$id]);
        header("Location: admin_dashboard.php?approved=employee");
        exit;
    } elseif ($type === 'driver') {
        $pdo->prepare("UPDATE cab_drivers SET status = 'active' WHERE driver_id = ?")
            ->execute([$id]);
        header("Location: admin_dashboard.php?approved=driver");
        exit;
    }
}

// Fetch dashboard statistics
try {
    // Properties stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties");
    $stats['total_properties'] = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM properties GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['status'] === 'sold')     $stats['sold_properties']      = $row['cnt'];
        if ($row['status'] === 'available') $stats['available_properties'] = $row['cnt'];
    }

    // Commission total (employees + drivers)
    $stmt = $pdo->query("SELECT SUM(commission) as total FROM employees");
    $emp_comm = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT SUM(commission + referral_bonus) as total FROM cab_drivers");
    $driver_comm = $stmt->fetchColumn() ?: 0;

    $stats['total_commission'] = $emp_comm + $driver_comm;

    // Counts
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees");
    $stats['total_employees'] = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM cab_drivers");
    $stats['total_drivers'] = $stmt->fetchColumn() ?: 0;

    // Pending approvals
    $stmt = $pdo->query("SELECT COUNT(*) FROM employees WHERE status != 'active'");
    $stats['pending_employees'] = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM cab_drivers WHERE status != 'active'");
    $stats['pending_drivers'] = $stmt->fetchColumn() ?: 0;

    // Pending employees list
    $stmt = $pdo->query("SELECT emp_id, emp_name, mobile_no, email, enrollment_date 
                         FROM employees WHERE status != 'active' ORDER BY enrollment_date DESC LIMIT 5");
    $pending_employees = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Pending cab drivers list
    $stmt = $pdo->query("SELECT driver_id, driver_name, mobile_no, email, enrollment_date 
                         FROM cab_drivers WHERE status != 'active' ORDER BY enrollment_date DESC LIMIT 5");
    $pending_drivers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Top 3 earners (combined commission + referral)
    $stmt = $pdo->query("
        SELECT 'employee' as type, emp_id as id, emp_name as name, commission + referral_bonus as earnings
        FROM employees
        UNION ALL
        SELECT 'driver' as type, driver_id as id, driver_name as name, commission + referral_bonus as earnings
        FROM cab_drivers
        ORDER BY earnings DESC
        LIMIT 3
    ");
    $stats['top_earners'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent properties (last 5)
    $stmt = $pdo->query("
        SELECT property_id as id, property_type as type, property_name, location_city, location_area, price, status, image1 
        FROM properties 
        ORDER BY property_id DESC 
        LIMIT 5
    ");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        :root {
            --bg-main: #0f1217;
            --bg-card: #161b22;
            --text-main: #e2e8f0;
            --text-muted: #94a3b8;
            --rich-green: #0f6b3a;
            --rich-green-dark: #084d2a;
            --rich-blue: #1e40af;
            --gold: #d4af37;
            --gold-dark: #b8860b;
            --border: #2d3748;
            --shadow: 0 6px 20px rgba(0,0,0,0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
        }

        .sidebar { width: 260px; background: linear-gradient(180deg, var(--rich-green-dark), var(--rich-green)); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.4s ease; box-shadow: 4px 0 25px rgba(0,0,0,0.5); }
        .navbar { position: fixed; top: 0; left: 260px; right: 0; height: 75px; background: linear-gradient(90deg, var(--gold), var(--gold-dark)); color: var(--black); box-shadow: 0 4px 20px rgba(0,0,0,0.4); z-index: 999; display: flex; align-items: center; padding: 0 30px; font-weight: 600; transition: left 0.4s ease; }
        .mobile-menu-btn { display: none; font-size: 1.9rem; cursor: pointer; color: var(--black); }

        .main-content {
            margin-left: 260px;
            margin-top: 75px;
            padding: 2rem;
            transition: margin-left 0.4s ease;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .welcome {
            font-size: 1.9rem;
            font-weight: 700;
            margin: 1.5rem 0 2.5rem;
            color: var(--rich-green);
        }

        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            flex: 1;
            min-width: 180px;
            background: var(--card-bg);
            border: 1px solid var(--gold);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
            text-align: center;
        }

        .stat-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--rich-green);
            margin-bottom: 0.4rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 3rem 0 1.2rem;
            color: var(--rich-blue);
        }

        .properties-row, .earners-row {
            display: flex;
            overflow-x: auto;
            gap: 1.2rem;
            padding-bottom: 1rem;
            scrollbar-width: thin;
        }

        .property-card, .earner-card {
            flex: 0 0 240px;
            background: var(--card-bg);
            border: 1px solid var(--gold);
            border-radius: 12px;
            overflow: hidden;
        }

        .prop-image {
            height: 140px;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prop-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prop-body, .earner-body {
            padding: 1rem;
            text-align: center;
        }

        .prop-type, .earner-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .prop-price, .earner-earnings {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--rich-green);
        }

        .prop-location, .earner-type {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
        }

        .pending-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--gold);
            margin-bottom: 2rem;
        }

        .pending-table th, .pending-table td {
            padding: 1.1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .pending-table th {
            background: var(--rich-green);
            color: white;
            font-weight: 600;
        }

        .btn-approve {
            background: var(--rich-green);
            color: white;
            border: 2px solid var(--gold);
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-approve:hover {
            background: var(--rich-green-dark);
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 1.5rem; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 768px) {
            .stats-row { flex-direction: column; }
            .properties-row, .earners-row { flex-direction: column; }
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

<div class="main-content container">

    <!-- Welcome -->
    <div class="welcome">
        Welcome back, <?= htmlspecialchars($admin['admin_name'] ?? 'Admin') ?>!
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_properties'] ?></div>
            <div class="stat-label">Total Properties</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['available_properties'] ?></div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['sold_properties'] ?></div>
            <div class="stat-label">Sold</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">₹ <?= number_format($stats['total_commission'], 0) ?></div>
            <div class="stat-label">Total Commission</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_employees'] ?></div>
            <div class="stat-label">Employees</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['total_drivers'] ?></div>
            <div class="stat-label">Cab Drivers</div>
        </div>
    </div>

    <!-- Top 3 Earners -->
    <div class="section-title">Top 3 Earners (Commission + Referral)</div>
    <?php if (empty($stats['top_earners'])): ?>
        <div style="text-align:center; color:var(--text-muted); padding:2rem 0;">
            No earnings data available yet.
        </div>
    <?php else: ?>
        <div class="earners-row">
            <?php foreach ($stats['top_earners'] as $earner): ?>
                <div class="earner-card">
                    <div class="earner-body">
                        <div class="earner-name"><?= htmlspecialchars($earner['name']) ?></div>
                        <div class="earner-type"><?= ucfirst($earner['type']) ?></div>
                        <div class="earner-earnings">₹ <?= number_format($earner['earnings'], 0) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Recent Properties -->
    <div class="section-title">Recent Properties</div>
    <?php if (empty($properties)): ?>
        <div style="text-align:center; color:var(--text-muted); padding:3rem 0;">
            No recent properties found.
        </div>
    <?php else: ?>
        <div class="properties-row">
            <?php foreach ($properties as $p): ?>
                <div class="property-card">
                    <div class="prop-image">
                        <?php if (!empty($p['image1'])): ?>
                            <img src="<?= htmlspecialchars($p['image1']) ?>" alt="Property">
                        <?php else: ?>
                            <i class="fas fa-home" style="font-size:3rem;color:#4b5563;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="prop-body">
                        <div class="prop-type"><?= htmlspecialchars($p['property_type'] ?? 'Property') ?></div>
                        <div class="prop-price">₹ <?= number_format($p['price'] ?? 0, 0) ?></div>
                        <div class="prop-location">
                            <?= htmlspecialchars($p['location_city'] ?? '—') ?>, 
                            <?= htmlspecialchars($p['location_area'] ?? '—') ?>
                        </div>
                        <span class="status-badge status-<?= strtolower($p['status'] ?? 'available') ?>">
                            <?= ucfirst($p['status'] ?? 'Available') ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pending Employee Approvals -->
    <div class="section-title">Pending Employee Approvals (<?= $stats['pending_employees'] ?>)</div>
    <?php if (empty($pending_employees)): ?>
        <div style="text-align:center; color:var(--text-muted); padding:2rem 0;">
            No pending employee approvals.
        </div>
    <?php else: ?>
        <table class="pending-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Enrolled</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_employees as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['emp_name']) ?></td>
                        <td><?= htmlspecialchars($e['mobile_no']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>
                        <td><?= date('d M Y', strtotime($e['enrollment_date'])) ?></td>
                        <td>
                            <a href="?approve=<?= $e['emp_id'] ?>&type=employee" class="btn-approve">
                                Approve
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Pending Cab Driver Approvals -->
    <div class="section-title">Pending Cab Driver Approvals (<?= $stats['pending_drivers'] ?>)</div>
    <?php if (empty($pending_drivers)): ?>
        <div style="text-align:center; color:var(--text-muted); padding:2rem 0;">
            No pending cab driver approvals.
        </div>
    <?php else: ?>
        <table class="pending-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Enrolled</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_drivers as $d): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['driver_name']) ?></td>
                        <td><?= htmlspecialchars($d['mobile_no']) ?></td>
                        <td><?= htmlspecialchars($d['email']) ?></td>
                        <td><?= date('d M Y', strtotime($d['enrollment_date'])) ?></td>
                        <td>
                            <a href="?approve=<?= $d['driver_id'] ?>&type=driver" class="btn-approve">
                                Approve
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