<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    // Only drivers have referrals in this system
    header('Location: user_dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch referred drivers
$stmt = $pdo->prepare("
    SELECT driver_name, email, mobile_no, enrollment_date, total_properties_sold, commission 
    FROM cab_drivers 
    WHERE referral_id = ?
    ORDER BY enrollment_date DESC
");
$stmt->execute([$user_id]);
$referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total stats from referrals
$total_referred = count($referrals);
$total_bonus_earned = 0; 
// Note: total_bonus_earned should conceptually be sum(referral_bonus) from current driver, 
// but here we are iterating referred drivers. 
// The actual bonus is stored in current driver's row 'referral_bonus'. 
// To show 'Bonus eared from THIS driver', we'd need to calculate it or store it. 
// For now, let's just show the referred driver's performance.

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Referrals | Assurnest Realty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #2a5bd7;
            --primary-dark: #1e4bb9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --dark: #2c3e50;
            --success: #28a745;
            --sidebar-width: 250px;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--light); margin: 0; color: var(--dark); }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%); color: white; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; transition: transform 0.3s ease; }
        .main-content { margin-left: var(--sidebar-width); padding: 2rem 1.5rem; transition: margin-left 0.3s ease; }
        
        .header-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .referral-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .driver-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .stat-box { text-align: right; }
        .stat-val { font-size: 1.2rem; font-weight: bold; color: var(--dark); }
        .stat-lbl { font-size: 0.9rem; color: var(--gray); }

        .empty-state {
            text-align: center;
            padding: 4rem;
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .header-box { flex-direction: column; text-align: center; gap: 1rem; }
            .referral-card { flex-direction: column; text-align: center; align-items: center; }
            .stat-box { text-align: center; }
        }
    </style>
</head>
<body>

<nav class="sidebar" id="sidebar">
    <?php include '../../includes/sidebaruser.php'; ?>
</nav>

<div class="main-content">
    <div class="header-box">
        <div>
            <h1>My Referrals</h1>
            <p>Track the drivers you've introduced to the platform.</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2.5rem; font-weight:bold;"><?= $total_referred ?></div>
            <div>Total Referrals</div>
        </div>
    </div>

    <?php if (empty($referrals)): ?>
        <div class="empty-state">
            <i class="fas fa-users" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
            <h2>No Referrals Yet</h2>
            <p>Share your referral code to start earning bonuses!</p>
        </div>
    <?php else: ?>
        <?php foreach ($referrals as $driver): ?>
            <div class="referral-card">
                <div class="driver-info">
                    <div class="avatar">
                        <?= strtoupper(substr($driver['driver_name'], 0, 1)) ?>
                    </div>
                    <div style="text-align:left;">
                        <h3 style="margin:0;"><?= htmlspecialchars($driver['driver_name']) ?></h3>
                        <small style="color:var(--gray);">Joined: <?= date('d M Y', strtotime($driver['enrollment_date'])) ?></small>
                    </div>
                </div>
                
                <div class="stat-box">
                    <div class="stat-lbl">Mobile</div>
                    <div class="stat-val"><?= htmlspecialchars($driver['mobile_no']) ?></div>
                </div>

                <div class="stat-box">
                    <div class="stat-lbl">Sales Made</div>
                    <div class="stat-val"><?= $driver['total_properties_sold'] ?></div>
                </div>

                <div class="stat-box">
                    <div class="stat-lbl">Their Commission</div>
                    <div class="stat-val">₹ <?= number_format($driver['commission'], 2) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('mobile-open');
    }
</script>

</body>
</html>
