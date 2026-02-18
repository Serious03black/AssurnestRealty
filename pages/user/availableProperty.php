<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'driver'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch only AVAILABLE properties
$stmt = $pdo->prepare("
    SELECT property_id, property_type, property_name, 
           location_city, location_area, full_location, 
           price, commission, image1, status 
    FROM properties 
    WHERE status = 'available' 
    ORDER BY property_id DESC
");
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Properties | Assurnest Realty Agent</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            margin: 0;
            color: var(--dark);
        }

        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 1.5rem;
            transition: margin-left 0.3s ease;
        }

        h1 {
            text-align: center;
            margin-bottom: 2.5rem;
            color: var(--dark);
            font-size: 2.2rem;
        }

        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }

        .property-card {
            background: white;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(0,0,0,0.09);
            transition: all 0.28s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .property-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.15);
        }

        .card-image {
            height: 220px;
            background: #f0f4f8;
            overflow: hidden;
            position: relative;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .property-card:hover .card-image img {
            transform: scale(1.08);
        }

        .card-image-placeholder {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #78909c;
            font-size: 5rem;
        }

        .card-body {
            padding: 1.4rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .property-type {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 0.6rem 0;
            color: var(--primary);
        }

        .property-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: #d81b60;
            margin: 0.4rem 0 1rem 0;
        }

        .property-location {
            color: var(--gray);
            font-size: 0.98rem;
            line-height: 1.45;
            margin-bottom: 1.1rem;
            flex-grow: 1;
        }

        .commission-info {
            font-size: 0.95rem;
            color: var(--dark);
            font-weight: 600;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: #e8f5e9;
            border-radius: 6px;
            text-align: center;
            color: #2e7d32;
        }

        .view-btn {
            margin-top: auto;
            padding: 0.95rem;
            background: var(--primary);
            color: white;
            text-align: center;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.25s;
        }

        .view-btn:hover {
            background: var(--primary-dark);
        }

        .no-properties {
            text-align: center;
            padding: 120px 30px;
            color: var(--gray);
            font-size: 1.45rem;
        }

        .no-properties i {
            font-size: 6rem;
            color: #e0e0e0;
            margin-bottom: 1.8rem;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
        }

        @media (max-width: 576px) {
            .properties-grid { grid-template-columns: 1fr; }
            h1 { font-size: 1.9rem; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>


<div class="main-content">
    
    <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()" style="border:none; background:none; margin-bottom:1rem;">
        <i class="fas fa-bars"></i> Menu
    </button>
    
    <br>

    <h1>Available Properties</h1>

    <?php if (empty($properties)): ?>
        <div class="no-properties">
            <i class="fas fa-home"></i>
            <p>No available properties found at the moment.</p>
            <p>Check back later or contact admin for updates.</p>
        </div>
    <?php else: ?>
        <div class="properties-grid">
            <?php foreach ($properties as $prop): ?>
                <a href="viewProperty.php?id=<?= $prop['property_id'] ?>" class="property-card">
                    <div class="card-image">
                        <?php if (!empty($prop['image1'])): ?>
                            <img src="../../includes/view_image.php?id=<?= $prop['property_id'] ?>&num=1" alt="<?= htmlspecialchars($prop['property_type']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-image-placeholder">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <div class="property-type">
                            <?= htmlspecialchars($prop['property_type'] ?? 'Property') ?>
                        </div>

                        <div class="property-price">
                            ₹ <?= number_format($prop['price'] ?? 0, 2) ?>
                        </div>

                        <div class="property-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?= htmlspecialchars($prop['location_city'] ?? '') ?>, 
                            <?= htmlspecialchars($prop['location_area'] ?? '') ?>
                        </div>

                        <div class="commission-info">
                            Commission: <?= htmlspecialchars($prop['commission'] ?? '—') ?>%
                        </div>

                        <div class="view-btn">
                            View Details →
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    }
    document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);
</script>

</body>
</html>