<?php
// tools/recalculate_bills.php
// Recalculate all bills using slab rates from rate_slabs.
//
// Usage:
//   php recalculate_bills.php          -> dry-run (no DB changes)
//   php recalculate_bills.php --apply  -> apply updates to bills table
//
// IMPORTANT: Backup DB before running with --apply.

require_once __DIR__ . '/../src/inc/db.php';
require_once __DIR__ . '/../src/inc/billing.php';

$apply = in_array('--apply', $argv, true);
echo "Recalculation started. Apply mode: " . ($apply ? "YES" : "NO (dry-run)") . PHP_EOL;
echo "Make sure you have backed up the database BEFORE running with --apply." . PHP_EOL . PHP_EOL;

$m = db();
$logFile = __DIR__ . '/recalc_applied.log';
if ($apply) $log = fopen($logFile, 'a');

$q = "SELECT b.id, b.bill_no, b.service_no, b.prev_reading, b.curr_reading, b.total_bill AS old_total, c.service_type
      FROM bills b
      LEFT JOIN customers c ON b.service_no = c.service_no
      ORDER BY b.id ASC";
$res = $m->query($q);

if (!$res) {
    echo "Query failed: " . $m->error . PHP_EOL;
    exit(1);
}

$changes = 0;
while ($b = $res->fetch_assoc()) {
    $id = (int)$b['id'];
    $service_no = $b['service_no'];
    $prev = (int)$b['prev_reading'];
    $curr = (int)$b['curr_reading'];
    $category = $b['service_type'] ?? 'household';
    $old_total = (float)$b['old_total'];

    // compute new bill price & units using slab model
    $calc = calculate_tiered_bill($prev, $curr, $category);
    $units = $calc['units'];
    $bill_price = $calc['bill_price'];

    // find previous unpaid bill excluding current (most recent id != current)
    $stmt = $m->prepare("SELECT total_bill, status FROM bills WHERE service_no = ? AND id != ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('si', $service_no, $id);
    $stmt->execute();
    $last = $stmt->get_result()->fetch_assoc();
    $prev_dues = ($last && $last['status'] === 'unpaid') ? (float)$last['total_bill'] : 0.0;
    $fine = ($prev_dues > 0) ? 150.00 : 0.0;
    $new_total = round($bill_price + $prev_dues + $fine, 2);

    $line = sprintf("Bill ID=%d Service=%s Units=%d OldTotal=%.2f NewPrice=%.2f PrevDues=%.2f Fine=%.2f NewTotal=%.2f",
                    $id, $service_no, $units, $old_total, $bill_price, $prev_dues, $fine, $new_total);

    if ($apply) {
        $up = $m->prepare("UPDATE bills SET units = ?, bill_price = ?, prev_dues = ?, fine = ?, total_bill = ? WHERE id = ?");
        $up->bind_param('iddddi', $units, $bill_price, $prev_dues, $fine, $new_total, $id);
        if ($up->execute()) {
            $changes++;
            echo "[APPLIED] " . $line . PHP_EOL;
            fwrite($log, date('c') . " " . $line . PHP_EOL);
        } else {
            echo "[ERROR] Failed to update bill id {$id}: " . $up->error . PHP_EOL;
            fwrite($log, date('c') . " ERROR updating bill id {$id}: " . $up->error . PHP_EOL);
        }
    } else {
        // dry-run: print differences
        if (abs($old_total - $new_total) > 0.005) {
            echo "[DIFFER] " . $line . PHP_EOL;
        } else {
            echo "[SAME]   " . $line . PHP_EOL;
        }
    }
}

if ($apply) {
    if (isset($log)) fclose($log);
    echo PHP_EOL . "Applied updates to {$changes} records. Log: {$logFile}" . PHP_EOL;
} else {
    echo PHP_EOL . "Dry-run complete. To apply changes, run with --apply after backing up DB." . PHP_EOL;
}