<?php
// public/payment_action.php
session_start();
require_once __DIR__ . '/../src/inc/db.php';

if (isset($_GET['bill_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 'user') {
    $bill_id = (int)$_GET['bill_id'];
    $s_no = $_SESSION['service_no'];

    $m = db();
    $stmt = $m->prepare("UPDATE bills SET status='paid' WHERE id = ? AND service_no = ?");
    $stmt->bind_param('is', $bill_id, $s_no);
    if ($stmt->execute()) {
        echo "<script>alert('Payment Received Successfully!'); window.location='generate_receipt.php?id=$bill_id';</script>";
        exit;
    } else {
        echo "<script>alert('Error processing payment.'); window.location='user_dashboard.php';</script>";
        exit;
    }
}
header("Location: user_dashboard.php");
exit();
?>