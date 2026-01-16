<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
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

// Leaderboard
$stmt = $pdo->query("SELECT u.username, COUNT(s.id) as sales_count FROM users u LEFT JOIN sales s ON u.id = s.user_id WHERE u.role = 'user' GROUP BY u.id ORDER BY sales_count DESC");
$leaderboard = $stmt->fetchAll();

// Calculate statistics
$total_properties = count($properties);
$available_properties = count(array_filter($properties, function($p) { return $p['status'] === 'available'; }));
$my_sales = count($sold_by_me);
$my_commission = array_sum(array_map(function($p) { 
    return ($p['price'] * $p['commission'] / 100); 
}, $sold_by_me));

// Find user rank
$user_rank = 0;
foreach ($leaderboard as $rank => $l) {
    if ($l['username'] == $user['username']) {
        $user_rank = $rank + 1;
        break;
    }
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
        /* Main Content Styles */
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
            --navbar-height: 70px;
            --sidebar-width: 250px;
            --card-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            color: var(--dark);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
        }

        .company-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }

        .company-logo i {
            color: var(--secondary);
            font-size: 28px;
        }

        .company-name {
            font-size: 20px;
            font-weight: 700;
        }

        .company-tagline {
            font-size: 11px;
            opacity: 0.7;
            margin-top: 3px;
            letter-spacing: 1px;
        }

        .sidebar-menu {
            padding: 25px 0;
        }

        .menu-section {
            margin-bottom: 25px;
        }

        .menu-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.5);
            padding: 0 20px 10px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-weight: 500;
            font-size: 14px;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left-color: var(--secondary);
            padding-left: 23px;
        }

        .menu-item.active {
            background: rgba(42, 91, 215, 0.2);
            color: white;
            border-left-color: var(--primary);
        }

        .menu-item i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .menu-item.logout {
            color: #ff9f9f;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 20px;
        }

        .menu-item.logout:hover {
            color: #ff6b6b;
            background: rgba(255,0,0,0.1);
        }

        .user-profile {
            padding: 20px;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
            position: absolute;
            bottom: 0;
            width: 100%;
            background: rgba(0,0,0,0.1);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary), #ff9f9f);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 20px;
            color: white;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            transition: all 0.3s;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--dark);
            cursor: pointer;
            padding: 10px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            color: var(--primary);
        }

    

      
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .navbar-user:hover {
            background: var(--light);
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

     

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            padding: calc(var(--navbar-height) + 20px) 30px 30px;
            transition: all 0.3s;
            min-height: 100vh;
            /* width: calc(100% - var(--sidebar-width)); */
            overflow-y: auto;
            max-height: calc(100vh - var(--navbar-height));
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s;
            cursor: pointer;
            min-width: 0;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
        }

        .stat-icon.properties {
            background: linear-gradient(135deg, #2a5bd7, #1e4bb9);
        }

        .stat-icon.sales {
            background: linear-gradient(135deg, #28a745, #20a14e);
        }

        .stat-icon.available {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        .stat-icon.commission {
            background: linear-gradient(135deg, #ff7e5f, #feb47b);
        }

        .stat-info h3 {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--gray);
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            width: 100%;
        }

        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            /* padding: 20px 30px; */
            font-size: 18px;
            font-weight: 600;
            display: flex;
            /* align-items: center; */
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header i {
            font-size: 20px;
        }

        .card-body {
            padding: 0;
            overflow-x: auto;
            max-width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        thead {
            background-color: var(--light);
        }

        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--light-gray);
            white-space: nowrap;
        }

        td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--light-gray);
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }

        .status-available {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success);
        }

        .status-sold {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }

        .status-maintenance {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning);
        }

        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A0A0A0); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32, #A6692E); }
        .rank-other { background: var(--gray); }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: var(--light-gray);
        }

        .dashboard-columns {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 30px;
            width: 100%;
        }

        .dashboard-columns > div {
            min-width: 0;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: black;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-banner h1 {
            font-size: 24px;
            margin-bottom: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .welcome-banner p {
            opacity: 0.9;
            margin: 0;
        }

        .rank-display {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
            min-width: 150px;
        }

        .rank-number {
            font-size: 32px;
            font-weight: 700;
            color: var(--secondary);
        }

        .rank-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .performance-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            margin-top: 10px;
        }

        @media (max-width: 1200px) {
            .dashboard-columns {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: calc(var(--navbar-height) + 20px) 20px 20px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
            
            .navbar {
                left: 0;
                padding: 0 20px;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
           
            
            .user-info {
                display: none;
            }
            
            .main-content {
                margin-left: 0;
                padding: calc(var(--navbar-height) + 15px) 15px 15px;
                width: 100%;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-columns {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .card-header {
                padding: 15px 20px;
                font-size: 16px;
            }
            
            .welcome-banner {
                padding: 20px;
                flex-direction: column;
                text-align: center;
            }
            
            .welcome-banner h1 {
                font-size: 20px;
            }
            
            table {
                min-width: 500px;
            }
            
            th, td {
                padding: 10px 15px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: calc(var(--navbar-height) + 10px) 10px 10px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 20px;
            }
            
            .stat-value {
                font-size: 20px;
            }
            
            .welcome-banner {
                padding: 15px;
            }
            
            .welcome-banner h1 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="user_dashboard.php" class="company-logo">
                <i class="fas fa-building"></i>
                <div>
                    <div class="company-name">Assurnest Realty</div>
                    <div class="company-tagline">AGENT PORTAL</div>
                </div>
            </a>
        </div>

        <div class="sidebar-menu">
            <div class="menu-section">
                <div class="menu-title">Dashboard</div>
                <a href="user_dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Properties</div>
                <a href="available_properties.php" class="menu-item">
                    <i class="fas fa-home"></i>
                    <span>Available Properties</span>
                </a>
                <a href="my_sales.php" class="menu-item">
                    <i class="fas fa-handshake"></i>
                    <span>My Sales</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Performance</div>
                <a href="commission.php" class="menu-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>My Commissions</span>
                </a>
                <a href="performance.php" class="menu-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Performance Stats</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-title">Account</div>
                <a href="profile.php" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="menu-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

    
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar" id="navbar">
        <div class="navbar-left">
            <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                <span>Agent Dashboard</span>
            </div>
        </div>
        
        <div class="navbar-right">
            
            <div class="navbar-user">
                <div class="user-avatar-small">
                    <?php 
                        $userInitial = isset($user['username']) ? strtoupper(substr($user['username'], 0, 1)) : 'U';
                        echo $userInitial;
                    ?>
                </div>
                <div class="user-info">
                    <span class="user-name-small"><?php echo htmlspecialchars($user['username'] ?? 'Agent'); ?></span>
                    <span class="user-role-small">Real Estate Agent</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($user['username'] ?? 'Agent'); ?>! 🏠</h1>
                <p>Track your sales performance and discover new opportunities.</p>
                <div class="performance-badge">
                    <i class="fas fa-trophy" style="color: var(--secondary);"></i>
                    <span>Current Rank: #<?php echo $user_rank ?: 'N/A'; ?></span>
                </div>
            </div>
            <div class="rank-display">
                <div class="rank-number">#<?php echo $user_rank ?: '-'; ?></div>
                <div class="rank-label">Overall Rank</div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon properties">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Properties</h3>
                    <div class="stat-value"><?php echo $total_properties; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon sales">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-info">
                    <h3>My Sales</h3>
                    <div class="stat-value"><?php echo $my_sales; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon available">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stat-info">
                    <h3>Available</h3>
                    <div class="stat-value"><?php echo $available_properties; ?></div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon commission">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>My Commission</h3>
                    <div class="stat-value">₹ <?php echo number_format($my_commission, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-columns">
            <!-- Left Column -->
            <div>
                <!-- Available Properties Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-home"></i> Available Properties
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($properties) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Commission</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $available_properties_list = array_filter($properties, function($p) { 
                                        return $p['status'] === 'available'; 
                                    });
                                    $recent_available = array_slice($available_properties_list, 0, 5);
                                    foreach ($recent_available as $p): 
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['type']); ?></td>
                                            <td><?php echo htmlspecialchars($p['location']); ?></td>
                                            <td>₹ <?php echo number_format($p['price'], 2); ?></td>
                                            <td><?php echo $p['commission']; ?>%</td>
                                            <td>
                                                <span class="status-badge status-<?php echo $p['status']; ?>">
                                                    <?php echo ucfirst($p['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($recent_available)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 30px; color: var(--gray);">
                                                <i class="fas fa-home" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                                No available properties at the moment
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-home"></i>
                                <h3>No Properties Available</h3>
                                <p>Check back later for new listings</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><br>

                <!-- My Sales Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-handshake"></i> My Sales
                        </div>
                        <span style="font-size: 14px; opacity: 0.9;">
                            Total: <?php echo $my_sales; ?> properties
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (count($sold_by_me) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Price</th>
                                        <th>Commission</th>
                                        <th>Earned</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sold_by_me as $p): 
                                        $commission_earned = ($p['price'] * $p['commission'] / 100);
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['type']); ?></strong><br>
                                                <small style="color: var(--gray);"><?php echo htmlspecialchars($p['location']); ?></small>
                                            </td>
                                            <td>₹ <?php echo number_format($p['price'], 2); ?></td>
                                            <td><?php echo $p['commission']; ?>%</td>
                                            <td style="color: var(--success); font-weight: 600;">
                                                ₹ <?php echo number_format($commission_earned, 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-handshake"></i>
                                <h3>No Sales Yet</h3>
                                <p>Start selling properties to see your earnings here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <!-- Leaderboard Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-trophy"></i> Agent Leaderboard
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($leaderboard) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Agent</th>
                                        <th>Sales</th>
                                        <th>Performance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $top_agents = array_slice($leaderboard, 0, 10);
                                    foreach ($top_agents as $rank => $l): 
                                        $isCurrentUser = $l['username'] == $user['username'];
                                    ?>
                                        <tr style="<?php echo $isCurrentUser ? 'background: rgba(42, 91, 215, 0.05);' : ''; ?>">
                                            <td>
                                                <div class="rank-badge rank-<?php echo $rank < 3 ? $rank+1 : 'other'; ?>">
                                                    <?php echo $rank + 1; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($l['username']); ?>
                                                <?php if ($isCurrentUser): ?>
                                                    <span style="color: var(--primary); font-size: 12px;">(You)</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $l['sales_count']; ?></td>
                                            <td>
                                                <?php 
                                                    $max_sales = $top_agents[0]['sales_count'];
                                                    $width = $max_sales > 0 ? ($l['sales_count'] / $max_sales) * 100 : 0;
                                                ?>
                                                <div style="background: var(--light-gray); height: 8px; border-radius: 4px; width: 100%; max-width: 150px;">
                                                    <div style="background: <?php echo $rank < 3 ? 'linear-gradient(to right, var(--primary), var(--secondary))' : 'var(--gray)'; ?>; height: 100%; border-radius: 4px; width: <?php echo $width; ?>%;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <h3>No Sales Data</h3>
                                <p>Leaderboard will appear once agents start making sales</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-chart-bar"></i> Performance Summary
                        </div>
                    </div>
                    <div class="card-body" style="padding: 20px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="text-align: center; padding: 15px; background: rgba(42, 91, 215, 0.05); border-radius: 8px;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--primary);"><?php echo $my_sales; ?></div>
                                <div style="font-size: 12px; color: var(--gray);">Properties Sold</div>
                            </div>
                            <div style="text-align: center; padding: 15px; background: rgba(40, 167, 69, 0.05); border-radius: 8px;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--success);">₹ <?php echo number_format($my_commission, 0); ?></div>
                                <div style="font-size: 12px; color: var(--gray);">Total Commission</div>
                            </div>
                            <div style="text-align: center; padding: 15px; background: rgba(255, 193, 7, 0.05); border-radius: 8px;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--warning);"><?php echo $user_rank ?: '-'; ?></div>
                                <div style="font-size: 12px; color: var(--gray);">Current Rank</div>
                            </div>
                            <div style="text-align: center; padding: 15px; background: rgba(255, 126, 95, 0.05); border-radius: 8px;">
                                <div style="font-size: 24px; font-weight: 700; color: var(--secondary);"><?php echo $available_properties; ?></div>
                                <div style="font-size: 12px; color: var(--gray);">Available Now</div>
                            </div>
                        </div>
                        
                        <?php if ($my_sales > 0 && $my_commission > 0): ?>
                            <div style="margin-top: 25px; padding-top: 25px; border-top: 1px solid var(--light-gray);">
                                <div style="font-size: 14px; color: var(--gray); margin-bottom: 10px;">Average per Sale</div>
                                <div style="font-size: 20px; font-weight: 700; color: var(--primary);">
                                    ₹ <?php echo number_format($my_commission / $my_sales, 2); ?>
                                    <span style="font-size: 14px; color: var(--gray); font-weight: normal;"> average commission per property</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(event.target) && 
                (!mobileMenuBtn || !mobileMenuBtn.contains(event.target))) {
                sidebar.classList.remove('mobile-open');
            }
        });

        // Search functionality
        document.querySelector('.search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = this.value.trim();
                if (query) {
                    alert('Searching for properties: ' + query);
                    // Implement actual search here
                }
            }
        });
    </script>
</body>
</html>