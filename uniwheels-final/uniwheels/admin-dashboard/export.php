<?php
/**
 * Download the sold cars of one year as a CSV file that opens in Excel.
 * Link: export.php?year=2026
 */

require __DIR__ . '/lib.php';

$year = (int) ($_GET['year'] ?? date('Y'));
if ($year < 2000 || $year > 2100) {
    $year = (int) date('Y');
}

$rows = [];
if ($pdo) {
    $stmt = $pdo->prepare('SELECT * FROM cars WHERE deleted_at IS NULL AND sold_at IS NOT NULL AND YEAR(sold_at) = ? ORDER BY sold_at');
    $stmt->execute([$year]);
    $rows = $stmt->fetchAll();
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="uniwheels-sales-' . $year . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // lets Excel read the ₱ sign and accents correctly
fputcsv($out, ['Date sold', 'Car', 'Brand', 'Stock ID', 'Price (PHP)', 'Days to sell']);
$total = 0;
foreach ($rows as $car) {
    $days = max(0, (int) floor((strtotime($car['sold_at']) - strtotime($car['created_at'])) / 86400));
    $total += (float) $car['price'];
    fputcsv($out, [
        date('Y-m-d', strtotime($car['sold_at'])),
        $car['name'],
        $car['brand'],
        $car['stock_id'],
        number_format((float) $car['price'], 2, '.', ''),
        $days,
    ]);
}
fputcsv($out, []);
fputcsv($out, ['Total', count($rows) . ' cars', '', '', number_format($total, 2, '.', ''), '']);
fclose($out);
