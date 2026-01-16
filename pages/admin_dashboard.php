<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all properties
$stmt = $pdo->query("SELECT * FROM properties");
$properties = $stmt->fetchAll();

// Fetch unapproved users
$stmt = $pdo->query("SELECT * FROM users WHERE approved = 0 AND role = 'user'");
$unapproved = $stmt->fetchAll();

// Fetch all users for assigning sales
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' AND approved = 1");
$users = $stmt->fetchAll();

// Leaderboard: users by sales count
$stmt = $pdo->query("SELECT u.username, COUNT(s.id) as sales_count FROM users u LEFT JOIN sales s ON u.id = s.user_id WHERE u.role = 'user' GROUP BY u.id ORDER BY sales_count DESC");
$leaderboard = $stmt->fetchAll();

// Get admin user info
$admin_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$admin_stmt->execute([$_SESSION['user_id']]);
$admin = $admin_stmt->fetch();

// Calculate statistics
$total_properties = count($properties);
$sold_properties = count(array_filter($properties, function($p) { return $p['status'] === 'sold'; }));
$available_properties = count(array_filter($properties, function($p) { return $p['status'] === 'available'; }));
$total_commission = array_sum(array_map(function($p) { 
    return $p['status'] === 'sold' ? ($p['price'] * $p['commission'] / 100) : 0; 
}, $properties));

// Approve user
if (isset($_GET['approve'])) {
    $user_id = $_GET['approve'];
    $pdo->prepare("UPDATE users SET approved = 1 WHERE id = ?")->execute([$user_id]);
    header('Location: admin_dashboard.php');
    exit;
}

// Update property status with optional agent assignment
if (isset($_POST['update_status'])) {
    $prop_id = $_POST['prop_id'];
    $status = $_POST['status'];
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    
    $pdo->prepare("UPDATE properties SET status = ? WHERE id = ?")->execute([$status, $prop_id]);
    
    // If status changed to sold and agent is selected, assign sale
    if ($status === 'sold' && $user_id) {
        // Check if sale already exists for this property
        $check_stmt = $pdo->prepare("SELECT id FROM sales WHERE property_id = ?");
        $check_stmt->execute([$prop_id]);
        
        if ($check_stmt->fetch()) {
            // Update existing sale
            $pdo->prepare("UPDATE sales SET user_id = ? WHERE property_id = ?")->execute([$user_id, $prop_id]);
        } else {
            // Create new sale record
            $pdo->prepare("INSERT INTO sales (property_id, user_id) VALUES (?, ?)")->execute([$prop_id, $user_id]);
        }
    }
    
    header('Location: admin_dashboard.php');
    exit;
}

