<?php
// src/inc/auth.php
// Authentication helpers (register/login/role checks)

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/validators.php';

function find_user_by_login($login) {
    $m = db();
    $sql = "SELECT * FROM customers WHERE username = ? OR service_no = ? LIMIT 1";
    $stmt = $m->prepare($sql);
    $stmt->bind_param('ss', $login, $login);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc() ?: null;
}

function register_user($data, &$errors = []) {
    $m = db();

    // Validate
    $v = validate_name($data['name']);
    if ($v !== true) $errors[] = $v;

    $v = validate_contact($data['contact']);
    if ($v !== true) $errors[] = $v;

    $v = validate_service_no($data['service_no'], $data['role'] === 'user');
    if ($v !== true) $errors[] = $v;

    if (!filter_var($data['username'], FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z0-9_@.-]{3,100}$/']])) {
        $errors[] = "Username is invalid.";
    }

    if (strlen($data['password']) < 6) $errors[] = "Password must be at least 6 characters.";

    if ($errors) return false;

    $name = to_title_case($data['name']);
    $username = $m->real_escape_string(trim($data['username']));
    $contact = preg_replace('/\D+/', '', $data['contact']);
    $address = $m->real_escape_string(trim($data['address'] ?? ''));
    $service_no = trim($data['service_no']);
    if ($service_no === '') {
        // generate for non-user roles
        $service_no = '9' . mt_rand(1000, 9999);
    }
    $role = $data['role'];
    $type = $data['service_type'] ?? 'household';
    $pass_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert with prepared stmt
    $sql = "INSERT INTO customers (username, password, role, name, contact, address, service_no, service_type, prev_reading, curr_reading) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0)";
    $stmt = $m->prepare($sql);
    if (!$stmt) {
        $errors[] = "Database error.";
        return false;
    }
    $stmt->bind_param('ssssssss', $username, $pass_hash, $role, $name, $contact, $address, $service_no, $type);

    if (!$stmt->execute()) {
        $errors[] = "Insert failed: " . $stmt->error;
        return false;
    }
    return true;
}

function require_role($role) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: index.php");
        exit();
    }
}
?>