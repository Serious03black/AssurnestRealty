<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: available_properties.php?error=invalid');
    exit;
}

$prop_id = (int)$_GET['id'];

// Fetch current property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->execute([$prop_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: available_properties.php?error=not_found');
    exit;
}

// Fetch employees and drivers for the seller dropdown
$sellers = [];
$emp_stmt = $pdo->query("SELECT emp_id AS id, emp_name AS name, 'employee' AS type FROM employees WHERE status = 'approved' ORDER BY emp_name");
$sellers = array_merge($sellers, $emp_stmt->fetchAll(PDO::FETCH_ASSOC));

$driver_stmt = $pdo->query("SELECT driver_id AS id, driver_name AS name, 'driver' AS type FROM cab_drivers WHERE status = 'approved' ORDER BY driver_name");
$sellers = array_merge($sellers, $driver_stmt->fetchAll(PDO::FETCH_ASSOC));

// Get current seller (if sold)
$current_seller = null;
if ($property['status'] === 'sold') {
    $sale_stmt = $pdo->prepare("SELECT emp_id, driver_id FROM property_sales WHERE property_id = ?");
    $sale_stmt->execute([$prop_id]);
    $sale = $sale_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sale) {
        if ($sale['emp_id']) {
            $current_seller = 'employee_' . $sale['emp_id'];
        } elseif ($sale['driver_id']) {
            $current_seller = 'driver_' . $sale['driver_id'];
        }
    }
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type         = trim($_POST['property_type'] ?? '');
    $name         = trim($_POST['property_name'] ?? '');
    $city         = trim($_POST['location_city'] ?? '');
    $area         = trim($_POST['location_area'] ?? '');
    $full_loc     = trim($_POST['full_location'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    
    // New fields
    $sqft         = floatval($_POST['sqft'] ?? 0);
    $kitchens     = intval($_POST['kitchens'] ?? 0);
    $rooms        = intval($_POST['rooms'] ?? 0);
    $bathrooms    = intval($_POST['bathrooms'] ?? 0);

    $price        = floatval($_POST['price'] ?? 0);
    $commission   = floatval($_POST['commission'] ?? 0);
    $status       = $_POST['status'] ?? 'available';
    $seller_value = $_POST['seller_id'] ?? ''; // Format: type_id e.g., employee_1

    // Validation
    if (empty($type))          $errors[] = "Property type is required.";
    if (empty($name))          $errors[] = "Property name is required.";
    if (empty($full_loc))      $errors[] = "Full location is required.";
    if ($price <= 0)           $errors[] = "Price must be greater than 0.";
    if ($commission < 0)       $errors[] = "Commission cannot be negative.";
    if ($status === 'sold' && empty($seller_value)) {
        $errors[] = "Please select a seller when marking as Sold.";
    }

    // Image uploads (Direct BLOB update)
    $imageUpdates = [];
    $imageParams = [];
    
    for ($i = 1; $i <= 5; $i++) {
        $field = "image$i";
        if (!empty($_FILES[$field]['tmp_name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES[$field]['tmp_name']);
            $imageUpdates[] = "$field = ?";
            $imageParams[] = $imgData;
        }
    }

    // Save if no errors
    if (empty($errors)) {
        // Build Update Query
        $sql = "UPDATE properties SET 
                property_type = ?, property_name = ?, location_city = ?, location_area = ?, 
                full_location = ?, description = ?, 
                sqft = ?, kitchens = ?, rooms = ?, bathrooms = ?,
                price = ?, commission = ?, status = ?";
        
        $params = [
            $type, $name, $city, $area, 
            $full_loc, $description, 
            $sqft, $kitchens, $rooms, $bathrooms,
            $price, $commission, $status
        ];

        if (!empty($imageUpdates)) {
            $sql .= ", " . implode(", ", $imageUpdates);
            $params = array_merge($params, $imageParams);
        }

        $sql .= " WHERE property_id = ?";
        $params[] = $prop_id;

        $update_stmt = $pdo->prepare($sql);
        $update_stmt->execute($params);

        // Handle Sale Record
        if ($status === 'sold' && !empty($seller_value)) {
            list($seller_type, $seller_id) = explode('_', $seller_value);
            $emp_id = ($seller_type === 'employee') ? $seller_id : null;
            $driver_id = ($seller_type === 'driver') ? $seller_id : null;
            $sale_date = date('Y-m-d');

            // Check if sale exists
            $check = $pdo->prepare("SELECT sale_id FROM property_sales WHERE property_id = ?");
            $check->execute([$prop_id]);
            
            if ($check->fetch()) {
                $pdo->prepare("UPDATE property_sales SET emp_id = ?, driver_id = ?, sale_price = ? WHERE property_id = ?")
                    ->execute([$emp_id, $driver_id, $price, $prop_id]);
            } else {
                $pdo->prepare("INSERT INTO property_sales (property_id, emp_id, driver_id, sale_date, sale_price) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$prop_id, $emp_id, $driver_id, $sale_date, $price]);
            }
        } else {
            // Remove sale if no longer sold
            $pdo->prepare("DELETE FROM property_sales WHERE property_id = ?")->execute([$prop_id]);
        }

        $success = true;
        // Refresh property data
        $stmt->execute([$prop_id]);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Re-fetch sale info if relevant
        if ($status === 'sold') { 
            // ... (optional, logic above handles specific seller display update on reload)
        } else {
            $current_seller = null;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property #<?= $prop_id ?> | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; padding: 0; color: #333; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        h1 { text-align: center; margin-bottom: 2rem; color: #2c3e50; }
        .form-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-group { margin-bottom: 1.6rem; }
        label { display: block; margin-bottom: 0.6rem; font-weight: 600; color: #444; }
        input[type="text"], input[type="number"], textarea, select {
            width: 100%; padding: 0.9rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem;
        }
        textarea { min-height: 100px; resize: vertical; }
        .error-box { background: #ffebee; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .success-box { background: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; }
        .seller-section { display: none; margin-top: 1rem; }
        button { background: #2a5bd7; color: white; padding: 0.9rem 1.8rem; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1e4bb9; }
        .back-link { display: inline-block; margin: 1rem 0 2rem; color: #2a5bd7; font-weight: 600; text-decoration: none; }
        .current-img-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

  <?php include '../../includes/sidebaradmin.php'; ?>
  <?php include '../../includes/navbar.php'; ?>

<div class="container">

    <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a> <!-- Or viewProperty.php -->

    <h1>Edit Property: <?= htmlspecialchars($property['property_name'] ?? 'Property') ?></h1>

    <?php if ($success): ?>
        <div class="success-box"><strong>Success!</strong> Property updated successfully.</div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">
        
            <div class="form-group">
                <label>Property Name *</label>
                <input type="text" name="property_name" value="<?= htmlspecialchars($property['property_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Property Type *</label>
                <input type="text" name="property_type" value="<?= htmlspecialchars($property['property_type'] ?? '') ?>" required>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>City</label>
                    <input type="text" name="location_city" value="<?= htmlspecialchars($property['location_city'] ?? '') ?>">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Area</label>
                    <input type="text" name="location_area" value="<?= htmlspecialchars($property['location_area'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Full Location / Address *</label>
                <textarea name="full_location" rows="2" required><?= htmlspecialchars($property['full_location'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
            </div>

            <!-- New Fields -->
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <div class="form-group" style="flex:1; min-width:150px;">
                     <label>Square Footage (Sqft)</label>
                     <input type="number" name="sqft" step="0.01" value="<?= $property['sqft'] ?? 0 ?>">
                </div>
                <div class="form-group" style="flex:1; min-width:150px;">
                     <label>Kitchens</label>
                     <input type="number" name="kitchens" value="<?= $property['kitchens'] ?? 0 ?>">
                </div>
                <div class="form-group" style="flex:1; min-width:150px;">
                     <label>Rooms</label>
                     <input type="number" name="rooms" value="<?= $property['rooms'] ?? 0 ?>">
                </div>
                <div class="form-group" style="flex:1; min-width:150px;">
                     <label>Bathrooms</label>
                     <input type="number" name="bathrooms" value="<?= $property['bathrooms'] ?? 0 ?>">
                </div>
            </div>

            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" step="0.01" value="<?= $property['price'] ?? 0 ?>" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Commission (%) *</label>
                    <input type="number" name="commission" step="0.01" value="<?= $property['commission'] ?? 0 ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select id="status" name="status">
                    <option value="available"   <?= $property['status'] === 'available'   ? 'selected' : '' ?>>Available</option>
                    <option value="sold"        <?= $property['status'] === 'sold'        ? 'selected' : '' ?>>Sold</option>
                    <option value="maintenance" <?= $property['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                </select>
            </div>

            <div class="form-group seller-section" id="sellerSection" style="display:<?= $property['status'] === 'sold' ? 'block' : 'none' ?>;">
                <label>Sold By (Employee or Driver) *</label>
                <select name="seller_id" id="seller_id">
                    <option value="">-- Select Seller --</option>
                    <?php foreach ($sellers as $s): ?>
                        <?php $val = $s['type'] . '_' . $s['id']; ?>
                        <option value="<?= $val ?>" <?= ($current_seller === $val) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?> (<?= ucfirst($s['type']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Images (Upload to Replace)</label>
                <div style="display:flex; flex-wrap:wrap; gap:1rem;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div style="width: 150px; text-align:center;">
                            <small>Image <?= $i ?></small>
                            <?php if (!empty($property["image$i"])): ?>
                                <img src="../../includes/view_image.php?id=<?= $prop_id ?>&num=<?= $i ?>" class="current-img-preview">
                            <?php else: ?>
                                <div class="current-img-preview" style="background:#eee; display:flex; align-items:center; justify-content:center; color:#999;">No Img</div>
                            <?php endif; ?>
                            <input type="file" name="image<?= $i ?>" accept="image/*" style="font-size:0.8rem;">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <button type="submit">Update Property</button>
        </form>
    </div>

</div>

<script>
    document.getElementById('status').addEventListener('change', function() {
        const section = document.getElementById('sellerSection');
        const select = document.getElementById('seller_id');
        if (this.value === 'sold') {
            section.style.display = 'block';
            select.setAttribute('required', 'required');
        } else {
            section.style.display = 'none';
            select.removeAttribute('required');
        }
    });
</script>

</body>
</html>