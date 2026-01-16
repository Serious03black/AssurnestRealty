<?php
// navbar.php
?>
<style>
    /* Navbar Styles */
    :root {
        --primary: #2a5bd7;
        --secondary: #ff7e5f;
        --light: #f8f9fa;
        --dark: #343a40;
        --navbar-height: 70px;
        --sidebar-width: 250px;
    }

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

    .navbar-search {
        position: relative;
        width: 300px;
    }

    .search-input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid var(--light-gray);
        border-radius: 8px;
        font-size: 14px;
        background: var(--light);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray);
    }

    .navbar-notifications {
        position: relative;
        cursor: pointer;
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--secondary);
        color: white;
        font-size: 10px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
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

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--dark);
    }

    .user-role {
        font-size: 12px;
        color: var(--gray);
    }

    .user-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        border-radius: 8px;
        min-width: 200px;
        display: none;
        z-index: 1000;
    }

    .user-dropdown.active {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        color: var(--dark);
        text-decoration: none;
        transition: all 0.3s;
        border-bottom: 1px solid var(--light-gray);
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background: var(--light);
        color: var(--primary);
    }

    .quick-actions {
        display: flex;
        gap: 10px;
    }

    .action-btn {
        padding: 8px 15px;
        background: var(--light);
        border: 1px solid var(--light-gray);
        border-radius: 6px;
        color: var(--dark);
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    @media (max-width: 768px) {
        .navbar {
            left: 0;
            padding: 0 20px;
        }
        
        .mobile-menu-btn {
            display: block;
        }
        
        .navbar-search {
            display: none;
        }
        
        .quick-actions {
            display: none;
        }
        
        .user-info {
            display: none;
        }
    }
</style>

<!-- Top Navbar -->
<nav class="navbar" id="navbar">
    <div class="navbar-left">
        <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="page-title">
            <i class="fas fa-tachometer-alt"></i>
            <span>Admin Dashboard</span>
        </div>
        
        <div class="quick-actions">
            <button class="action-btn" onclick="window.location.href='add_property.php'">
                <i class="fas fa-plus"></i> Add Property
            </button>
            <button class="action-btn" onclick="window.location.href='reports.php'">
                <i class="fas fa-chart-bar"></i> Reports
            </button>
        </div>
    </div>
    
    <div class="navbar-right">
        <div class="navbar-search">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Search properties, users...">
        </div>
        
        <div class="navbar-notifications">
            <i class="fas fa-bell"></i>
            <?php if (isset($unapproved) && count($unapproved) > 0): ?>
                <span class="notification-badge"><?php echo count($unapproved); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="navbar-user" id="userDropdownBtn">
            <div class="user-avatar">
                <?php 
                    $adminInitial = isset($admin['username']) ? strtoupper(substr($admin['username'], 0, 1)) : 'A';
                    echo $adminInitial;
                ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($admin['username'] ?? 'Admin'); ?></span>
                <span class="user-role">Administrator</span>
            </div>
            <i class="fas fa-chevron-down"></i>
            
            <div class="user-dropdown" id="userDropdown">
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="settings.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="help.php" class="dropdown-item">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item" style="color: var(--danger);">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    // User dropdown toggle
    document.getElementById('userDropdownBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('userDropdown').classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!document.getElementById('userDropdownBtn').contains(e.target)) {
            document.getElementById('userDropdown').classList.remove('active');
        }
    });

    // Notifications dropdown (you can expand this functionality)
    document.querySelector('.navbar-notifications').addEventListener('click', function() {
        alert('You have <?php echo isset($unapproved) ? count($unapproved) : 0; ?> pending approvals');
    });

    // Search functionality
    document.querySelector('.search-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            alert('Searching for: ' + this.value);
            // Implement actual search here
        }
    });
</script>