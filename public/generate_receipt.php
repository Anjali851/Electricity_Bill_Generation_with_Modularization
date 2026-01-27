<?php
// public/generate_receipt.php
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/auth.php';

if (!isset($_GET['id'])) die("Access Denied");
$id = (int)$_GET['id'];

$m = db();
$stmt = $m->prepare("SELECT b.*, c.name, c.address, c.contact, c.service_type FROM bills b JOIN customers c ON b.service_no = c.service_no WHERE b.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) die("Receipt not found.");
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt - <?= esc($data['bill_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style> .receipt-box{ max-width:700px; margin:30px auto; padding:30px; background:#fff; border-radius:12px; } </style>
</head>
<body class="bg-light">
<div class="receipt-box shadow">
  <div class="d-flex justify-content-between">
    <div><h2 class="text-primary">UTILITY RECEIPT</h2><p class="small">Invoice: <strong><?= esc($data['bill_no']) ?></strong></p></div>
    <div class="text-end"><span class="badge bg-success p-2 px-3">STATUS: <?= strtoupper(esc($data['status'])) ?></span><p class="small mt-2"><?= date('d-m-Y') ?></p></div>
  </div>
  <hr>
  <div class="row">
    <div class="col-6">
      <h6>Customer</h6><p><strong><?= esc($data['name']) ?></strong><br><?= esc($data['contact']) ?><br><small><?= esc($data['address']) ?></small></p>
    </div>
    <div class="col-6 text-end">
      <h6>Service</h6><p>Service ID: <strong><?= esc($data['service_no']) ?></strong><br><?= strtoupper(esc($data['service_type'])) ?><br>Reading: <?= esc($data['prev_reading']) ?> → <?= esc($data['curr_reading']) ?></p>
    </div>
  </div>

  <table class="table table-bordered mt-3">
    <tr><td>Energy Charges (<?= esc($data['units']) ?> kWh)</td><td class="text-end">₹<?= number_format($data['bill_price'],2) ?></td></tr>
    <tr><td>Previous Arrears</td><td class="text-end text-danger">₹<?= number_format($data['prev_dues'],2) ?></td></tr>
    <?php if ($data['fine'] > 0): ?>
    <tr><td>Late Payment Surcharge</td><td class="text-end text-danger">₹<?= number_format($data['fine'],2) ?></td></tr>
    <?php endif; ?>
    <tr class="table-primary fw-bold"><td>TOTAL PAID</td><td class="text-end">₹<?= number_format($data['total_bill'],2) ?></td></tr>
  </table>

  <div class="text-center mt-3">
    <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    <a href="user_dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>
</div>
</body>
</html>