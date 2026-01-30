<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch all properties  ← this part is NOT changed
$stmt = $pdo->query("SELECT * FROM properties");
$properties = $stmt->fetchAll();
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
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--light);
            margin: 0;
            padding: 0;
            color: var(--dark);
        }

        .container {
            max-width: 1280px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h1 {
            text-align: center;
            color: var(--dark);
            margin-bottom: 2.5rem;
            font-size: 2.3rem;
            font-weight: 600;
        }

        .property-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.6rem;
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
            height: 210px;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: var(--dark);
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

        .status-badge {
            display: inline-block;
            padding: 0.45rem 1.1rem;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-sold     { background: #ffebee; color: #c62828; }
        .status-maintenance,
        .status-under_maintenance { background: #fff3e0; color: #ef6c00; }
        .status-on-hold  { background: #e3f2fd; color: #1565c0; }

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

        @media (max-width: 576px) {
            .property-grid {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 1.9rem;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">

    <h1>All Properties</h1>

    <?php if (empty($properties)): ?>
        <div class="no-properties">
            <i class="fas fa-home"></i>
            <p>No properties found in the database.</p>
            <p>Add your first property using the admin panel.</p>
        </div>
    <?php else: ?>
        <div class="property-grid">
            <?php foreach ($properties as $prop): ?>
                <a href="viewProperty.php?id=<?= $prop['id'] ?>" class="property-card">
                    <div class="card-image">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="card-body">
                        <h2 class="property-type">
                            <?= htmlspecialchars($prop['type'] ?? 'Property') ?>
                        </h2>

                        <div class="property-price">
                            ₹ <?= number_format($prop['price'] ?? 0, 2) ?>
                        </div>

                        <div class="property-location">
                            <?= htmlspecialchars(trim(implode(', ', array_filter([
                                $prop['area'] ?? '',
                                $prop['city'] ?? '',
                                $prop['state'] ?? '',
                                $prop['pincode'] ?? ''
                            ])))) ?: 'Location not specified' ?>
                        </div>

                        <span class="status-badge status-<?= strtolower(str_replace(' ', '_', $prop['status'] ?? 'unknown')) ?>">
                            <?= ucfirst($prop['status'] ?? 'Unknown') ?>
                        </span>

                        <div class="view-btn">
                            View Details →
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>