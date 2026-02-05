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
    'total_properties' => 0,
    'sold_properties' => 0,
    'available_properties' => 0,
    'maintenance_properties' => 0,
    'total_sales_value' => 0
];
$properties = [];
$pending_users = [];

// Fetch admin name
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC) ?: $admin;
} catch (Exception $e) {
    error_log("Admin fetch error: " . $e->getMessage());
}

// Fetch stats & data
try {
    // Total properties
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties");
    $stats['total_properties'] = $stmt->fetchColumn() ?: 0;

    // Status counts
    $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM properties GROUP BY status");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        switch ($row['status']) {
            case 'available':   $stats['available_properties']   = $row['cnt']; break;
            case 'sold':        $stats['sold_properties']        = $row['cnt']; break;
            case 'maintenance': $stats['maintenance_properties'] = $row['cnt']; break;
        }
    }

    // Total sales value
    $stmt = $pdo->query("SELECT SUM(price) as total FROM properties WHERE status = 'sold'");
    $stats['total_sales_value'] = $stmt->fetchColumn() ?: 0;

    // Recent properties (5 latest)
    $stmt = $pdo->query("SELECT id, type, location, price, status, image1 FROM properties ORDER BY id DESC LIMIT 3");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Pending user approvals
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users WHERE approved = 0 AND role = 'user' ORDER BY created_at DESC LIMIT 6");
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
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
            --rich-blue: #efefef;
            --gold: #d4af37;
            --gold-dark: #b8860b;
            --border: #2d3748;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        .welcome {
            font-size: 1.9rem;
            font-weight: 700;
            margin: 1.5rem 0 2.5rem;
            color: var(--rich-green);
        }

        /* Stats – one line, small cards, golden border */
        .stats-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }
        .main-content {
    margin-left: var(--sidebar-width);   /* Must match sidebar width */
    margin-top: var(--navbar-height);    /* For navbar */
    padding: 2rem 1.5rem;
    min-height: 100vh;                   /* Full height */
    background: var(--light-bg);         /* Helps see if overlapped */
    position: relative;                  /* Prevents z-index issues */
    z-index: 1;                          /* Content above sidebar if needed */
}

        .stat-card {
            flex: 1;
            min-width: 180px;
            background: var(--bg-card);
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

        /* Recent Properties – Amazon-style cards, one row */
        .recent-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 2.5rem 0 1.2rem;
            color: var(--rich-blue);
        }

        .properties-row {
            display: flex;
            overflow-x: auto;
            gap: 1.2rem;
            padding-bottom: 1rem;
            scrollbar-width: thin;
        }

        .property-card {
            flex: 0 0 240px;
            background: var(--bg-card);
            border: 1px solid var(--border);
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

        .prop-body {
            padding: 1rem;
        }

        .prop-type {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.4rem;
        }

        .prop-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--rich-green);
        }

        .prop-location {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
        }

        /* Pending Approvals */
        .pending-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 3rem 0 1.2rem;
            color: var(--rich-blue);
        }

        .pending-table {
            width: 100%;
            border-collapse: collapse;
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
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
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-approve:hover {
            background: var(--rich-green-dark);
        }

        @media (max-width: 992px) {
            .main-content { padding: 1.5rem; }
        }

        @media (max-width: 768px) {
            .stats-row { flex-direction: column; }
            .properties-row { flex-direction: column; }
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
    <?php include '../../includes/navbar.php'; ?>
</nav>

<div class="main-content container">

    <!-- Welcome -->
    <div class="welcome">
        Welcome back, <?= htmlspecialchars($admin['username'] ?? 'Admin') ?>!
    </div>

    <!-- Stats – one horizontal line, small cards, golden border -->
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
            <div class="stat-value">₹ <?= number_format($stats['total_sales_value'], 0) ?></div>
            <div class="stat-label">Sales Value</div>
        </div>
    </div>

    <!-- Recent Properties – compact cards in one row (horizontal scroll on mobile) -->
    <div class="recent-title">Recent Properties</div>
    <?php if (empty($properties)): ?>
        <div style="text-align:center; color:#94a3b8; padding:3rem 0;">
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
                        <div class="prop-type"><?= htmlspecialchars($p['type'] ?? 'Property') ?></div>
                        <div class="prop-price">₹ <?= number_format($p['price'] ?? 0, 0) ?></div>
                        <div class="prop-location"><?= htmlspecialchars($p['location'] ?? '—') ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pending User Approvals -->
    <div class="pending-title">Pending User Approvals</div>
    <?php if (empty($pending_users)): ?>
        <div style="text-align:center; color:#94a3b8; padding:3rem 0;">
            No pending approvals at the moment.
        </div>
    <?php else: ?>
        <table class="pending-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="?approve=<?= $u['id'] ?>" class="btn-approve">
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