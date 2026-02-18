<?php
session_start();
$message = '';
$success = false;

include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_type   = trim($_POST['property_type'] ?? '');
    $property_name   = trim($_POST['property_name'] ?? '');
    $location_state  = trim($_POST['location_state'] ?? '');
    $location_city   = trim($_POST['location_city'] ?? '');
    $location_area   = trim($_POST['location_area'] ?? '');
    $full_location   = trim($_POST['full_location'] ?? '');
    $price           = floatval($_POST['price'] ?? 0);
    $commission      = floatval($_POST['commission'] ?? 0);
    $status          = $_POST['status'] ?? 'available';
    $description     = trim($_POST['description'] ?? '');
    
    // New fields
    $sqft            = floatval($_POST['sqft'] ?? 0);
    $kitchens        = intval($_POST['kitchens'] ?? 0);
    $rooms           = intval($_POST['rooms'] ?? 0);
    $bathrooms       = intval($_POST['bathrooms'] ?? 0);

    if (empty($property_type) || empty($property_name) || empty($full_location) || $price <= 0) {
        $message = "Please fill all required fields correctly.";
        $success = false;
    } else {
        try {
            // Prepare image data
            $image_data = [null, null, null, null, null];
            
            for ($i = 1; $i <= 5; $i++) {
                $key = "image{$i}";
                if (!empty($_FILES[$key]['tmp_name']) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                    $image_data[$i-1] = file_get_contents($_FILES[$key]['tmp_name']);
                }
            }

            // Insert property with BLOBs
            $stmt = $pdo->prepare("
                INSERT INTO properties (
                    property_type, property_name, location_state, location_city, location_area, 
                    full_location, price, commission, description, status, admin_id,
                    sqft, kitchens, rooms, bathrooms,
                    image1, image2, image3, image4, image5
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $property_type, $property_name, $location_state, $location_city, $location_area,
                $full_location, $price, $commission, $description, $status, $_SESSION['user_id'],
                $sqft, $kitchens, $rooms, $bathrooms,
                $image_data[0], $image_data[1], $image_data[2], $image_data[3], $image_data[4]
            ]);
            
            $prop_id = $pdo->lastInsertId();

            $message = "Property added successfully! (ID: $prop_id)";
            $success = true;

        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $success = false;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Property | Assurnest Realty Admin</title>
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
            --shadow: 0 10px 30px rgba(0,0,0,0.4);
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
            padding: 2.5rem 2rem;
            min-height: calc(100vh - 75px);
            transition: margin-left 0.4s ease;
        }

        .form-container {
            max-width: 1000px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid var(--gold);
            box-shadow: var(--shadow);
        }

        .form-header {
            background: linear-gradient(135deg, var(--rich-green), var(--rich-blue));
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2.3rem;
            margin-bottom: 0.6rem;
        }

        .form-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .form-body {
            padding: 2.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.8rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group {
            margin-bottom: 1.4rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 600;
            color: var(--text-main);
            font-size: 1.05rem;
        }

        .form-control,
        textarea.form-control,
        select.form-control,
        input[type="file"] {
            width: 100%;
            padding: 1rem 1.3rem;
            background: #1e293b;
            border: 2px solid var(--gold);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        textarea.form-control:focus,
        select.form-control:focus {
            outline: none;
            border-color: var(--rich-green);
            box-shadow: 0 0 0 4px rgba(15,107,58,0.25);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .status-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .status-badge {
            padding: 0.9rem 1.6rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--gold);
            background: transparent;
            color: var(--text-main);
        }

        .status-badge.active,
        .status-badge:hover {
            background: var(--rich-green);
            color: white;
            border-color: var(--rich-green);
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: block;
            margin-top: 0.5rem;
        }

        .file-btn {
            background: var(--rich-blue);
            color: white;
            padding: 1rem 1.6rem;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            transition: all 0.3s ease;
            border: 2px solid var(--gold);
            width: 100%;
            justify-content: center;
            font-weight: 600;
        }

        .file-btn:hover {
            background: var(--rich-blue-dark);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 3.5rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 1.1rem 2.2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid var(--gold);
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
        }

        .btn-primary {
            background: var(--rich-green);
            color: white;
        }

        .btn-primary:hover {
            background: var(--rich-green-dark);
        }

        .btn-secondary {
            background: #1e293b;
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background: #253549;
        }

        .message {
            padding: 1.4rem;
            border-radius: 12px;
            margin-bottom: 2.5rem;
            text-align: center;
            font-weight: 600;
            border: 2px solid var(--gold);
        }

        .message.success { background: rgba(16,185,129,0.15); color: #10b981; }
        .message.error   { background: rgba(239,68,68,0.15); color: #ef4444; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 2rem 1.5rem; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .mobile-menu-btn { display: block; }
            .form-actions { flex-direction: column; gap: 1.2rem; }
            .btn { width: 100%; justify-content: center; }
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaradmin.php'; ?>
</nav>

<!-- Navbar -->
<nav class="navbar">
    <button class="mobile-menu-btn" id="mobileMenuBtn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <?php include '../../includes/navbar.php'; ?>
</nav>

<div class="main-content">

    <div class="form-container">
        <div class="form-header">
            <h1>Add New Property</h1>
            <p>Enter premium property details to expand your portfolio</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-body">
            <form method="POST" id="propertyForm" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="property_type">
                            <i class="fas fa-tag"></i> Property Type
                        </label>
                        <input type="text" class="form-control" id="property_type" name="property_type" required placeholder="e.g., Villa, Apartment, Flat, Plot">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="property_name">
                            <i class="fas fa-home"></i> Property Name / Title
                        </label>
                        <input type="text" class="form-control" id="property_name" name="property_name" required placeholder="e.g., Sunshine Villa, Ocean View Apartment">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location_state">
                            <i class="fas fa-map"></i> State
                        </label>
                        <input type="text" class="form-control" id="location_state" name="location_state" required placeholder="e.g., Maharashtra">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location_city">
                            <i class="fas fa-city"></i> City
                        </label>
                        <input type="text" class="form-control" id="location_city" name="location_city" required placeholder="e.g., Mumbai, Nashik">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location_area">
                            <i class="fas fa-map-marker-alt"></i> Area / Locality
                        </label>
                        <input type="text" class="form-control" id="location_area" name="location_area" required placeholder="e.g., Bandra West, College Road">
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label" for="full_location">
                            <i class="fas fa-address-card"></i> Full Location / Address
                        </label>
                        <textarea class="form-control" id="full_location" name="full_location" required placeholder="Complete address including landmark, pincode..."></textarea>
                    </div>

                    <div class="form-group full-width">
                         <label class="form-label" for="description">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea class="form-control" id="description" name="description" placeholder="Enter property description..."></textarea>
                    </div>

                    <!-- New Fields -->
                    <div class="form-group">
                        <label class="form-label" for="sqft">
                            <i class="fas fa-ruler-combined"></i> Square Footage (Sqft)
                        </label>
                        <input type="number" class="form-control" id="sqft" name="sqft" step="0.01" placeholder="e.g. 1200">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="kitchens">
                            <i class="fas fa-utensils"></i> Kitchens
                        </label>
                        <input type="number" class="form-control" id="kitchens" name="kitchens" placeholder="e.g. 1">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="rooms">
                            <i class="fas fa-door-open"></i> Rooms
                        </label>
                        <input type="number" class="form-control" id="rooms" name="rooms" placeholder="e.g. 3">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bathrooms">
                            <i class="fas fa-bath"></i> Bathrooms
                        </label>
                        <input type="number" class="form-control" id="bathrooms" name="bathrooms" placeholder="e.g. 2">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price">
                            <i class="fas fa-rupee-sign"></i> Price (₹)
                        </label>
                        <input type="number" class="form-control" id="price" name="price" step="0.01" required placeholder="0.00" oninput="updatePriceDisplay()">
                        <div class="price-display" id="priceDisplay">₹ 0.00</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="commission">
                            <i class="fas fa-percentage"></i> Commission (%)
                        </label>
                        <input type="number" class="form-control" id="commission" name="commission" step="0.01" required placeholder="0.00" oninput="updateCommissionDisplay()">
                        <div class="commission-display" id="commissionDisplay">0% (₹ 0.00)</div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label">
                            <i class="fas fa-signal"></i> Property Status
                        </label>
                        <div class="status-badges">
                            <label class="status-badge available">
                                <input type="radio" name="status" value="available" class="hidden-radio" checked> 
                                Available
                            </label>
                            <label class="status-badge sold">
                                <input type="radio" name="status" value="sold" class="hidden-radio">
                                Sold
                            </label>
                            <label class="status-badge maintainence">
                                <input type="radio" name="status" value="maintainence" class="hidden-radio">
                                Maintenance
                            </label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label class="form-label"><i class="fas fa-images"></i> Property Images (up to 5)</label>
                        <div class="file-input-wrapper">
                            <div class="file-btn"><i class="fas fa-upload"></i> Main Image (Cover)</div>
                            <input type="file" name="image1" accept="image/*">
                        </div><br>
                        <div class="file-input-wrapper">
                            <div class="file-btn"><i class="fas fa-upload"></i> Image 2</div>
                            <input type="file" name="image2" accept="image/*">
                        </div><br>
                        <div class="file-input-wrapper">
                            <div class="file-btn"><i class="fas fa-upload"></i> Image 3</div>
                            <input type="file" name="image3" accept="image/*">
                        </div><br>
                        <div class="file-input-wrapper">
                            <div class="file-btn"><i class="fas fa-upload"></i> Image 4</div>
                            <input type="file" name="image4" accept="image/*">
                        </div><br>
                        <div class="file-input-wrapper">
                            <div class="file-btn"><i class="fas fa-upload"></i> Image 5</div>
                            <input type="file" name="image5" accept="image/*">
                        </div>
                    </div>

                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>pages/admin/admin_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Property
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
// Mobile sidebar toggle
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}

document.getElementById('mobileMenuBtn')?.addEventListener('click', toggleSidebar);

// Price & commission live preview
function updatePriceDisplay() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    document.getElementById('priceDisplay').textContent = '₹ ' + price.toLocaleString('en-IN', {minimumFractionDigits: 2});
    updateCommissionDisplay();
}

function updateCommissionDisplay() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const commission = parseFloat(document.getElementById('commission').value) || 0;
    const amount = (price * commission) / 100;
    document.getElementById('commissionDisplay').textContent = `${commission}% (₹ ${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})})`;
}

// Initialize
document.addEventListener('DOMContentLoaded', updatePriceDisplay);
</script>

</body>
</html>