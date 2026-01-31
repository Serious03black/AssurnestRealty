<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: available_properties.php?error=invalid');
    exit;
}

$prop_id = (int)$_GET['id'];

// Fetch property details
$stmt = $pdo->prepare("
    SELECT id, type, location, address, price, commission, status, 
           image1, image2, image3, image4, video
    FROM properties 
    WHERE id = ?
");
$stmt->execute([$prop_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: available_properties.php?error=not_found');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['type'] ?? 'Property') ?> Details | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --sidebar-width: 250px;
            --navbar-height: 70px;
        }

        body {                 font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
 background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        /* .navbar { position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: var(--navbar-height); background: white; box-shadow: 0 2px 15px rgba(0,0,0,0.1); z-index: 999; display: flex; align-items: center; padding: 0 20px; } */
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        .back-link:hover { text-decoration: underline; }

        .property-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .property-title { font-size: 2.4rem; margin: 0; color: var(--dark); }

        .price-tag {
            font-size: 2.2rem;
            font-weight: 700;
            color: #d81b60;
            background: rgba(216, 27, 96, 0.1);
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2.5rem;
        }

        .gallery-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .gallery-item:hover {
            transform: scale(1.03);
        }

        .gallery-item img, .gallery-item video {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .detail-row {
            display: flex;
            margin-bottom: 1.2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray);
            min-width: 140px;
        }

        .detail-value {
            flex: 1;
            font-size: 1.1rem;
        }

        .status-badge {
            padding: 0.6rem 1.3rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-block;
        }

        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-sold      { background: #ffebee; color: #c62828; }
        .status-maintenance { background: #fff3e0; color: #ef6c00; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 576px) {
            .property-title { font-size: 1.9rem; }
            .price-tag { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>


<div class="main-content">

    <a href="available_properties.php" class="back-link">← Back to Available Properties</a>

    <div class="property-header">
        <h1 class="property-title"><?= htmlspecialchars($property['type'] ?? 'Property') ?></h1>
        <div class="price-tag">₹ <?= number_format($property['price'] ?? 0, 2) ?></div>
    </div>

    <!-- Gallery -->
    <div class="gallery">
        <?php
        $images = array_filter([
            $property['image1'],
            $property['image2'],
            $property['image3'],
            $property['image4']
        ], function($img) {
            return !empty($img) && filter_var($img, FILTER_VALIDATE_URL);
        });

        foreach ($images as $img): ?>
            <div class="gallery-item">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($property['type'] ?? 'Property') ?>" loading="lazy">
            </div>
        <?php endforeach; ?>

        <?php if (!empty($property['video']) && filter_var($property['video'], FILTER_VALIDATE_URL)): ?>
            <div class="gallery-item">
                <video controls>
                    <source src="<?= htmlspecialchars($property['video']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        <?php endif; ?>
    </div>

    <!-- Details -->
    <div class="details-card">
        <h2>Property Details</h2>

        <div class="detail-row">
            <div class="detail-label">Type:</div>
            <div class="detail-value"><?= htmlspecialchars($property['type'] ?? '—') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Location:</div>
            <div class="detail-value"><?= htmlspecialchars($property['location'] ?? '—') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Address:</div>
            <div class="detail-value"><?= nl2br(htmlspecialchars($property['address'] ?? '—')) ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Price:</div>
            <div class="detail-value">₹ <?= number_format($property['price'] ?? 0, 2) ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Commission:</div>
            <div class="detail-value"><?= htmlspecialchars($property['commission'] ?? '—') ?>%</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value">
                <span class="status-badge status-<?= strtolower($property['status'] ?? 'unknown') ?>">
                    <?= ucfirst($property['status'] ?? 'Unknown') ?>
                </span>
            </div>
        </div>
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