<?php
// public/user_dashboard.php
session_start();
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/auth.php';
require_role('user');

$s_no = $_SESSION['service_no'];
$m = db();
$stmt = $m->prepare("SELECT * FROM bills WHERE service_no = ? ORDER BY id DESC");
$stmt->bind_param('s', $s_no);
$stmt->execute();
$res = $stmt->get_result();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>User Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between">
      <h3>Welcome, <?= esc($_SESSION['name']) ?></h3>
      <div>
        <span class="badge bg-light text-dark">Service No: <?= esc($s_no) ?></span>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>
    <div class="card-body">
      <h4>My Billing History</h4>
      <div class="table-responsive">
        <table class="table table-hover table-bordered">
          <thead class="table-dark"><tr><th>Bill No</th><th>Units</th><th>Usage</th><th>Dues</th><th>Total</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
          <tbody>
            <?php if ($res->num_rows): while($r = $res->fetch_assoc()): ?>
            <tr>
              <td><?= esc($r['bill_no']) ?></td>
              <td><?= esc($r['units']) ?> kWh<br><small><?= esc($r['prev_reading']) ?> → <?= esc($r['curr_reading']) ?></small></td>
              <td>₹<?= number_format($r['bill_price'],2) ?></td>
              <td class="text-danger">₹<?= number_format($r['prev_dues'],2) ?><?php if ($r['fine']>0) echo "<br><span class='badge bg-warning text-dark'>+ ₹".number_format($r['fine'],2)."</span>"; ?></td>
              <td><strong>₹<?= number_format($r['total_bill'],2) ?></strong></td>
              <td><?= date('M d, Y', strtotime($r['due_date'])) ?></td>
              <td><span class="badge bg-<?= ($r['status']=='paid')?'success':'danger' ?>"><?= strtoupper($r['status']) ?></span></td>
              <td>
                <?php if ($r['status']=='unpaid'): ?>
                  <a href="payment_action.php?bill_id=<?= $r['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Proceed with payment of ₹<?= $r['total_bill'] ?>?')">Pay Now</a>
                <?php else: ?>
                  <a href="generate_receipt.php?id=<?= $r['id'] ?>" class="btn btn-outline-primary btn-sm" target="_blank">Receipt</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="8" class="text-center">No records found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer small">
      <ul>
        <li>Fixed rate slabs apply based on your Service Category.</li>
        <li>Zero usage results in a ₹25 minimum service charge.</li>
        <li>Unpaid previous balances incur a flat ₹150 fine.</li>
      </ul>
    </div>
  </div>
</div>
</body>
</html>