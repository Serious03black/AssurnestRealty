<?php
session_start();
include '../../includes/db.php';

// Check login
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'driver'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role']; // 'employee' or 'driver'

// 1. Get User Info
if ($role === 'employee') {
    $stmt = $pdo->prepare("SELECT emp_name AS name, monthly_rank, total_properties_sold, commission, referral_bonus FROM employees WHERE emp_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT driver_name AS name, monthly_rank, total_properties_sold, commission, referral_bonus FROM cab_drivers WHERE driver_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$user) {
    echo "User not found.";
    exit;
}

$user_name = $user['name'];
$my_rank   = $user['monthly_rank'] ?? 'Not Ranked';
$my_sales  = $user['total_properties_sold'] ?? 0;
$my_comm   = $user['commission'] ?? 0.00;
//$my_bonus  = $user['referral_bonus'] ?? 0.00; 
// Total earnings can be commission + bonus if applicable, but for now showing commission based on previous code.

// 2. Fetch Available Properties
$stmt = $pdo->query("SELECT * FROM properties WHERE status = 'available' ORDER BY property_id DESC");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_properties_count = count($properties); // Count of available ones ideally? Or all? 
// Let's count all properties for stats
$count_stmt = $pdo->query("SELECT COUNT(*) FROM properties");
$all_props_count = $count_stmt->fetchColumn();


// 3. Leaderboard - Top 3 (Mixed employees and drivers? Or separate? 
// The schema has `monthly_ranking` table. Let's use that if populated, or fallback to sales count.
// For simplicity and robustness, let's query the top sellers from both tables combined if monthly_ranking isn't ready.
// Actually, let's try to query `employees` AND `cab_drivers` union for top sales.

$leaderboard_sql = "
    SELECT name, total_properties_sold, role FROM (
        SELECT emp_name AS name, total_properties_sold, 'employee' as role FROM employees
        UNION ALL
        SELECT driver_name AS name, total_properties_sold, 'driver' as role FROM cab_drivers
    ) as owners
    ORDER BY total_properties_sold DESC
    LIMIT 3
";
$lb_stmt = $pdo->query($leaderboard_sql);
$leaderboard = $lb_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2a5bd7;
            --secondary: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --sidebar-width: 250px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
        }

        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-title { font-size: 2.2rem; margin-bottom: 1rem; color: var(--primary); }

        /* Ladder Ranking */
        .ladder-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin: 2rem 0;
        }

        .ladder-step {
            text-align: center;
            animation: fadeInUp 0.8s ease forwards;
            opacity: 0;
        }

        .ladder-step:nth-child(1) { animation-delay: 0.2s; }
        .ladder-step:nth-child(2) { animation-delay: 0.4s; }
        .ladder-step:nth-child(3) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .podium-place {
            width: 120px;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            border-radius: 10px 10px 0 0;
            padding: 2rem 0;
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            text-align: center;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 10px;
        }

        .podium-1 { height: 140px; background: linear-gradient(#FFD700, #ffca28); color: #333; }
        .podium-2 { height: 110px; background: linear-gradient(#C0C0C0, #b0b0b0); }
        .podium-3 { height: 80px; background: linear-gradient(#CD7F32, #b5894b); }

        .rank-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto -40px;
            position: relative;
            z-index: 10;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .rank-stats {
            color: #555;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        /* Horizontal Cards */
        .horizontal-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2.5rem;
        }

        .short-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }

        .short-card:hover { transform: translateY(-6px); }

        .card-icon { font-size: 2.5rem; margin-bottom: 0.8rem; color: var(--primary); }
        .card-value { font-size: 2.2rem; font-weight: 700; color: var(--dark); }
        .card-label { color: var(--gray); font-size: 1rem; }

        /* Available Properties */
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.25s;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .prop-image {
            height: 180px;
            background: #f0f4f8;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 3rem;
            overflow: hidden;
        }
        
        .prop-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .prop-body { padding: 1.2rem; }
        .prop-title { font-size: 1.3rem; font-weight: 600; margin-bottom: 0.6rem; }
        .prop-price { font-size: 1.5rem; font-weight: 700; color: #d81b60; margin: 0.5rem 0; }
        .prop-loc { color: var(--gray); margin-bottom: 1rem; }
        .prop-btn {
            display: block;
            text-align: center;
            background: var(--primary);
            color: white;
            padding: 0.8rem;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 1rem;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>

<!-- Main Content -->
<div class="main-content">

    <!-- Welcome + Rank -->
    <div class="welcome-section">
        <h1 class="welcome-title">
            Welcome back, <?= htmlspecialchars($user_name) ?>! 🏠
        </h1>

        <!-- Your Current Rank -->
        <div style="
            background: #e3f2fd;
            padding: 1.2rem;
            border-radius: 12px;
            margin: 1.5rem auto;
            max-width: 600px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 600;
            color: #1565c0;
            border-left: 6px solid var(--primary);
        ">
            <i class="fas fa-trophy" style="color:#FFD700; margin-right:0.8rem;"></i>
            Your Monthly Rank: <span style="font-size:1.6rem; color:#000;">#<?= htmlspecialchars($my_rank) ?></span>
        </div>

        <!-- Top 3 Ladder -->
        <div class="ladder-container">
            <?php 
            $podium_heights = [140, 110, 80];
            $podium_colors = ['#FFD700', '#C0C0C0', '#CD7F32'];
            
            foreach ($leaderboard as $i => $top): ?>
                <div class="ladder-step" style="animation-delay: <?= $i * 0.3 ?>s;">
                    <div class="rank-avatar" style="background: <?= $podium_colors[$i] ?>;">
                        <?= $i + 1 ?>
                    </div>
                    <div class="podium-place podium-<?= $i+1 ?>" style="height: <?= $podium_heights[$i] ?>px;">
                        <div>
                            <?= htmlspecialchars($top['name']) ?><br>
                            <small>(<?= $top['role'] ?>)</small>
                        </div>
                    </div>
                    <div class="rank-stats">
                        <?= $top['total_properties_sold'] ?> sales
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Horizontal Short Cards -->
    <div class="horizontal-cards">
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-home"></i></div>
            <div class="card-value"><?= $all_props_count ?></div>
            <div class="card-label">Total Properties</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-key"></i></div>
            <div class="card-value"><?= count($properties) ?></div>
            <div class="card-label">Available Now</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-handshake"></i></div>
            <div class="card-value"><?= $my_sales ?></div>
            <div class="card-label">My Sales</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="card-value">₹ <?= number_format($my_comm, 2) ?></div>
            <div class="card-label">Total Earnings</div>
        </div>
    </div>

    <!-- All Available Properties -->
    <h2 style="margin: 2rem 0 1rem; text-align:center;">Available Properties</h2>
    <div class="properties-grid">
        <?php if (empty($properties)): ?>
            <p style="text-align:center; color:var(--gray); grid-column: 1 / -1;">
                No available properties at the moment.
            </p>
        <?php else: ?>
            <?php foreach ($properties as $prop): ?>
                <div class="property-card">
                    <div class="prop-image">
                         <img src="../../includes/view_image.php?id=<?= $prop['property_id'] ?>&num=1" alt="Property">
                    </div>
                    <div class="prop-body">
                        <div class="prop-title"><?= htmlspecialchars($prop['property_name']) ?></div>
                        <div class="prop-price">₹ <?= number_format($prop['price'], 2) ?></div>
                        <div class="prop-loc">
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($prop['location_city']) ?>
                        </div>
                        <a href="viewProperty.php?id=<?= $prop['property_id'] ?>" class="prop-btn">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    }

    document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);
</script>

</body>
</html>