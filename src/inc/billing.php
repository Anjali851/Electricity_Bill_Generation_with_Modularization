<?php
// src/inc/billing.php
// Tiered slab billing using values from rate_slabs table.
// Minimum fixed charge of 25.00 when units == 0.

require_once __DIR__ . '/db.php';

/**
 * Get slab rates (r1..r4) for a given category (returns array of floats).
 * Fallback to legacy defaults if DB call fails.
 */
function get_slabs_for_category($category = 'household') {
    $m = db();
    $sql = "SELECT r1, r2, r3, r4 FROM rate_slabs WHERE `type` = ? LIMIT 1";
    $stmt = $m->prepare($sql);
    if (!$stmt) {
        error_log("get_slabs_for_category prepare failed: " . $m->error);
        // fallback defaults (legacy)
        return [1.5, 2.5, 3.5, 4.5];
    }
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        return [
            (float)$res['r1'],
            (float)$res['r2'],
            (float)$res['r3'],
            (float)$res['r4']
        ];
    }
    // fallback
    return [1.5, 2.5, 3.5, 4.5];
}

/**
 * Calculate bill using tiered slab rates retrieved from DB.
 * Returns array: ['units'=>int, 'bill_price'=>float]
 */
function calculate_tiered_bill($prev, $curr, $category = 'household') {
    $prev = (int)$prev;
    $curr = (int)$curr;
    $units = max(0, $curr - $prev);

    // If zero units, apply minimum fixed charge
    if ($units === 0) {
        return [
            'units' => 0,
            'bill_price' => 25.00
        ];
    }

    list($r1, $r2, $r3, $r4) = get_slabs_for_category($category);

    $remaining = $units;
    $price = 0.0;

    // First 50 units
    $s1 = min($remaining, 50);
    $price += $s1 * $r1;
    $remaining -= $s1;

    // Next 50
    if ($remaining > 0) {
        $s2 = min($remaining, 50);
        $price += $s2 * $r2;
        $remaining -= $s2;
    }

    // Next 50
    if ($remaining > 0) {
        $s3 = min($remaining, 50);
        $price += $s3 * $r3;
        $remaining -= $s3;
    }

    // Above 150
    if ($remaining > 0) {
        $price += $remaining * $r4;
    }

    return [
        'units' => $units,
        'bill_price' => round($price, 2)
    ];
}

/**
 * Create a bill in DB using slab calc and previous unpaid dues logic.
 * Returns ['success'=>bool, 'bill'=>array] or ['success'=>false,'error'=>string]
 */
function create_bill($service_no, $prev, $curr, $category) {
    $m = db();

    $calc = calculate_tiered_bill($prev, $curr, $category);
    $curr_price = $calc['bill_price'];
    $units = $calc['units'];

    // Get last bill for prev_dues logic
    $stmt = $m->prepare("SELECT total_bill, status FROM bills WHERE service_no = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $service_no);
    $stmt->execute();
    $last = $stmt->get_result()->fetch_assoc();

    $prev_dues = ($last && $last['status'] === 'unpaid') ? (float)$last['total_bill'] : 0.0;
    $fine = ($prev_dues > 0) ? 150.00 : 0.0;

    $total = round($curr_price + $prev_dues + $fine, 2);
    $due_date = date('Y-m-d', strtotime('+15 days'));
    $bill_no = "BILL-" . strtoupper(substr(md5(uniqid((string)microtime(true), true)), 0, 6));

    $sql = "INSERT INTO bills (bill_no, service_no, prev_reading, curr_reading, units, bill_price, prev_dues, fine, total_bill, due_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid')";
    $stmt2 = $m->prepare($sql);
    if (!$stmt2) {
        return ['success' => false, 'error' => "Prepare failed: " . $m->error];
    }

    $stmt2->bind_param('ssiiidddds', $bill_no, $service_no, $prev, $curr, $units, $curr_price, $prev_dues, $fine, $total, $due_date);

    if (!$stmt2->execute()) {
        return ['success' => false, 'error' => "Execute failed: " . $stmt2->error];
    }

    // Update customer's curr_reading
    $up = $m->prepare("UPDATE customers SET curr_reading = ? WHERE service_no = ?");
    if ($up) {
        $up->bind_param('is', $curr, $service_no);
        $up->execute();
    }

    return [
        'success' => true,
        'bill' => [
            'id' => $m->insert_id,
            'bill_no' => $bill_no,
            'service_no' => $service_no,
            'prev' => $prev,
            'curr' => $curr,
            'units' => $units,
            'bill_price' => $curr_price,
            'prev_dues' => $prev_dues,
            'fine' => $fine,
            'total' => $total,
            'due_date' => $due_date
        ]
    ];
}
?>