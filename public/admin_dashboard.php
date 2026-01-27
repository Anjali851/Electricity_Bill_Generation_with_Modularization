<?php
// public/admin_dashboard.php
session_start();
require_once __DIR__ . '/../src/inc/auth.php';
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/validators.php';

require_role('admin');

$mysqli = db();
$errors = [];
$success = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $mysqli->prepare("SELECT service_no FROM customers WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $sno = $res['service_no'];
            $stmt2 = $mysqli->prepare("DELETE FROM bills WHERE service_no = ?");
            $stmt2->bind_param('s', $sno);
            $stmt2->execute();
            $stmt3 = $mysqli->prepare("DELETE FROM customers WHERE id = ?");
            $stmt3->bind_param('i', $id);
            $stmt3->execute();
            $success = "Account and history removed.";
        }
    }
}

// Handle register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $data = [
        'name' => $_POST['name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'contact' => $_POST['contact'] ?? '',
        'address' => $_POST['address'] ?? '',
        'password' => $_POST['password'] ?? '',
        'role' => $_POST['role'] ?? 'user',
        'service_no' => $_POST['service_no'] ?? '',
        'service_type' => $_POST['type'] ?? 'household'
    ];
    if (register_user($data, $errors)) {
        $success = "Account Registered Successfully for " . to_title_case($data['name']);
    }
}

// Fetch users
$users_res = $mysqli->query("SELECT * FROM customers WHERE role != 'admin' ORDER BY role DESC, name ASC");

// Bills filter
$filter = $_GET['status'] ?? 'all';
$bill_sql = "SELECT b.*, c.name, c.service_type FROM bills b JOIN customers c ON b.service_no = c.service_no";
if ($filter == 'paid') $bill_sql .= " WHERE b.status = 'paid'";
if ($filter == 'unpaid') $bill_sql .= " WHERE b.status = 'unpaid'";
$bill_sql .= " ORDER BY b.id DESC";
$bills_res = $mysqli->query($bill_sql);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f0f2f5; font-family:Inter, sans-serif; }
.card { border-radius:12px; }
.nav-header { background:#1e293b; color:white; padding:14px 0; margin-bottom:20px; }
.filter-link { margin-left:8px; padding:6px 12px; border-radius:18px; text-decoration:none; color:#6b7280; }
.filter-link.active { background:#0d6efd; color:white !important; }
</style>
</head>
<body>
<div class="nav-header">
  <div class="container d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Admin Management</h4>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</div>

<div class="container">
  <?php if ($success): ?><div class="alert alert-success"><?= esc($success) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-danger"><?= esc(implode(' / ', $errors)) ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card p-4">
        <h5>Add New Account</h5>
        <form method="POST">
          <div class="mb-2">
            <select name="role" id="roleSelect" class="form-select" onchange="toggleFields()">
              <option value="user">Customer (User)</option>
              <option value="employee">Employee (Staff)</option>
            </select>
          </div>
          <input name="name" class="form-control mb-2" placeholder="Full Name" required>
          <input name="username" class="form-control mb-2" placeholder="Login Username" required>
          <input name="contact" class="form-control mb-2" placeholder="Phone (10 digits)" required>
          <textarea name="address" class="form-control mb-2" rows="2" placeholder="Address"></textarea>
          <div id="consumerFields">
            <select name="type" class="form-select mb-2">
              <option value="household">Household</option>
              <option value="commercial">Commercial</option>
              <option value="industry">Industry</option>
            </select>
            <input name="service_no" id="s_no_input" class="form-control mb-2" placeholder="Service Number (digits)">
          </div>
          <input name="password" type="password" class="form-control mb-3" placeholder="Password" required>
          <button class="btn btn-primary w-100" name="register" type="submit">Register Account</button>
        </form>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card p-4">
        <h5>System Directory</h5>
        <div class="table-responsive" style="max-height:360px; overflow:auto;">
          <table class="table">
            <thead><tr><th>Account</th><th>Address</th><th>Role</th><th>Action</th></tr></thead>
            <tbody>
              <?php while($u = $users_res->fetch_assoc()): ?>
              <tr>
                <td>
                  <strong><?= esc($u['name']) ?></strong><br>
                  <small class="text-primary"><?= esc($u['service_no']) ?></small><br>
                  <small class="text-muted"><?= esc($u['contact']) ?></small>
                </td>
                <td><?= esc($u['address']) ?></td>
                <td><?= strtoupper(esc($u['role'])) ?></td>
                <td><a class="btn btn-sm btn-outline-danger" href="admin_dashboard.php?delete_id=<?= $u['id'] ?>" onclick="return confirm('Delete?')">Delete</a></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-12">
      <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5>Master Billing History</h5>
          <div>
            <a href="admin_dashboard.php?status=all" class="filter-link <?= ($filter=='all') ? 'active' : '' ?>">All</a>
            <a href="admin_dashboard.php?status=paid" class="filter-link <?= ($filter=='paid') ? 'active' : '' ?>">Paid</a>
            <a href="admin_dashboard.php?status=unpaid" class="filter-link <?= ($filter=='unpaid') ? 'active' : '' ?>">Unpaid</a>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-sm">
            <thead class="table-dark"><tr><th>Service ID</th><th>Customer</th><th>Units</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
              <?php if ($bills_res->num_rows): while($b = $bills_res->fetch_assoc()): ?>
              <tr>
                <td><?= esc($b['service_no']) ?></td>
                <td><?= esc($b['name']) ?> <small class="text-muted">(<?= esc(strtoupper($b['service_type'])) ?>)</small></td>
                <td><?= esc($b['units']) ?> kWh</td>
                <td class="text-success">₹<?= number_format($b['total_bill'],2) ?></td>
                <td><span class="badge bg-<?= ($b['status']=='paid')?'success':'danger' ?>"><?= strtoupper($b['status']) ?></span></td>
              </tr>
              <?php endwhile; else: ?>
              <tr><td colspan="5" class="text-center">No records found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>

</div>

<script>
function toggleFields(){
  var role = document.getElementById('roleSelect').value;
  var cf = document.getElementById('consumerFields');
  var sno = document.getElementById('s_no_input');
  if (role === 'user') { cf.style.display = 'block'; sno.required=true; } else { cf.style.display='none'; sno.required=false; }
}
window.onload = toggleFields;
</script>

</body>
</html>