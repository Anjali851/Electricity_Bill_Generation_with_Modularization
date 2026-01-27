<?php
// public/employee_dashboard.php
session_start();
require_once __DIR__ . '/../src/inc/auth.php';
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/billing.php';

require_role('employee');

$msg = '';
$customer = null;
$generated = null;

if (isset($_POST['search_customer'])) {
    $s_no = strtoupper(trim($_POST['service_no']));
    $m = db();
    $stmt = $m->prepare("SELECT * FROM customers WHERE service_no = ? LIMIT 1");
    $stmt->bind_param('s', $s_no);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    if (!$customer) $msg = "<div class='alert alert-danger'>Service Number $s_no not found.</div>";
}

if (isset($_POST['generate_bill'])) {
    $s_no = strtoupper(trim($_POST['service_no']));
    $curr = intval($_POST['curr_reading']);
    $prev = intval($_POST['prev_reading']);
    $category = $_POST['category_name'] ?? 'household';

    $res = create_bill($s_no, $prev, $curr, $category);
    if ($res['success']) {
        $generated = $res['bill'];
    } else {
        $msg = "<div class='alert alert-danger'>".$res['error']."</div>";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Employee Billing</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style> body{background:#f4f7f6;} .bill-card{border-top:5px solid #198754;} </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark mb-4">
  <div class="container">
    <span class="navbar-brand">Staff Billing Portal</span>
    <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="container pb-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <?php if ($generated): ?>
        <div class="card bill-card p-4 mb-5">
            <div class="text-center mb-3">
                <h3>Invoice Generated</h3>
                <p>No: <?= esc($generated['bill_no']) ?></p>
            </div>
            <table class="table">
                <tr><td>Service No</td><td><?= esc($generated['service_no']) ?></td></tr>
                <tr><td>Units</td><td><?= esc($generated['units']) ?> kWh</td></tr>
                <tr><td>Energy Charges</td><td>₹<?= number_format($generated['bill_price'],2) ?></td></tr>
                <tr><td>Arrears</td><td>₹<?= number_format($generated['prev_dues'],2) ?></td></tr>
                <tr class="table-success"><td>Total</td><td>₹<?= number_format($generated['total'],2) ?></td></tr>
            </table>
            <div class="text-center"><a href="employee_dashboard.php" class="btn btn-outline-secondary">Next Entry</a></div>
        </div>
      <?php else: ?>
        <div class="card p-4">
          <h4>Reading Entry & Bill Generation</h4>
          <?= $msg ?>
          <form method="POST" class="mb-4">
            <div class="input-group mb-3">
              <input type="text" name="service_no" class="form-control" placeholder="Service No" required value="<?= htmlspecialchars($_POST['service_no'] ?? '') ?>">
              <button name="search_customer" class="btn btn-primary" type="submit">Search</button>
            </div>
          </form>

          <?php if ($customer): ?>
            <form method="POST" class="bg-light p-3">
                <input type="hidden" name="service_no" value="<?= esc($customer['service_no']) ?>">
                <input type="hidden" name="prev_reading" value="<?= esc($customer['curr_reading']) ?>">
                <input type="hidden" name="category_name" value="<?= esc($customer['service_type']) ?>">
                <div class="mb-2"><strong><?= esc($customer['name']) ?></strong></div>
                <div class="mb-2">Prev: <?= esc($customer['curr_reading']) ?></div>
                <div class="mb-3">
                  <label>New Reading</label>
                  <input type="number" name="curr_reading" class="form-control" required min="<?= esc($customer['curr_reading']) ?>">
                </div>
                <button name="generate_bill" class="btn btn-success w-100">Generate Bill</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>