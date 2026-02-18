<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: user_dashboard.php');
    exit;
}

$prop_id = (int)$_GET['id'];

// Fetch property details
$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->execute([$prop_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    echo "Property not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['property_name']) ?> | Assurnest Realty</title>
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

        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        .mobile-menu-btn { display: none; font-size: 1.8rem; cursor: pointer; color: var(--dark); }

        .main-content {
            margin-left: var(--sidebar-width);
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

        .property-title { font-size: 2.2rem; margin: 0; color: var(--dark); }

        .price-tag {
            font-size: 2rem;
            font-weight: 700;
            color: #d81b60;
            background: rgba(216, 27, 96, 0.1);
            padding: 0.6rem 1.2rem;
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
            height: 250px;
            background: #eee;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .gallery-item img:hover { transform: scale(1.03); }

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
            border-bottom: 1px solid #eee;
            padding-bottom: 0.8rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray);
            min-width: 160px;
        }

        .detail-value {
            flex: 1;
            font-size: 1.1rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
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

    <a href="user_dashboard.php" class="back-link">← Back to Dashboard</a>

    <div class="property-header">
        <div>
            <h1 class="property-title"><?= htmlspecialchars($property['property_name'] ?? 'Property') ?></h1>
            <div style="color:var(--gray); margin-top:0.5rem; font-size:1.1rem;">
                <?= htmlspecialchars($property['property_type']) ?> • <?= htmlspecialchars($property['location_city']) ?>
            </div>
        </div>
        <div class="price-tag">₹ <?= number_format($property['price'] ?? 0, 2) ?></div>
    </div>

    <!-- Gallery -->
    <div class="gallery">
        <?php for($i=1; $i<=5; $i++): ?>
            <?php if(!empty($property["image$i"])): ?>
                <div class="gallery-item">
                    <img src="../../includes/view_image.php?id=<?= $prop_id ?>&num=<?= $i ?>" alt="Image <?= $i ?>">
                </div>
            <?php endif; ?>
        <?php endfor; ?>
    </div>

    <!-- Details -->
    <div class="details-card">
        <h2>Property Details</h2>

        <div class="detail-row">
            <div class="detail-label">Description</div>
            <div class="detail-value"><?= nl2br(htmlspecialchars($property['description'] ?? 'No description.')) ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge status-<?= strtolower($property['status']) ?>">
                    <?= ucfirst($property['status']) ?>
                </span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Configuration</div>
            <div class="detail-value">
                <?= $property['rooms'] ? $property['rooms'] . ' Rooms' : 'N/A' ?> • 
                <?= $property['kitchens'] ? $property['kitchens'] . ' Kitchens' : 'N/A' ?> • 
                <?= $property['bathrooms'] ? $property['bathrooms'] . ' Bathrooms' : 'N/A' ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Square Footage</div>
            <div class="detail-value">
                <?= $property['sqft'] ? number_format($property['sqft']) . ' sq. ft.' : 'N/A' ?>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Full Location</div>
            <div class="detail-value"><?= htmlspecialchars($property['full_location'] ?? '') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">Location Area</div>
            <div class="detail-value"><?= htmlspecialchars($property['location_area'] ?? '') ?></div>
        </div>

        <div class="detail-row">
            <div class="detail-label">City/State</div>
            <div class="detail-value">
                <?= htmlspecialchars($property['location_city'] ?? '') ?>, 
                <?= htmlspecialchars($property['location_state'] ?? '') ?>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-label">Commission (Agent)</div>
            <div class="detail-value"><?= htmlspecialchars($property['commission'] ?? 0) ?>%</div>
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