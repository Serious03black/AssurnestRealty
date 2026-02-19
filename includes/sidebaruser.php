<?php
// sidebar.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Sidebar Styles */
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --secondary: #ff7e5f;
            --light: #f8f9fa;
            --dark: #343a40;
            --sidebar-width: 250px;
        }

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

        .menu-badge {
            background: var(--secondary);
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: auto;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>pages/user/user_dashboard.php" class="company-logo">
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
            <a href="<?= BASE_URL ?>/pages/user/user_dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'user_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-title">Properties</div>
            <a href="<?= BASE_URL ?>/pages/user/availableProperty.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'available_properties.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Available Properties</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/user/my_sales.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'my_sales.php' ? 'active' : '' ?>">
                <i class="fas fa-handshake"></i>
                <span>My Sales</span>
            </a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'driver'): ?>
            <a href="<?= BASE_URL ?>/pages/user/my_referrals.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'my_referrals.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>My Referrals</span>
            </a>
            <?php endif; ?>
        </div>

        <div class="menu-section">
            <div class="menu-title">Performance</div>
            <a href="<?= BASE_URL ?>/pages/user/commission.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'commission.php' ? 'active' : '' ?>">
                <i class="fas fa-dollar-sign"></i>
                <span>My Commissions</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/user/performance.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'performance.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Performance Stats</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-title">Account</div>
            <!-- <a href="<?= BASE_URL ?>pages/user/profile.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </a> -->
            <!-- <a href="<?= BASE_URL ?>pages/user/settings.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a> -->
            <a href="<?= BASE_URL ?>/pages/user/logout.php" class="menu-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- User Info at Bottom -->
    <div class="user-profile">
        <div class="user-avatar">
            <?php 
                $initial = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
                echo $initial;
            ?>
        </div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Agent') ?></div>
        <div class="user-role">Real Estate Agent</div>
    </div>
</nav>

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
</script>
</body>
</html>