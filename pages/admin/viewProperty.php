<?php
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

    $pdo->prepare("UPDATE properties SET status = ? WHERE id = ?")
        ->execute([$new_status, $prop_id]);

    if ($new_status === 'sold' && $new_user_id) {
        $check = $pdo->prepare("SELECT id FROM sales WHERE property_id = ?");
        $check->execute([$prop_id]);

        if ($check->fetch()) {
            $pdo->prepare("UPDATE sales SET user_id = ? WHERE property_id = ?")
                ->execute([$new_user_id, $prop_id]);
        } else {
            $pdo->prepare("INSERT INTO sales (property_id, user_id) VALUES (?, ?)")
                ->execute([$prop_id, $new_user_id]);
        }
    } else {
        $pdo->prepare("DELETE FROM sales WHERE property_id = ?")->execute([$prop_id]);
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit;
}

// Fetch all properties
$stmt = $pdo->query("SELECT * FROM properties ORDER BY id DESC");
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Properties | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --dark-bg: #0f1217;
            --card-bg: #161b22;
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
            background: var(--dark-bg);
            color: var(--text-main);
            min-height: 100vh;
            line-height: 1.6;
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

        .message { 
            padding: 1.2rem; 
            border-radius: 10px; 
            margin-bottom: 2rem; 
            text-align: center; 
            font-weight: 600; 
            border: 2px solid var(--gold);
        }
        .success { background: rgba(16,185,129,0.15); color: #10b981; }
        .deleted  { background: rgba(239,68,68,0.15); color: #ef4444; }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .property-card {
            background: var(--card-bg);
            border: 1px solid var(--gold);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .card-image {
            height: 180px;
            background: #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-image-placeholder {
            font-size: 4rem;
            color: #4b5563;
        }

        .card-body {
            padding: 1.2rem;
        }

        .property-type {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--rich-green);
            margin-bottom: 0.5rem;
        }

        .property-price {
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--rich-green);
            margin: 0.4rem 0;
        }

        .property-location {
            font-size: 0.95rem;
            color: var(--text-muted);
            margin-bottom: 0.8rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .status-available { background: rgba(15,107,58,0.15); color: var(--rich-green); border: 1px solid var(--rich-green); }
        .status-sold      { background: rgba(220,38,38,0.15); color: #ef4444; border: 1px solid #ef4444; }
        .status-maintenance { background: rgba(245,158,11,0.15); color: #f59e0b; border: 1px solid #f59e0b; }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.7rem 1.3rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.25s ease;
        }

        .btn-view   { background: var(--rich-blue); color: white; }
        .btn-edit   { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }

        .status-form {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: center;
        }

        .status-form select {
            padding: 0.7rem;
            border-radius: 8px;
            border: 1px solid var(--gold);
            background: var(--card-bg);
            color: var(--text-main);
            min-width: 130px;
        }

        .status-form button {
            background: var(--rich-green);
            color: white;
            padding: 0.7rem 1.4rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .seller-select {
            display: none;
            padding: 0.7rem;
            border-radius: 8px;
            border: 1px solid var(--gold);
            background: var(--card-bg);
            color: var(--text-main);
            min-width: 160px;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 1.8rem 1.2rem; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 576px) {
            .property-grid { grid-template-columns: 1fr; }
            .action-buttons { flex-direction: column; }
            .status-form { flex-direction: column; align-items: stretch; }
            .status-form select, .status-form button, .seller-select { width: 100%; }
        }
        /* From Uiverse.io by iZOXVL */ 
.boton-elegante {
  padding: 8px 15px;
  border: 2px solid #2c2c2c;
  background-color: #1a1a1a;
  color: #ffffff;
  font-size: 1.2rem;
  cursor: pointer;
  border-radius: 30px;
  transition: all 0.4s ease;
  outline: none;
  position: relative;
  overflow: hidden;
  font-weight: bold;
}

.boton-elegante::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(
    circle,
    rgba(255, 255, 255, 0.25) 0%,
    rgba(255, 255, 255, 0) 70%
  );
  transform: scale(0);
  transition: transform 0.5s ease;
}

.boton-elegante:hover::after {
  transform: scale(4);
}

.boton-elegante:hover {
  border-color: #666666;
  background: #292929;
}

    </style>
</head>
<body>

<!-- Navbar with mobile toggle -->
<nav class="navbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include '../../includes/navbar.php'; ?>
</nav>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</nav>

<div class="main-content">

    <?php if (isset($_GET['updated'])): ?>
        <div class="message success">Property updated successfully!</div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="message deleted">Property deleted successfully!</div>
    <?php endif; ?>

    <h1>All Properties</h1>

    <?php if (empty($properties)): ?>
        <div style="text-align:center; color:var(--text-muted); padding:4rem 0; font-size:1.3rem;">
            No properties found yet. <a href="add_property.php" style="color:var(--rich-green); font-weight:600;">Add New Property</a>
        </div>
    <?php else: ?>
        <div class="property-grid">
            <?php foreach ($properties as $prop): ?>
                <div class="property-card">
                    <div class="card-image">
                        <?php 
                        $mainImage = $prop['image1'] ?? null;
                        if (!empty($mainImage) && filter_var($mainImage, FILTER_VALIDATE_URL)): ?>
                            <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($prop['type'] ?? 'Property') ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-image-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="property-type"><?= htmlspecialchars($prop['type'] ?? 'Property') ?></div>
                        <div class="property-price">₹ <?= number_format($prop['price'] ?? 0, 2) ?></div>
                        <div class="property-location">
                            <?= htmlspecialchars(trim(implode(', ', array_filter([
                                $prop['location'] ?? '',
                                $prop['address'] ?? ''
                            ])))) ?: 'Location not specified' ?>
                        </div>

                        <span class="status-badge status-<?= strtolower($prop['status'] ?? 'unknown') ?>">
                            <?= ucfirst($prop['status'] ?? 'Unknown') ?>
                        </span>

                        <!-- Status + Seller Form (admin only) -->
                        <?php if ($isAdmin): ?>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="prop_id" value="<?= $prop['id'] ?>">
                                <input type="hidden" name="update_status" value="1">

                                <select name="status" class="status-select" data-prop-id="<?= $prop['id'] ?>">
                                    <option value="available"     <?= $prop['status'] === 'available'     ? 'selected' : '' ?>>Available</option>
                                    <option value="sold"          <?= $prop['status'] === 'sold'          ? 'selected' : '' ?>>Sold</option>
                                    <option value="maintenance"   <?= $prop['status'] === 'maintenance'   ? 'selected' : '' ?>>Maintenance</option>
                                    <option value="on_hold"       <?= $prop['status'] === 'on_hold'       ? 'selected' : '' ?>>On Hold</option>
                                </select>

                                <select name="user_id" class="seller-select" id="seller-<?= $prop['id'] ?>">
                                    <option value="">Select Agent</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?= $agent['id'] ?>">
                                            <?= htmlspecialchars($agent['username']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit">Update</button>
                            </form>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="action-buttons">

                            <?php if ($isAdmin): ?>
                                <a href="edit_property.php?id=<?= $prop['id'] ?>" class="">
                                   <!-- From Uiverse.io by iZOXVL --> 
<button class="boton-elegante">EDIT</button>

                                </a>

                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this property permanently?');">
                                    <input type="hidden" name="prop_id" value="<?= $prop['id'] ?>">
                                    <input type="hidden" name="delete_property" value="1">
                                   <!-- From Uiverse.io by iZOXVL --> 
<button type="submit" class="boton-elegante">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
// Mobile sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}

document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);

// Show/hide seller dropdown when status = sold
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const propId = this.getAttribute('data-prop-id');
        const sellerSelect = document.getElementById('seller-' + propId);
        sellerSelect.style.display = this.value === 'sold' ? 'block' : 'none';
        sellerSelect.required = this.value === 'sold';
    });

    // Trigger on load
    select.dispatchEvent(new Event('change'));
});
</script>

</body>
</html>