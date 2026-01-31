<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: .../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user info
$user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Fetch all properties
$stmt = $pdo->query("SELECT * FROM properties");
$properties = $stmt->fetchAll();

// Fetch properties sold by this user
$stmt = $pdo->prepare("SELECT p.* FROM properties p JOIN sales s ON p.id = s.property_id WHERE s.user_id = ?");
$stmt->execute([$user_id]);
$sold_by_me = $stmt->fetchAll();

// Leaderboard - top sellers
$stmt = $pdo->query("
    SELECT u.username, COUNT(s.id) as sales_count 
    FROM users u 
    LEFT JOIN sales s ON u.id = s.user_id 
    WHERE u.role = 'user' 
    GROUP BY u.id 
    ORDER BY sales_count DESC 
    LIMIT 10
");
$leaderboard = $stmt->fetchAll();

// Calculate statistics
$total_properties = count($properties);
$available_properties = count(array_filter($properties, function($p) { return $p['status'] === 'available'; }));
$my_sales = count($sold_by_me);
$my_commission = array_sum(array_map(function($p) { 
    return ($p['price'] * $p['commission'] / 100); 
}, $sold_by_me));

// Find current user's rank & sales count
$user_rank = 'Not ranked yet';
$user_sales_count = 0;

foreach ($leaderboard as $rank => $l) {
    if ($l['username'] == $user['username']) {
        $user_rank = $rank + 1;
        $user_sales_count = $l['sales_count'];
        break;
    }
}

// If user not in top 10 → calculate exact rank
if ($user_rank === 'Not ranked yet' && $my_sales > 0) {
    $rank_stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 AS rank
        FROM (
            SELECT COUNT(s.id) AS sales_count
            FROM sales s
            JOIN users u ON s.user_id = u.id
            WHERE u.role = 'user'
            GROUP BY u.id
            HAVING sales_count > ?
        ) AS higher
    ");
    $rank_stmt->execute([$my_sales]);
    $rank_row = $rank_stmt->fetch(PDO::FETCH_ASSOC);
    $user_rank = $rank_row['rank'] ?? 'Not ranked';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Assurnest Realty Agent</title>
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
            /* --navbar-height: 70px; */
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
            /* margin-top: var(--navbar-height); */
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
        .ladder-step.your-rank { animation-delay: 0.8s; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .rank-podium {
            width: 120px;
            position: relative;
            margin: 0 auto;
        }

        .podium-place {
            width: 100%;
            background: linear-gradient(135deg, #fff, #f0f0f0);
            border-radius: 10px 10px 0 0;
            padding: 2rem 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            text-align: center;
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

        .rank-name {
            margin-top: 2.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .rank-stats {
            color: #555;
            font-size: 0.9rem;
        }

        .your-rank {
            background: #e3f2fd !important;
            border: 3px solid var(--primary);
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

        @media (max-width: 576px) {
            .horizontal-cards { grid-template-columns: 1fr; }
            .profile-header { flex-direction: column; text-align: center; }
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
            Welcome back, <?= htmlspecialchars($user['username'] ?? 'Agent') ?>! 🏠
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
            Your Current Rank: <span style="font-size:1.6rem; color:#000;"><?= htmlspecialchars($user_rank) ?></span>
        </div>

        <!-- Top 3 Ladder -->
        <div class="ladder-container">
            <?php 
            $top3 = array_slice($leaderboard, 0, 3);
            $podium_heights = [140, 110, 80];
            $podium_colors = ['#FFD700', '#C0C0C0', '#CD7F32'];
            foreach ($top3 as $i => $top): ?>
                <div class="ladder-step" style="animation-delay: <?= $i * 0.3 ?>s;">
                    <div class="rank-avatar" style="background: <?= $podium_colors[$i] ?>;">
                        <?= $i + 1 ?>
                    </div>
                    <div class="podium-place podium-<?= $i+1 ?>" style="height: <?= $podium_heights[$i] ?>px;">
                        <?= htmlspecialchars($top['username']) ?>
                    </div>
                    <div class="rank-stats">
                        <?= $top['sales_count'] ?> sales
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Your Rank (always shown) -->
            <div class="ladder-step your-rank" style="animation-delay: 0.9s;">
                <div class="rank-avatar" style="background: #17a2b8;">
                    <?= $user_rank ?>
                </div>
                <div class="podium-place" style="height:60px; background:#e3f2fd; color:#1565c0;">
                    <?= htmlspecialchars($user['username']) ?> (You)
                </div>
                <div class="rank-stats">
                    <?= $user_sales_count ?> sales
                </div>
            </div>
        </div>
    </div>

    <!-- Horizontal Short Cards -->
    <div class="horizontal-cards">
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-home"></i></div>
            <div class="card-value"><?= $total_properties ?></div>
            <div class="card-label">Total Properties</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-key"></i></div>
            <div class="card-value"><?= $available_properties ?></div>
            <div class="card-label">Available</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-handshake"></i></div>
            <div class="card-value"><?= $my_sales ?></div>
            <div class="card-label">My Sales</div>
        </div>
        <div class="short-card">
            <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="card-value">₹ <?= number_format($my_commission, 2) ?></div>
            <div class="card-label">My Commission</div>
        </div>
    </div>

    <!-- All Available Properties -->
    <h2 style="margin: 2rem 0 1rem; text-align:center;">Available Properties</h2>
    <div class="properties-grid">
        <?php 
        $available_list = array_filter($properties, function($p) { 
            return $p['status'] === 'available'; 
        });
        if (empty($available_list)): ?>
            <p style="text-align:center; color:var(--gray); grid-column: 1 / -1;">
                No available properties at the moment.
            </p>
        <?php else: ?>
            <?php foreach ($available_list as $prop): ?>
                <div class="property-card">
                    <div class="prop-image">
                        <?php if (!empty($prop['image1'])): ?>
                            <img src="<?= htmlspecialchars($prop['image1']) ?>" alt="Property" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-home"></i>
                        <?php endif; ?>
                    </div>
                    <div class="prop-body">
                        <div class="prop-title"><?= htmlspecialchars($prop['type']) ?></div>
                        <div class="prop-price">₹ <?= number_format($prop['price'], 2) ?></div>
                        <div class="prop-loc"><?= htmlspecialchars($prop['location']) ?></div>
                        <a href="viewProperty.php?id=<?= $prop['id'] ?>" class="prop-btn">
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