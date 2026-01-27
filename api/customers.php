<?php
// api/customers.php
// Simple API to fetch customer info
require_once __DIR__ . '/../src/inc/db.php';
$config = require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$api_key = $_GET['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($api_key !== $config['api_key']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'list';
$m = db();

if ($action === 'get' && isset($_GET['service_no'])) {
    $s = $_GET['service_no'];
    $stmt = $m->prepare("SELECT id, username, name, contact, address, service_no, service_type, prev_reading, curr_reading FROM customers WHERE service_no = ? LIMIT 1");
    $stmt->bind_param('s', $s);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();
    if (!$data) {
        echo json_encode(['error' => 'Not found']);
        exit;
    }
    echo json_encode($data);
    exit;
}

if ($action === 'list') {
    $res = $m->query("SELECT id, username, name, contact, service_no, service_type, role FROM customers ORDER BY role DESC, name ASC");
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
    exit;
}

// default
http_response_code(400);
echo json_encode(['error' => 'Bad request']);