// Assign sale separately
if (isset($_POST['assign_sale'])) {
    $prop_id = $_POST['prop_id'];
    $user_id = $_POST['user_id'];
    $pdo->prepare("INSERT INTO sales (property_id, user_id) VALUES (?, ?)")->execute([$prop_id, $user_id]);
    $pdo->prepare("UPDATE properties SET status = 'sold' WHERE id = ?")->execute([$prop_id]);
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Assurnest Realty Admin</title>
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
            width: 100%;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            padding: calc(var(--navbar-height) + 20px) 30px 30px;
            transition: all 0.3s;
            /* min-height: 100vh; */
            /* width: calc(100% - var(--sidebar-width)); */
            /* overflow-y: auto; */
            max-height: calc(100vh - var(--navbar-height));
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
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
            /* overflow: hidden; */
            margin-bottom: 30px;
            width: 80vw;
        }
        
        .card1 {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            /* overflow: hidden; */
            margin-bottom: 30px;
            width: 80vw;
        }

        .card-header {
            background: linear-gradient(to right, var(--primary), var(--primary-dark));
            color: white;
            padding: 20px 30px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
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
            min-width: 800px;
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

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        .form-select {
            padding: 8px 12px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            background: white;
            font-size: 14px;
            max-width: 150px;
            width: 100%;
        }

        .agent-select {
            margin-top: 8px;
            padding: 6px 10px;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            background: white;
            font-size: 13px;
            width: 100%;
            display: none;
        }

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

        .dashboard-columns {
            display: grid;
            gap: 30px;
            width: 100%;
        }

        .dashboard-columns > div {
            min-width: 0;
        }

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            width: 70vw;
            /* transform: translateX(50%); */
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

        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            width: 100%;
        }

        .quick-actions-grid button {
            padding: 12px;
            justify-content: center;
            width: 100%;
        }

        .currency {
            font-family: Arial, sans-serif;
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
            .main-content {
                margin-left: 0;
                padding: calc(var(--navbar-height) + 20px) 15px 15px;
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
            }
            
            .welcome-banner h1 {
                font-size: 20px;
            }
            
            table {
                min-width: 600px;
            }
            
            th, td {
                padding: 10px 15px;
            }
            
            .quick-actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: calc(var(--navbar-height) + 15px) 10px 10px;
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
            
            .quick-actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    <?php include '../includes/navbar.php'; ?>

    <div class="main-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($admin['username'] ?? 'Admin'); ?>! 👋</h1>
            <p>Here's what's happening with your real estate business today.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-grid">
            <div class="stat-card" onclick="window.location.href='properties.php'">
                <div class="stat-icon properties">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Properties</h3>
                    <div class="stat-value"><?php echo $total_properties; ?></div>
                </div>
            </div>
            
            <div class="stat-card" onclick="window.location.href='sales.php'">
                <div class="stat-icon sales">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-info">
                    <h3>Properties Sold</h3>
                    <div class="stat-value"><?php echo $sold_properties; ?></div>
                </div>
            </div>
            
            <div class="stat-card" onclick="window.location.href='properties.php?status=available'">
                <div class="stat-icon available">
                    <i class="fas fa-key"></i>
                </div>
                <div class="stat-info">
                    <h3>Available</h3>
                    <div class="stat-value"><?php echo $available_properties; ?></div>
                </div>
            </div>
            
            <div class="stat-card" onclick="window.location.href='commission.php'">
                <div class="stat-icon commission">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Commission</h3>
                    <div class="stat-value"><span class="currency">₹</span> <?php echo number_format($total_commission, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="dashboard-columns">
            <!-- Left Column -->
            <div style="display: flex; flex-direction: column; gap: 30px;width: 100%;">
                <!-- Unapproved Users Card -->
              
                <!-- Leaderboard Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-trophy"></i> Top Performers
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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaderboard as $rank => $l): ?>
                                        <tr>
                                            <td>
                                                <div class="rank-badge rank-<?php echo $rank < 3 ? $rank+1 : 'other'; ?>">
                                                    <?php echo $rank + 1; ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($l['username']); ?></td>
                                            <td><?php echo $l['sales_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <h3>No Sales Data</h3>
                                <p>Leaderboard will appear after sales</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div> <br>

            <!-- Right Column -->
            <div>
                <!-- Recent Properties Card -->
                <div class="card1">
                    <div class="card-header">
                        <div>
                            <i class="fas fa-building"></i> Recent Properties
                        </div>
                        <a href="add_property.php" style="color: white; font-size: 14px;">
                            <i class="fas fa-plus"></i> Add New
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (count($properties) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $recent_properties = array_slice($properties, 0, 5);
                                    foreach ($recent_properties as $p): 
                                        // Check if property has been assigned to an agent
                                        $sale_stmt = $pdo->prepare("SELECT u.username FROM sales s JOIN users u ON s.user_id = u.id WHERE s.property_id = ?");
                                        $sale_stmt->execute([$p['id']]);
                                        $assigned_agent = $sale_stmt->fetch();
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['type']); ?></strong><br>
                                                <small style="color: var(--gray);"><?php echo htmlspecialchars($p['location']); ?></small>
                                                <?php if ($assigned_agent): ?>
                                                    <br><small style="color: var(--primary); font-size: 11px;">
                                                        <i class="fas fa-user-tie"></i> Sold by: <?php echo htmlspecialchars($assigned_agent['username']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="currency">₹</span> <?php echo number_format($p['price'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $p['status']; ?>">
                                                    <?php echo ucfirst($p['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" class="status-form">
                                                    <input type="hidden" name="prop_id" value="<?php echo $p['id']; ?>">
                                                    <select name="status" class="form-select status-select" data-prop-id="<?php echo $p['id']; ?>" style="font-size: 12px;">
                                                        <option value="available" <?php echo $p['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                        <option value="sold" <?php echo $p['status'] === 'sold' ? 'selected' : ''; ?>>Sold</option>
                                                        <option value="maintenance" <?php echo $p['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                    </select>
                                                    
                                                    <!-- Agent selection (shown only when sold is selected) -->
                                                    <select name="user_id" class="agent-select" id="agent-select-<?php echo $p['id']; ?>" style="font-size: 12px;">
                                                        <option value="">Select Agent</option>
                                                        <?php foreach ($users as $u): ?>
                                                            <option value="<?php echo $u['id']; ?>" <?php echo ($assigned_agent && $assigned_agent['username'] == $u['username']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($u['username']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    
                                                    <input type="hidden" name="update_status" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm" style="margin-top: 5px;">
                                                        <i class="fas fa-sync-alt"></i> Update
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-home"></i>
                                <h3>No Properties</h3>
                                <p>Add your first property</p>
                                <a href="add_property.php" class="btn btn-primary" style="margin-top: 15px;">
                                    <i class="fas fa-plus"></i> Add Property
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
              <div class="card ">
                    <div class="card-header">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-user-clock"></i> 
                            <span>Pending Approvals</span>
                            <?php if (count($unapproved) > 0): ?>
                                <span style="background: var(--secondary); color: white; padding: 3px 8px; border-radius: 10px; font-size: 12px;">
                                    <?php echo count($unapproved); ?> new
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($unapproved) > 0): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($unapproved as $u): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                                            <td>
                                                <a href="?approve=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm" onclick="return confirm('Approve this user?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-check"></i>
                                <h3>All Users Approved</h3>
                                <p>No pending approvals</p>
                            </div>
                        <?php endif; ?>
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

        // Show/hide agent selection based on status
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const propId = this.getAttribute('data-prop-id');
                const agentSelect = document.getElementById('agent-select-' + propId);
                
                if (this.value === 'sold') {
                    agentSelect.style.display = 'block';
                    agentSelect.required = true;
                } else {
                    agentSelect.style.display = 'none';
                    agentSelect.required = false;
                }
            });
            
            // Trigger change event on page load if sold is selected
            if (select.value === 'sold') {
                select.dispatchEvent(new Event('change'));
            }
        });

        // Form validation for agent assignment
        document.querySelectorAll('.status-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const statusSelect = this.querySelector('.status-select');
                const agentSelect = this.querySelector('.agent-select');
                
                if (statusSelect.value === 'sold' && (!agentSelect.value || agentSelect.value === '')) {
                    e.preventDefault();
                    alert('Please select an agent for this sale.');
                    return false;
                }
                
                if (!confirm('Update property status?')) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        });

        // Initialize agent selects based on current status
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.status-select').forEach(select => {
                if (select.value === 'sold') {
                    select.dispatchEvent(new Event('change'));
                }
            });
        });
    </script>
</body>
</html>