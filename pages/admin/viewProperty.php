<?php
// ALL LOGIC FIRST – NO OUTPUT BEFORE THIS
session_start();
include '../../includes/db.php';

// Login check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch approved agents (for seller dropdown)
$agents_stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'user' AND approved = 1 ORDER BY username");
$agents_stmt->execute();
$agents = $agents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle DELETE (admin only)
if ($isAdmin && isset($_POST['delete_property']) && isset($_POST['prop_id'])) {
    $prop_id = (int)$_POST['prop_id'];
    $pdo->prepare("DELETE FROM sales WHERE property_id = ?")->execute([$prop_id]);
    $pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([$prop_id]);
    header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
    exit;
}

// Handle STATUS + SELLER update
if (isset($_POST['update_status']) && isset($_POST['prop_id']) && isset($_POST['status'])) {
    $prop_id     = (int)$_POST['prop_id'];
    $new_status  = $_POST['status'];
    $new_user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    // Update property status
    $pdo->prepare("UPDATE properties SET status = ? WHERE id = ?")
        ->execute([$new_status, $prop_id]);

    // Handle seller assignment
    if ($new_status === 'sold' && $new_user_id) {
        // Check if sale already exists
        $check = $pdo->prepare("SELECT id FROM sales WHERE property_id = ?");
        $check->execute([$prop_id]);

        if ($check->fetch()) {
            // Update existing sale
            $pdo->prepare("UPDATE sales SET user_id = ? WHERE property_id = ?")
                ->execute([$new_user_id, $prop_id]);
        } else {
            // Create new sale record
            $pdo->prepare("INSERT INTO sales (property_id, user_id) VALUES (?, ?)")
                ->execute([$prop_id, $new_user_id]);
        }
    } else {
        // Remove seller if status is no longer sold
        $pdo->prepare("DELETE FROM sales WHERE property_id = ?")->execute([$prop_id]);
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit;
}

// Fetch all properties
$stmt = $pdo->query("SELECT * FROM properties");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Properties | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --sidebar-width: 250px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body { font-family: 'Segoe UI', sans-serif; background: var(--light); min-height: 100vh; color: var(--dark); }

        /* .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        } */

        .menu-toggle { font-size: 1.8rem; cursor: pointer; color: var(--primary); display: none; }

       

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 70px;
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .message { padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .deleted  { background: #f8d7da; color: #721c24; }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .property-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: transform 0.25s;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }

        .card-image { height: 200px; background: #f0f4f8; overflow: hidden; }
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s;
        }
        .property-card:hover .card-image img { transform: scale(1.06); }

        .card-image-placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #78909c;
            font-size: 4.5rem;
        }

        .card-body { padding: 1.25rem; }

        .property-type { font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; }
        .property-price { font-size: 1.6rem; font-weight: 700; color: #d81b60; margin: 0.5rem 0; }
        .property-location { color: var(--gray); font-size: 0.95rem; margin-bottom: 1rem; }

        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-sold      { background: #ffebee; color: #c62828; }
        .status-maintenance { background: #fff3e0; color: #ef6c00; }

        .action-buttons {
            margin-top: 1rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.7rem 1.3rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            touch-action: manipulation;
        }

        .btn-view   { background: var(--primary); color: white; }
        .btn-view:hover   { background: var(--primary-dark); }

        .btn-edit   { background: var(--warning); color: #212529; }
        .btn-edit:hover   { background: #e0a800; }

        .btn-delete { background: var(--danger); color: white; }
        .btn-delete:hover { background: #c82333; }

        .status-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
            margin-top: 1rem;
        }

        .status-form select {
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            min-width: 140px;
        }

        .status-form button {
            background: #17a2b8;
            color: white;
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .status-form button:hover { background: #138496; }

        .seller-select {
            display: none;
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #ddd;
            min-width: 180px;
        }

        @media (max-width: 992px) {
            .menu-toggle { display: block; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content, .property-grid { margin-left: 0; }
        }

        @media (max-width: 576px) {
            h1 { font-size: 1.8rem; }
            .action-buttons { flex-direction: column; gap: 0.6rem; }
            .btn { width: 100%; text-align: center; }
            .status-form { flex-direction: column; align-items: stretch; }
            .status-form select, .status-form button, .seller-select { width: 100%; }
        }
    </style>
</head>
<body>

<!-- Navbar with mobile toggle -->
<nav class="navbar">
    <div class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></div>
    <?php include '../../includes/navbar.php'; ?>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</div>

<div class="main-content">

    <?php if (isset($_GET['updated'])): ?>
        <div class="message success">Property updated successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="message deleted">Property deleted successfully!</div>
    <?php endif; ?>

    <h1>All Properties</h1>

    <?php if (empty($properties)): ?>
        <p style="text-align:center; color:var(--gray); font-size:1.3rem;">
            No properties found. <a href="add_property.php">Add New Property</a>
        </p>
    <?php else: ?>
        <div class="property-grid">
            <?php foreach ($properties as $prop): ?>
                <div class="property-card">
                    <div class="card-image">
                        <?php 
                        $mainImage = $prop['image1'] ?? null;
                        if (!empty($mainImage) && filter_var($mainImage, FILTER_VALIDATE_URL)): ?>
                            <img src="<?= htmlspecialchars($mainImage) ?>" alt="Property" loading="lazy">
                        <?php else: ?>
                            <div class="card-image-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <h2 class="property-type"><?= htmlspecialchars($prop['type'] ?? 'Property') ?></h2>
                        <div class="property-price">₹ <?= number_format($prop['price'] ?? 0, 2) ?></div>
                        <div class="property-location">
                            <?= htmlspecialchars(trim(implode(', ', array_filter([
                                $prop['location'] ?? '',
                                $prop['address'] ?? ''
                            ])))) ?: '—' ?>
                        </div>

                        <span class="status-badge status-<?= strtolower($prop['status'] ?? 'unknown') ?>">
                            <?= ucfirst($prop['status'] ?? 'Unknown') ?>
                        </span>

                        <!-- Status + Seller Update Form -->
                        <form method="POST" class="status-form">
                            <input type="hidden" name="prop_id" value="<?= $prop['id'] ?>">
                            <input type="hidden" name="update_status" value="1">

                            <select name="status" class="status-select" data-prop-id="<?= $prop['id'] ?>">
                                <option value="available"     <?= $prop['status'] === 'available'     ? 'selected' : '' ?>>Available</option>
                                <option value="sold"          <?= $prop['status'] === 'sold'          ? 'selected' : '' ?>>Sold</option>
                                <option value="maintenance"   <?= $prop['status'] === 'maintenance'   ? 'selected' : '' ?>>Maintenance</option>
                                <option value="on_hold"       <?= $prop['status'] === 'on_hold'       ? 'selected' : '' ?>>On Hold</option>
                            </select>

                            <!-- Seller dropdown – shown only when sold is selected -->
                            <select name="user_id" class="seller-select" id="seller-<?= $prop['id'] ?>">
                                <option value="">Select Agent/Seller</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>">
                                        <?= htmlspecialchars($agent['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit">Update</button>
                        </form>

                        <?php if ($isAdmin): ?>
                            <div class="action-buttons">
                                <a href="viewProperty.php?id=<?= $prop['id'] ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit_property.php?id=<?= $prop['id'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display:inline;" 
                                      onsubmit="return confirm('Delete this property permanently?');">
                                    <input type="hidden" name="prop_id" value="<?= $prop['id'] ?>">
                                    <input type="hidden" name="delete_property" value="1">
                                    <button type="submit" class="btn btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
// Mobile sidebar toggle
document.getElementById('menuToggle').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('menuToggle');
    if (window.innerWidth <= 992 &&
        !sidebar.contains(e.target) &&
        !toggle.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// Show/hide seller dropdown when status = sold
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const propId = this.getAttribute('data-prop-id');
        const sellerSelect = document.getElementById('seller-' + propId);
        if (this.value === 'sold') {
            sellerSelect.style.display = 'block';
            sellerSelect.required = true;
        } else {
            sellerSelect.style.display = 'none';
            sellerSelect.required = false;
            sellerSelect.value = ''; // clear selection
        }
    });

    // Trigger on page load
    select.dispatchEvent(new Event('change'));
});
</script>

</body>
</html>