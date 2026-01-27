<?php
// Minimal test runner: run some unit tests for billing calculation
require_once __DIR__ . '/../src/inc/billing.php';

$cases = [
    ['prev'=>100,'curr'=>100,'cat'=>'household','exp_units'=>0,'exp_price'=>25.00],
    ['prev'=>0,'curr'=>50,'cat'=>'household','exp_units'=>50],
    ['prev'=>0,'curr'=>120,'cat'=>'household','exp_units'=>120],
];

foreach ($cases as $i => $c) {
    $res = calculate_tiered_bill($c['prev'],$c['curr'],$c['cat']);
    echo "Case ".($i+1)." units={$res['units']} price={$res['bill_price']}\n";
}
?>