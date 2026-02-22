<?php
session_start();
include '../../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$emp_id  = (int)$_GET['id'];
$success = '';
$error   = '';

// ── Handle Edit Employee Info ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'edit_info') {
        $name    = trim($_POST['emp_name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $mobile  = trim($_POST['mobile_no'] ?? '');
        $address = trim($_POST['emp_address'] ?? '');
        $status  = $_POST['status'] ?? 'pending';

        $pdo->prepare("
            UPDATE employees
            SET emp_name=?, email=?, mobile_no=?, emp_address=?, status=?
            WHERE emp_id=?
        ")->execute([$name, $email, $mobile, $address, $status, $emp_id]);
        $success = 'Employee information updated successfully.';
    }

    if ($_POST['action'] === 'pay_commission') {
        $amount = (float)($_POST['amount'] ?? 0);
        $txn    = trim($_POST['transaction_id'] ?? '');
        $notes  = trim($_POST['notes'] ?? '');
        if ($amount > 0) {
            $pdo->prepare("
                INSERT INTO payments (user_id, role, amount, transaction_id, notes, payment_date)
                VALUES (?, 'employee', ?, ?, ?, NOW())
            ")->execute([$emp_id, $amount, $txn, $notes]);
            $success = 'Payment of ₹' . number_format($amount, 2) . ' recorded successfully.';
        } else {
            $error = 'Please enter a valid amount.';
        }
    }
}

// ── Fetch Employee ───────────────────────────────────────────────────
$emp = $pdo->prepare("SELECT * FROM employees WHERE emp_id = ?");
$emp->execute([$emp_id]);
$emp = $emp->fetch(PDO::FETCH_ASSOC);

if (!$emp) {
    header('Location: manage_users.php');
    exit;
}

// ── Commission Stats ─────────────────────────────────────────────────
$s = $pdo->prepare("
    SELECT COALESCE(SUM(
        (CASE WHEN ps.sale_price > 0 THEN ps.sale_price ELSE p.price END)
        * p.commission / 100
    ),0) as earned
    FROM property_sales ps
    JOIN properties p ON ps.property_id = p.property_id
    WHERE ps.emp_id = ?
");
$s->execute([$emp_id]);
$total_earned = (float)$s->fetchColumn();

$p = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE user_id=? AND role='employee'");
$p->execute([$emp_id]);
$total_received = (float)$p->fetchColumn();
$total_pending  = max(0, $total_earned - $total_received);

// ── Sold Properties ──────────────────────────────────────────────────
$sales = $pdo->prepare("
    SELECT ps.sale_id, ps.sale_date, ps.sale_price,
           p.property_id, p.property_name, p.property_type,
           p.location_city, p.location_area, p.price, p.commission
    FROM property_sales ps
    JOIN properties p ON ps.property_id = p.property_id
    WHERE ps.emp_id = ?
    ORDER BY ps.sale_date DESC
");
$sales->execute([$emp_id]);
$sold_properties = $sales->fetchAll(PDO::FETCH_ASSOC);
$total_sold = count($sold_properties);

// ── Rank among employees (by total_earned from sales) ────────────────
$all_emps = $pdo->query("
    SELECT e.emp_id,
           COALESCE(SUM(
               (CASE WHEN ps.sale_price > 0 THEN ps.sale_price ELSE p.price END)
               * p.commission / 100
           ),0) AS earned
    FROM employees e
    LEFT JOIN property_sales ps ON ps.emp_id = e.emp_id
    LEFT JOIN properties p ON ps.property_id = p.property_id
    GROUP BY e.emp_id
    ORDER BY earned DESC
")->fetchAll(PDO::FETCH_ASSOC);

$emp_rank = 'N/A';
foreach ($all_emps as $pos => $ae) {
    if ((int)$ae['emp_id'] === $emp_id) {
        $emp_rank = '#' . ($pos + 1);
        break;
    }
}
$total_employees = count($all_emps);

// ── Payment History ───────────────────────────────────────────────────
$pay_hist = $pdo->prepare("
    SELECT amount, transaction_id, notes, payment_date
    FROM payments
    WHERE user_id=? AND role='employee'
    ORDER BY payment_date DESC
    LIMIT 10
");
$pay_hist->execute([$emp_id]);
$payments = $pay_hist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee: <?= htmlspecialchars($emp['emp_name']) ?> | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    <style>
        :root {
            --bg:      #0f1217;
            --card:    #161b22;
            --green:   #0f6b3a;
            --green-d: #084d2a;
            --gold:    #d4af37;
            --blue:    #3b82f6;
            --red:     #ef4444;
            --text:    #e2e8f0;
            --muted:   #94a3b8;
            --border:  #2d3748;
            --sidebar: 260px;
            --nav:     70px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

        .main    { padding:2rem; }

        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--gold); font-weight:600; text-decoration:none; margin-bottom:1.5rem; font-size:1rem; }
        .back-link:hover { text-decoration:underline; }

        /* Section headings */
        .section-title { font-size:1.25rem; font-weight:700; color:var(--gold); margin:2rem 0 1rem; display:flex; align-items:center; gap:8px; }

        /* Cards */
        .card { background:var(--card); border:1px solid var(--border); border-radius:12px; padding:1.5rem; margin-bottom:1.5rem; }

        /* Profile header */
        .profile-header { display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap; }
        .avatar { width:80px; height:80px; border-radius:50%; background:linear-gradient(135deg,var(--green),var(--gold)); display:flex; align-items:center; justify-content:center; font-size:2rem; color:#000; font-weight:800; flex-shrink:0; }
        .profile-info h2 { font-size:1.7rem; color:var(--text); margin-bottom:4px; }
        .profile-info small { color:var(--muted); }

        .badge { padding:5px 12px; border-radius:20px; font-size:.82rem; font-weight:700; }
        .badge-approved { background:#052e16; color:#4ade80; }
        .badge-pending  { background:#422006; color:#fbbf24; }
        .badge-rejected { background:#450a0a; color:#f87171; }

        /* Info grid */
        .info-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; margin-top:1rem; }
        .info-item label { display:block; font-size:.8rem; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
        .info-item span  { font-size:1rem; color:var(--text); }

        /* Stats row */
        .stats-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; }
        .stat-box { background:var(--card); border-radius:10px; padding:1.2rem; text-align:center; border:1px solid; }
        .stat-box .val { font-size:1.8rem; font-weight:800; margin-bottom:4px; }
        .stat-box .lbl { font-size:.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }
        .box-green { border-color:#22c55e; } .box-green .val { color:#22c55e; }
        .box-blue  { border-color:#3b82f6; } .box-blue  .val { color:#60a5fa; }
        .box-red   { border-color:#ef4444; } .box-red   .val { color:#f87171; }
        .box-gold  { border-color:var(--gold); } .box-gold .val { color:var(--gold); }

        /* Rank display */
        .rank-badge { display:inline-flex; align-items:center; gap:10px; background:linear-gradient(135deg,#1a1a2e,#16213e); border:2px solid var(--gold); border-radius:12px; padding:1rem 1.5rem; }
        .rank-num { font-size:2.5rem; font-weight:900; color:var(--gold); }
        .rank-sub { color:var(--muted); font-size:.9rem; }

        /* Edit form */
        .form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; }
        .form-group { display:flex; flex-direction:column; gap:5px; }
        .form-group label { font-size:.85rem; color:var(--muted); font-weight:600; }
        .form-group input, .form-group select, .form-group textarea {
            background:#0d1117; border:1px solid var(--border); color:var(--text);
            padding:10px 12px; border-radius:8px; font-size:.95rem; font-family:inherit;
            transition:.2s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline:none; border-color:var(--gold);
        }
        .form-group select option { background:#0d1117; }

        .btn { padding:10px 20px; border:none; border-radius:8px; cursor:pointer; font-weight:700; font-size:.95rem; display:inline-flex; align-items:center; gap:7px; text-decoration:none; transition:.2s; }
        .btn-gold   { background:var(--gold); color:#000; }
        .btn-gold:hover   { background:#b8860b; }
        .btn-green  { background:#16a34a; color:#fff; }
        .btn-green:hover  { background:#15803d; }
        .btn-blue   { background:var(--blue); color:#fff; }
        .btn-blue:hover   { background:#2563eb; }

        /* Table */
        .table-wrap { border-radius:10px; overflow:hidden; border:1px solid var(--border); }
        table { width:100%; border-collapse:collapse; }
        th { background:var(--green); color:#fff; padding:13px 15px; text-align:left; font-size:.88rem; text-transform:uppercase; letter-spacing:.4px; }
        td { padding:13px 15px; border-bottom:1px solid var(--border); font-size:.93rem; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:rgba(255,255,255,.02); }
        .no-data { text-align:center; padding:2.5rem; color:var(--muted); font-size:1.1rem; }

        /* Alert */
        .alert { padding:12px 18px; border-radius:8px; margin-bottom:1.5rem; font-weight:600; }
        .alert-success { background:#052e16; color:#4ade80; border:1px solid #166534; }
        .alert-error   { background:#450a0a; color:#fca5a5; border:1px solid #991b1b; }

        /* Modal */
        .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7); z-index:2000; align-items:center; justify-content:center; }
        .modal.open { display:flex; }
        .modal-box { background:#161b22; border:1px solid var(--gold); border-radius:12px; padding:2rem; width:420px; max-width:95%; position:relative; }
        .modal-close { position:absolute; top:15px; right:18px; cursor:pointer; font-size:1.4rem; color:var(--muted); }
        .modal-close:hover { color:var(--text); }
        .modal-title { font-size:1.3rem; font-weight:700; color:var(--gold); margin-bottom:1.2rem; }

        @media(max-width:992px) { .main { padding: 1rem; } }
    </style>
</head>
<body>

<?php include '../../includes/navbaradmin.php'; ?>

<div class="main">
    <a href="manage_users.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Manage Users</a>

    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- ── Profile Card ─────────────────────────────────────────────── -->
    <div class="card">
        <div class="profile-header">
            <div class="avatar"><?= strtoupper(substr($emp['emp_name'], 0, 1)) ?></div>
            <div class="profile-info">
                <h2><?= htmlspecialchars($emp['emp_name']) ?></h2>
                <small><?= htmlspecialchars($emp['prefix'] ?? ('EMP-' . $emp_id)) ?></small><br>
                <span class="badge badge-<?= $emp['status'] ?>" style="margin-top:6px; display:inline-block;">
                    <?= ucfirst($emp['status']) ?>
                </span>
            </div>
            <div class="rank-badge" style="margin-left:auto;">
                <i class="fas fa-trophy" style="color:var(--gold); font-size:1.8rem;"></i>
                <div>
                    <div class="rank-num"><?= $emp_rank ?></div>
                    <div class="rank-sub">of <?= $total_employees ?> employees</div>
                </div>
            </div>
        </div>

        <div class="info-grid" style="margin-top:1.5rem;">
            <div class="info-item">
                <label><i class="fas fa-envelope"></i> Email</label>
                <span><?= htmlspecialchars($emp['email']) ?></span>
            </div>
            <div class="info-item">
                <label><i class="fas fa-phone"></i> Mobile</label>
                <span><?= htmlspecialchars($emp['mobile_no']) ?></span>
            </div>
            <div class="info-item">
                <label><i class="fas fa-calendar-alt"></i> Joining Date</label>
                <span><?= $emp['enrollment_date'] ? date('d M Y', strtotime($emp['enrollment_date'])) : '—' ?></span>
            </div>
            <div class="info-item">
                <label><i class="fas fa-map-marker-alt"></i> Address</label>
                <span><?= htmlspecialchars($emp['emp_address'] ?? '—') ?></span>
            </div>
        </div>
    </div>

    <!-- ── Commission Summary ─────────────────────────────────────────── -->
    <div class="section-title"><i class="fas fa-coins"></i> Commission Summary</div>
    <div class="stats-row" style="margin-bottom:1.5rem;">
        <div class="stat-box box-gold">
            <div class="val"><?= $total_sold ?></div>
            <div class="lbl">Properties Sold</div>
        </div>
        <div class="stat-box box-green">
            <div class="val">₹ <?= number_format($total_earned, 0) ?></div>
            <div class="lbl">Total Earned</div>
        </div>
        <div class="stat-box box-blue">
            <div class="val">₹ <?= number_format($total_received, 0) ?></div>
            <div class="lbl">Total Received</div>
        </div>
        <div class="stat-box box-red">
            <div class="val">₹ <?= number_format($total_pending, 0) ?></div>
            <div class="lbl">Pending</div>
        </div>
        <div class="stat-box" style="border-color:var(--border); display:flex; align-items:center; justify-content:center;">
            <button onclick="document.getElementById('payModal').classList.add('open')" class="btn btn-green">
                <i class="fas fa-money-bill-wave"></i> Record Payment
            </button>
        </div>
    </div>

    <!-- ── Edit Employee Info ─────────────────────────────────────────── -->
    <div class="section-title"><i class="fas fa-edit"></i> Edit Employee Details</div>
    <div class="card">
        <form method="POST">
            <input type="hidden" name="action" value="edit_info">
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="emp_name" value="<?= htmlspecialchars($emp['emp_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="text" name="mobile_no" value="<?= htmlspecialchars($emp['mobile_no']) ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="approved"  <?= $emp['status']==='approved'  ? 'selected':'' ?>>Approved</option>
                        <option value="pending"   <?= $emp['status']==='pending'   ? 'selected':'' ?>>Pending</option>
                        <option value="rejected"  <?= $emp['status']==='rejected'  ? 'selected':'' ?>>Rejected</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:1/-1;">
                    <label>Address</label>
                    <textarea name="emp_address" rows="2"><?= htmlspecialchars($emp['emp_address'] ?? '') ?></textarea>
                </div>
            </div>
            <div style="margin-top:1.2rem;">
                <button type="submit" class="btn btn-gold">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- ── Sold Properties ───────────────────────────────────────────── -->
    <div class="section-title"><i class="fas fa-home"></i> Sold Properties (<?= $total_sold ?>)</div>
    <div class="table-wrap" style="margin-bottom:2rem;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Property</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Sale Price</th>
                    <th>Commission Earned</th>
                    <th>Sale Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sold_properties)): ?>
                    <tr><td colspan="7" class="no-data"><i class="fas fa-box-open" style="font-size:2rem;margin-bottom:10px;display:block;"></i>No properties sold yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($sold_properties as $i => $sp):
                        $actual_price = $sp['sale_price'] > 0 ? $sp['sale_price'] : $sp['price'];
                        $comm_earned  = $actual_price * $sp['commission'] / 100;
                    ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($sp['property_name'] ?: 'Property #'.$sp['property_id']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($sp['property_type'] ?? '—') ?></td>
                            <td><?= htmlspecialchars(($sp['location_city'] ?? '') . ', ' . ($sp['location_area'] ?? '')) ?></td>
                            <td style="color:#60a5fa; font-weight:600;">₹ <?= number_format($actual_price, 0) ?></td>
                            <td style="color:#4ade80; font-weight:700;">₹ <?= number_format($comm_earned, 2) ?></td>
                            <td><?= $sp['sale_date'] ? date('d M Y', strtotime($sp['sale_date'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ── Payment History ───────────────────────────────────────────── -->
    <div class="section-title"><i class="fas fa-history"></i> Payment History</div>
    <div class="table-wrap" style="margin-bottom:2rem;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Amount</th>
                    <th>Transaction ID</th>
                    <th>Notes</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="5" class="no-data">No payments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $i => $pay): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td style="color:#4ade80; font-weight:700;">₹ <?= number_format($pay['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($pay['transaction_id'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($pay['notes'] ?? '—') ?></td>
                            <td><?= $pay['payment_date'] ? date('d M Y, h:i A', strtotime($pay['payment_date'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── Pay Modal ─────────────────────────────────────────────────────── -->
<div id="payModal" class="modal">
    <div class="modal-box">
        <span class="modal-close" onclick="document.getElementById('payModal').classList.remove('open')">&times;</span>
        <div class="modal-title"><i class="fas fa-money-bill-wave"></i> Record Commission Payment</div>
        <p style="color:var(--muted); margin-bottom:1.2rem;">
            Employee: <strong style="color:var(--text);"><?= htmlspecialchars($emp['emp_name']) ?></strong><br>
            Pending: <strong style="color:#f87171;">₹ <?= number_format($total_pending, 2) ?></strong>
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="pay_commission">
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Payment Amount (₹)</label>
                <input type="number" name="amount" step="0.01" min="1"
                       value="<?= $total_pending ?>" required
                       placeholder="Enter amount">
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label>Transaction ID / Ref (Optional)</label>
                <input type="text" name="transaction_id" placeholder="e.g. UPI-123456">
            </div>
            <div class="form-group" style="margin-bottom:1.4rem;">
                <label>Notes</label>
                <textarea name="notes" rows="2" placeholder="Optional notes..."></textarea>
            </div>
            <button type="submit" class="btn btn-green" style="width:100%; justify-content:center;">
                <i class="fas fa-check"></i> Confirm Payment
            </button>
        </form>
    </div>
</div>

<script>
// Close modal on background click
document.getElementById('payModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
</script>
</body>
</html>
