<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_users.php?error=invalid');
    exit;
}

$user_id = (int)$_GET['id'];

// Prevent viewing own profile in this context (optional)
if ($user_id === $_SESSION['user_id']) {
    header('Location: manage_users.php?error=self');
    exit;
}

// Fetch user details (only existing columns)
$stmt = $pdo->prepare("
    SELECT id, username, role 
    FROM users 
    WHERE id = ? AND role = 'user'
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: manage_users.php?error=not_found');
    exit;
}

// Fetch sold properties & commission
$sold_stmt = $pdo->prepare("
    SELECT p.id, p.type, p.location, p.address, p.price, p.commission, s.sale_date
    FROM sales s
    JOIN properties p ON s.property_id = p.id
    WHERE s.user_id = ?
    ORDER BY s.sale_date DESC
");
$sold_stmt->execute([$user_id]);
$sold_properties = $sold_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total commission
$total_commission = 0;
foreach ($sold_properties as $prop) {
    $comm = ($prop['commission'] / 100) * $prop['price'];
    $total_commission += $comm;
}

$total_sold = count($sold_properties);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile: <?= htmlspecialchars($user['username']) ?> | Assurnest Realty Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --success: #28a745;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
        }

        body { font-family: 'Segoe UI', sans-serif; background: var(--light); margin: 0; padding: 0; color: var(--dark); }
        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px;  margin-top: 1rem;
            margin-left:250px }
        .back-link { display: inline-block; margin-bottom: 1.5rem; color: var(--primary); font-weight: 600; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
              margin-top: 1rem;
            margin-left:250px
        }

        .profile-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            margin-top: -1rem;
            margin-left:250px
        }

        .profile-name { font-size: 2rem; margin: 0; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: #f1f3f5;
            padding: 1.2rem;
            border-radius: 10px;
            text-align: center;
            
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            
        }

        .stat-label { color: var(--gray); font-size: 0.95rem; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th { background: var(--primary); color: white; }
        tr:hover { background: #f8f9fa; }

        .no-sales {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            font-size: 1.2rem;
        }

        /* ────────────────────────────────────────────────
           LEADERBOARD / RANKING SNIPPET (added below)
        ──────────────────────────────────────────────── */
        .leaderboard-section {
            margin: 3rem 0 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .leaderboard-title {
            text-align: center;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-size: 1.6rem;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .leaderboard-item:last-child { border-bottom: none; }

        .rank-badge {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            color: white;
            flex-shrink: 0;
        }

        .rank-1 { background: #FFD700; }
        .rank-2 { background: #C0C0C0; }
        .rank-3 { background: #CD7F32; }
        .rank-other { background: var(--gray); }
    </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="container">

    <a href="manage_users.php" class="back-link">← Back to Manage Users</a>

  <div class="profile-card">
    <div class="profile-header">
        <h1 class="profile-name">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['username']) ?>
        </h1>
        <span style="font-size:1.1rem; color:#2a5bd7;">
            Role: <?= ucfirst($user['role']) ?>
        </span>
    </div>

    <!-- ────────────────────────────────────────────────
         CURRENT USER'S RANK (shows even outside top 10)
    ──────────────────────────────────────────────── -->
    <?php
    // 1. Get this user's sales count
    $user_sales_stmt = $pdo->prepare("
        SELECT COUNT(s.id) AS sales_count
        FROM sales s
        WHERE s.user_id = ?
    ");
    $user_sales_stmt->execute([$user_id]);
    $user_sales = $user_sales_stmt->fetch(PDO::FETCH_ASSOC);
    $user_sales_count = (int)($user_sales['sales_count'] ?? 0);

    // 2. Get full ranked list to calculate exact position
    $rank_stmt = $pdo->prepare("
        SELECT u.id, u.username, COUNT(s.id) AS sales_count
        FROM users u
        LEFT JOIN sales s ON u.id = s.user_id
        WHERE u.role = 'user'
        GROUP BY u.id, u.username
        ORDER BY sales_count DESC
    ");
    $rank_stmt->execute();
    $all_ranks = $rank_stmt->fetchAll(PDO::FETCH_ASSOC);

    $user_rank = 'Not ranked yet';
    $rank_position = null;

    foreach ($all_ranks as $pos => $ranked_user) {
        if ($ranked_user['id'] == $user_id) {
            $rank_position = $pos + 1;
            $user_rank = "#{$rank_position}";
            break;
        }
    }

    // Optional: message if not in top 10
    $rank_message = ($rank_position && $rank_position <= 10)
        ? '<span style="color:#28a745;">(Top 10!)</span>'
        : ($rank_position ? '<span style="color:#555;">(Keep selling!)</span>' : '');
    ?>

    <div style="
        background: #e3f2fd;
        padding: 1.2rem;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 1.8rem;
        font-size: 1.3rem;
        font-weight: 600;
        color: #1565c0;
        border-left: 6px solid var(--primary);
    ">
        <i class="fas fa-trophy" style="margin-right:0.6rem; color:#FFD700;"></i>
        Current Rank: <span style="font-size:1.6rem; color:#000;"><?= htmlspecialchars($user_rank) ?></span>
        <?= $rank_message ?>
        <?php if ($user_sales_count > 0): ?>
            <br><small style="color:#555; font-size:1rem;">
                (<?= $user_sales_count ?> properties sold)
            </small>
        <?php endif; ?>
    </div>

    <!-- Rest of your profile stats (unchanged) -->
    <div class="stat-grid">
        <div class="stat-item">
            <div class="stat-value"><?= $total_sold ?></div>
            <div class="stat-label">Properties Sold</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">₹ <?= number_format($total_commission, 2) ?></div>
            <div class="stat-label">Total Commission Earned</div>
        </div>
    </div>
</div>

    <h2>Sold Properties (<?= $total_sold ?>)</h2>

    <?php if (empty($sold_properties)): ?>
        <div class="no-sales">
            <i class="fas fa-home" style="font-size:3rem; color:#ddd; margin-bottom:1rem;"></i><br>
            This user has not sold any properties yet.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Location / Address</th>
                    <th>Price</th>
                    <th>Commission</th>
                    <th>Sold Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sold_properties as $prop): ?>
                    <tr>
                        <td><?= $prop['id'] ?></td>
                        <td><?= htmlspecialchars($prop['type']) ?></td>
                        <td><?= htmlspecialchars($prop['location'] . ' - ' . $prop['address']) ?></td>
                        <td>₹ <?= number_format($prop['price'], 2) ?></td>
                        <td>₹ <?= number_format(($prop['commission'] / 100) * $prop['price'], 2) ?></td>
                        <td><?= date('d M Y', strtotime($prop['sale_date'])) ?></td>
                        <td>
                            <a href="viewProperty.php?id=<?= $prop['id'] ?>" style="color:var(--primary); font-weight:600;">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ────────────────────────────────────────────────
         LEADERBOARD / RANKING SNIPPET (added here)
    ──────────────────────────────────────────────── -->
    <?php
    // Fetch top sellers (rank by number of properties sold)
    $leader_stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.username, 
            COUNT(s.id) AS sales_count,
            SUM(p.price * p.commission / 100) AS total_commission
        FROM users u
        LEFT JOIN sales s ON u.id = s.user_id
        LEFT JOIN properties p ON s.property_id = p.id
        WHERE u.role = 'user'
        GROUP BY u.id, u.username
        ORDER BY sales_count DESC
        LIMIT 10
    ");
    $leader_stmt->execute();
    $leaders = $leader_stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="leaderboard-section">
        <h2 class="leaderboard-title">
            <i class="fas fa-trophy"></i> Top Performers (by Properties Sold)
        </h2>

        <?php if (empty($leaders)): ?>
            <p style="text-align:center; color:#777; font-size:1.2rem;">
                No sales recorded yet.
            </p>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php $rank = 1; foreach ($leaders as $leader): ?>
                    <div class="leaderboard-item">
                        <div class="rank-badge rank-<?= $rank <= 3 ? $rank : 'other' ?>">
                            <?= $rank ?>
                        </div>
                        <div style="flex: 1;">
                            <strong><?= htmlspecialchars($leader['username']) ?></strong><br>
                            <small style="color:#555;">
                                Sold <?= $leader['sales_count'] ?> properties
                                <?php if ($leader['total_commission'] > 0): ?>
                                    | ₹ <?= number_format($leader['total_commission'], 2) ?> commission
                                <?php endif; ?>
                            </small>
                        </div>
                        <a href="user_profile.php?id=<?= $leader['id'] ?>" class="btn btn-view" style="padding:0.6rem 1.2rem;">
                            View Profile
                        </a>
                    </div>
                    <?php $rank++; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>