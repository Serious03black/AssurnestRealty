<?php
session_start();
include '../includes/db.php';

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
$stmt = $pdo->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->execute([$prop_id]);
$property = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$property) {
    header('Location: available_properties.php?error=not_found');
    exit;
}

// Fetch approved agents/sellers
$agents_stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'user' AND approved = 1 ORDER BY username");
$agents_stmt->execute();
$agents = $agents_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current seller (if sold)
$current_seller = null;
if ($property['status'] === 'sold') {
    $seller_stmt = $pdo->prepare("
        SELECT u.id, u.username 
        FROM sales s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.property_id = ?
    ");
    $seller_stmt->execute([$prop_id]);
    $current_seller = $seller_stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type       = trim($_POST['type'] ?? '');
    $location   = trim($_POST['location'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $price      = floatval($_POST['price'] ?? 0);
    $commission = floatval($_POST['commission'] ?? 0);
    $status     = $_POST['status'] ?? 'available';
    $user_id    = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    // Validation
    if (empty($type))          $errors[] = "Property type is required.";
    if (empty($location))      $errors[] = "Location is required.";
    if (empty($address))       $errors[] = "Full address is required.";
    if ($price <= 0)           $errors[] = "Price must be greater than 0.";
    if ($commission < 0)       $errors[] = "Commission cannot be negative.";
    if ($status === 'sold' && !$user_id) {
        $errors[] = "Please select a seller/agent when marking as Sold.";
    }

    // Keep old values if no new upload
    $image1 = $property['image1'];
    $image2 = $property['image2'];
    $image3 = $property['image3'];
    $image4 = $property['image4'];
    $video  = $property['video'];

    // Image uploads (1–4)
    $imageFields = ['image1', 'image2', 'image3', 'image4'];
    foreach ($imageFields as $idx => $field) {
        if (!empty($_FILES[$field]['name']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$field];
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $errors[] = "Image " . ($idx+1) . " must be jpg, jpeg, png, gif or webp.";
            } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
                $errors[] = "Image " . ($idx+1) . " is too large (max 5MB).";
            } else {
                // Cloudinary upload (replace placeholder when ready)
                /*
                $upload = \Cloudinary\Uploader::upload($file['tmp_name'], [
                    'folder' => 'real-estate/properties',
                    'resource_type' => 'image'
                ]);
                ${$field} = $upload['secure_url'];
                */
                ${$field} = "https://via.placeholder.com/600x400?text=Updated+Image+" . ($idx+1);
            }
        }
    }

    // Video upload
    if (!empty($_FILES['video']['name']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['video'];
        $allowed = ['mp4', 'webm', 'mov'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Video must be mp4, webm or mov.";
        } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB
            $errors[] = "Video is too large (max 50MB).";
        } else {
            // Cloudinary video upload
            /*
            $upload = \Cloudinary\Uploader::upload($file['tmp_name'], [
                'folder' => 'real-estate/videos',
                'resource_type' => 'video'
            ]);
            $video = $upload['secure_url'];
            */
            $video = "https://via.placeholder.com/600x400?text=Updated+Video";
        }
    }

    // Save if no errors
    if (empty($errors)) {
        // Update property
        $update_stmt = $pdo->prepare("
            UPDATE properties 
            SET type = ?, location = ?, address = ?, price = ?, commission = ?, status = ?,
                image1 = ?, image2 = ?, image3 = ?, image4 = ?, video = ?
            WHERE id = ?
        ");
        $update_stmt->execute([
            $type, $location, $address, $price, $commission, $status,
            $image1, $image2, $image3, $image4, $video, $prop_id
        ]);

        // Handle seller/sale record
        if ($status === 'sold' && $user_id) {
            $check = $pdo->prepare("SELECT id FROM sales WHERE property_id = ?");
            $check->execute([$prop_id]);

            if ($check->fetch()) {
                $pdo->prepare("UPDATE sales SET user_id = ? WHERE property_id = ?")
                    ->execute([$user_id, $prop_id]);
            } else {
                $pdo->prepare("INSERT INTO sales (property_id, user_id) VALUES (?, ?)")
                    ->execute([$prop_id, $user_id]);
            }
        } else {
            // Remove sale if no longer sold
            $pdo->prepare("DELETE FROM sales WHERE property_id = ?")->execute([$prop_id]);
        }

        $success = true;
        header("Location: available_properties.php?updated=1");
        exit;
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
        input[type="text"], input[type="number"], input[type="url"], textarea, select {
            width: 100%;
            padding: 0.9rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        textarea { min-height: 100px; resize: vertical; }
        input[type="file"] { padding: 0.5rem 0; }
        .current-media { margin-top: 0.6rem; display: flex; flex-wrap: wrap; gap: 1rem; }
        .current-media img, .current-media video {
            max-width: 240px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .error-box {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .success-box {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .seller-section { display: none; margin-top: 1rem; }
        .btn {
            background: #2a5bd7;
            color: white;
            padding: 0.9rem 1.8rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #1e4bb9; }
        .back-link {
            display: inline-block;
            margin: 1rem 0 2rem;
            color: #2a5bd7;
            font-weight: 600;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container">

    <a href="available_properties.php" class="back-link">← Back to All Properties</a>

    <h1>Edit Property #<?= $prop_id ?> - <?= htmlspecialchars($property['type'] ?? 'Property') ?></h1>

    <?php if ($success): ?>
        <div class="success-box">
            <strong>Success!</strong> Property updated successfully.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error-box">
            <strong>Error!</strong>
            <ul style="margin: 0.5rem 0 0 1.2rem; padding-left: 0;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">

            <!-- Basic Info -->
            <div class="form-group">
                <label for="type">Property Type *</label>
                <input type="text" id="type" name="type" value="<?= htmlspecialchars($property['type'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location *</label>
                <input type="text" id="location" name="location" value="<?= htmlspecialchars($property['location'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Full Address *</label>
                <textarea id="address" name="address" rows="3" required><?= htmlspecialchars($property['address'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price (₹) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= $property['price'] ?? 0 ?>" required>
            </div>

            <div class="form-group">
                <label for="commission">Commission (%) *</label>
                <input type="number" id="commission" name="commission" step="0.01" min="0" value="<?= $property['commission'] ?? 0 ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="available"     <?= $property['status'] === 'available'     ? 'selected' : '' ?>>Available</option>
                    <option value="sold"          <?= $property['status'] === 'sold'          ? 'selected' : '' ?>>Sold</option>
                    <option value="maintenance"   <?= $property['status'] === 'maintenance'   ? 'selected' : '' ?>>Maintenance</option>
                    <option value="on_hold"       <?= $property['status'] === 'on_hold'       ? 'selected' : '' ?>>On Hold</option>
                </select>
            </div>

            <!-- Seller/Agent selection (only shown when status = sold) -->
            <div class="form-group seller-section" id="sellerSection" style="display:<?= $property['status'] === 'sold' ? 'block' : 'none' ?>;">
                <label for="user_id">Sold By (Agent/Seller) *</label>
                <select name="user_id" id="user_id" required>
                    <option value="">-- Select Seller --</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>" <?= ($current_seller && $current_seller['id'] == $agent['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($agent['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($current_seller): ?>
                    <small style="color:#555; display:block; margin-top:0.5rem;">
                        Current seller: <strong><?= htmlspecialchars($current_seller['username']) ?></strong>
                    </small>
                <?php endif; ?>
            </div>

            <!-- Images (4) -->
            <div class="form-group">
                <label>Images (replace any or all)</label>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.8rem;">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div style="flex: 1 1 200px; min-width: 200px;">
                            <small>Image <?= $i ?> (current):</small><br>
                            <?php $imgKey = "image$i"; ?>
                            <?php if (!empty($property[$imgKey]) && filter_var($property[$imgKey], FILTER_VALIDATE_URL)): ?>
                                <img src="<?= htmlspecialchars($property[$imgKey]) ?>" alt="Image <?= $i ?>" style="max-width:100%; border-radius:8px; margin-top:0.5rem;">
                            <?php else: ?>
                                <p style="color:#777;">No image <?= $i ?></p>
                            <?php endif; ?>
                            <input type="file" name="image<?= $i ?>" accept="image/*" style="margin-top:0.8rem; width:100%;">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Video -->
            <div class="form-group">
                <label for="video">Upload Video (replace current)</label>
                <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/quicktime">
                <?php if (!empty($property['video']) && filter_var($property['video'], FILTER_VALIDATE_URL)): ?>
                    <div style="margin-top:0.8rem;">
                        <small>Current video:</small><br>
                        <video controls style="max-width:100%; max-height:200px; border-radius:8px; margin-top:0.5rem;">
                            <source src="<?= htmlspecialchars($property['video']) ?>" type="video/mp4">
                            Your browser does not support video.
                        </video>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">Save Changes</button>
        </form>
    </div>

</div>

<script>
// Show/hide seller dropdown when status changes to/from "sold"
document.querySelector('#status').addEventListener('change', function() {
    document.getElementById('sellerSection').style.display = 
        (this.value === 'sold') ? 'block' : 'none';
});
</script>

</body>
</html>