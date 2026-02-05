<?php
?>
<nav class="sidebar" id="sidebar">
  <div class="sidebar-header" sty>
    <a href="<?= BASE_URL ?>pages/admin/admin_dashboard.php" class="company-logo">
        <img src="https://res.cloudinary.com/dz37qeiet/image/upload/v1770035747/logo_nwhqeg.png" 
             alt="Assurnest Realty Logo">
    </a>
</div>
</div>
</div>
</div>
        </a>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-title">Dashboard</div>
            <a href="<?= BASE_URL ?>/pages/admin/admin_dashboard.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-title">Properties</div>
            <a href="<?= BASE_URL ?>/pages/admin/add_property.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === '../pages/admin/add_property.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Add Property</span>
            </a>
            <a href="<?= BASE_URL ?>/pages/admin/viewProperty.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'viewProperty.php' ? 'active' : '' ?>">
                <i class="fas fa-list"></i>
                <span>All Properties</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-title">Users & Sales</div>
            <a href="<?= BASE_URL ?>/pages/admin/manage_users.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'manage_users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
                <?php if (isset($unapproved) && count($unapproved) > 0): ?>
                    <span class="menu-badge"><?= count($unapproved) ?> new</span>
                <?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/pages/admin/sales.php" class="menu-item <?= basename($_SERVER['PHP_SELF']) === 'sales_reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Sales Reports</span>
            </a>
        </div>
    </div>
</nav>

<style>
    .logo-image {
    width: 80px;
    height: auto;
    display: block;
    border-radius: 8px;
}
    :root {
        --rich-green: #0f6b3a;
        --rich-green-dark: #084d2a;
        --rich-blue: #1e40af;
        --rich-blue-dark: #172554;
        --gold: #d4af37;
        --gold-dark: #b8860b;
        --black: #111111;
        --sidebar-width: 260px;
        --transition: all 0.35s ease;
    }

    .sidebar {
        width: var(--sidebar-width);
        background: #0E2432;
        color: #b8860b;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        transition: transform var(--transition);
        box-shadow: 6px 0 30px rgba(0,0,0,0.35);
        overflow-y: auto;
    }

    .sidebar-header {
        /* padding: 2.2rem 1.8rem; */
        border-bottom: 1px solid rgba(255,255,255,0.12);
        background: rgba(0,0,0,0.18);
    }

    .company-logo {
        display: flex;
        align-items: center;
        gap: 14px;
        color: white;
        text-decoration: none;
        font-size: 1.75rem;
        font-weight: 800;
        display: flex;
        justify-content: center;
    }
    .company-logo img{
    width: 40%;
    height: auto;
    display: block;
    }

    .company-logo i {
        color: var(--gold);
        font-size: 2.2rem;
    }

    .company-name {
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .company-tagline {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-top: 4px;
    }

    .sidebar-menu {
        padding: 1.5rem 0;
    }

    .menu-section {
        margin-bottom: 1.8rem;
    }

    .menu-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: rgba(255,255,255,0.6);
        padding: 0 1.8rem 0.8rem;
        margin-bottom: 0.6rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 1.1rem 1.8rem;
        color: #b8860b;
        text-decoration: none;
        transition: var(--transition);
        border-left: 4px solid transparent;
        font-weight: 500;
        font-size: 1.05rem;
    }

    .menu-item:hover,
    .menu-item.active {
        background: rgba(255,255,255,0.12);
        color: white;
        border-left-color: var(--gold);
        transform: translateX(6px);
    }


    .menu-badge {
        background: var(--rich-blue);
        color: white;
        font-size: 0.75rem;
        padding: 0.3rem 0.8rem;
        border-radius: 12px;
        margin-left: auto;
        font-weight: 600;
    }

    /* Admin Profile at Bottom */
    .admin-profile {
        padding: 1.8rem;
        border-top: 1px solid rgba(255,255,255,0.12);
        background: rgba(0,0,0,0.2);
        text-align: center;
        margin-top: auto;
    }

    .admin-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--rich-blue), var(--rich-green));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.8rem;
        font-size: 1.8rem;
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    }

    .admin-name {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.3rem;
    }

    .admin-role {
        font-size: 0.85rem;
        opacity: 0.8;
    }

    @media (max-width: 992px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
        }
        .sidebar.mobile-open {
            transform: translateX(0);
        }
    }
</style>

<script>
// Mobile sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    
    if (window.innerWidth <= 992 && 
        !sidebar.contains(event.target) && 
        (!mobileMenuBtn || !mobileMenuBtn.contains(event.target))) {
        sidebar.classList.remove('mobile-open');
    }
});
</script>