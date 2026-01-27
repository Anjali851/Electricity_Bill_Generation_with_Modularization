<?php
// api/bills.php
require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/billing.php';
$config = require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$api_key = $_GET['api_key'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($api_key !== $config['api_key']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$m = db();
$action = $_GET['action'] ?? 'list';

if ($action === 'create') {
    // required params: service_no, prev, curr, category
    $service_no = $_POST['service_no'] ?? '';
    $prev = intval($_POST['prev'] ?? 0);
    $curr = intval($_POST['curr'] ?? 0);
    $category = $_POST['category'] ?? 'household';
    if ($service_no === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Missing service_no']);
        exit;
    }
    $result = create_bill($service_no, $prev, $curr, $category);
    if ($result['success']) echo json_encode($result['bill']);
    else { http_response_code(500); echo json_encode(['error' => $result['error']]); }
    exit;
}

if ($action === 'list' && isset($_GET['service_no'])) {
    $s = $_GET['service_no'];
    $stmt = $m->prepare("SELECT * FROM bills WHERE service_no = ? ORDER BY id DESC");
    $stmt->bind_param('s', $s);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode($rows);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Bad request']);