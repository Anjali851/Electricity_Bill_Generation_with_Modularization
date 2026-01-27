<?php
// public/index.php - login
session_start();
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';

    $user = find_user_by_login($login_input);
    if (!$user) {
        $err = " Username/Service No not found.";
    } elseif ($user['role'] !== $role) {
        $err = " Role mismatch: trying to log in as $role but account is " . $user['role'];
    } elseif (!password_verify($password, $user['password'])) {
        $err = " Invalid password.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['service_no'] = $user['service_no'];

        if ($user['role'] === 'admin') header('Location: admin_dashboard.php');
        elseif ($user['role'] === 'employee') header('Location: employee_dashboard.php');
        else header('Location: user_dashboard.php');
        exit;
    }
}
$posted_username = htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$posted_role = htmlspecialchars($_POST['role'] ?? 'user', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Electricity Billing — Login</title>

<!-- Project styles & scripts (place assets/style.css and assets/app.js in public/assets/) -->
<link rel="stylesheet" href="assets/style.css">
<script src="assets/app.js" defer></script>

<style>
/* Fallback / minor overrides to keep the card compact if assets not present */
body { margin:0; font-family:Inter, sans-serif; background:linear-gradient(135deg,#00b4db,#0083b0); height:100vh; display:flex; align-items:center; justify-content:center; }
.card { width:380px; background:#fff; padding:28px; border-radius:14px; box-shadow:0 10px 40px rgba(0,0,0,.12); }
.input { width:100%; padding:12px; margin:10px 0; border-radius:8px; border:1px solid #cdd9ed; font-size:15px; }
.btn { width:100%; background:#0083b0; color:#fff; padding:12px; border:0; border-radius:8px; font-weight:600; cursor:pointer; }
.role-selector { display:flex; background:#f0f2f5; padding:4px; border-radius:8px; margin-bottom:15px; }
.role-option { flex:1; text-align:center; padding:8px; cursor:pointer; border-radius:6px; color:#666; font-weight:600; }
.role-option.active { background:#0083b0; color:#fff; }
.error { background:#ffe6e6; color:#c0392b; padding:10px; border-radius:6px; margin-top:10px; text-align:center; }
</style>
</head>
<body>
<div class="card" role="main" aria-labelledby="login-title">
    <h2 id="login-title" style="text-align:center;color:#005f8f;margin:0 0 12px">⚡ Electricity Portal</h2>
    <form method="POST" novalidate>
        <div class="role-selector" role="tablist" aria-label="Select role">
            <div id="tab-user" class="role-option <?= $posted_role === 'user' ? 'active' : '' ?>" onclick="setRole('user')" role="tab" aria-selected="<?= $posted_role === 'user' ? 'true' : 'false' ?>">Customer</div>
            <div id="tab-employee" class="role-option <?= $posted_role === 'employee' ? 'active' : '' ?>" onclick="setRole('employee')" role="tab" aria-selected="<?= $posted_role === 'employee' ? 'true' : 'false' ?>">Employee</div>
            <div id="tab-admin" class="role-option <?= $posted_role === 'admin' ? 'active' : '' ?>" onclick="setRole('admin')" role="tab" aria-selected="<?= $posted_role === 'admin' ? 'true' : 'false' ?>">Admin</div>
        </div>

        <input type="hidden" id="roleInput" name="role" value="<?= $posted_role ?>">

        <label class="small" for="username" style="display:none">Username or Service No</label>
        <input id="username" class="input" name="username" placeholder="Username or Service No" required autofocus value="<?= $posted_username ?>">

        <label class="small" for="password" style="display:none">Password</label>
        <input id="password" class="input" name="password" type="password" placeholder="Password" required>

        <button class="btn" type="submit">Login</button>
    </form>

    <?php if ($err): ?>
      <div class="error" role="alert"><?= esc($err) ?></div>
    <?php endif; ?>
</div>

<script>
// Keep compatibility with the small helper in public/assets/app.js.
// If app.js defines setRoleUI, delegate to it; otherwise use local logic.
function setRole(r){
  var roleInput = document.getElementById('roleInput');
  if (typeof setRoleUI === 'function') {
    try { setRoleUI(r); } catch(e) { /* fallback below */ }
  }
  if (roleInput) roleInput.value = r;
  document.querySelectorAll('.role-option').forEach(function(el){ el.classList.remove('active'); el.setAttribute('aria-selected','false'); });
  var tab = document.getElementById('tab-' + r);
  if (tab) { tab.classList.add('active'); tab.setAttribute('aria-selected','true'); }
}

// On load, ensure UI matches hidden input (in case of POST)
document.addEventListener('DOMContentLoaded', function(){
  var current = document.getElementById('roleInput').value || 'user';
  setRole(current);
});
</script>
</body>
</html>