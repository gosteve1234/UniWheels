<?php
/**
 * UniWheels staff home: inventory split into For sale, Reserved, Rented/Maint., and Sold.
 */

require __DIR__ . '/lib.php';

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $is_undo = !empty($_POST['is_undo']);
    $id = (int) ($_POST['id'] ?? 0);
    $fromTab = preg_replace('/[^a-z]/', '', strtolower((string) ($_POST['from_tab'] ?? '')));

    $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
    $stmt->execute([$id]);
    $car = $stmt->fetch();
    $back = 'index.php';

    if ($car) {
        $name = $car['name'];
        $prevStatus = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
        switch ($action) {
            case 'mark_sold':
                $saleMode = trim((string) ($_POST['sale_mode'] ?? 'set_price'));
                if (!in_array($saleMode, ['set_price', 'custom_price', 'trade'], true)) {
                    $saleMode = 'set_price';
                }

                if (!$is_undo && $saleMode === 'trade') {
                    $tradeCarName = trim((string) ($_POST['trade_car_name'] ?? ''));
                    $tradeStockId = trim((string) ($_POST['trade_stock_id'] ?? ''));
                    if ($tradeCarName === '' || $tradeStockId === '') {
                        flash("Please enter both the traded car's name and stock number for {$name}.");
                        $back = $fromTab ? 'index.php?show=' . $fromTab : 'index.php';
                        break;
                    }
                    $pdo->prepare("UPDATE cars SET
                        status = 'TRADED',
                        traded_at = NOW(),
                        sold_at = NULL,
                        reserved_at = NULL,
                        reserve_expires_at = NULL,
                        trade_car_name = ?,
                        trade_stock_id = ?
                        WHERE id = ?")->execute([$tradeCarName, $tradeStockId, $id]);
                    $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'expired')")->execute([$id]);
                    $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND state = 'pending'")->execute([$id]);
                    flash("$name marked as traded (with {$tradeCarName} · {$tradeStockId}) and moved to Trade.", 'put_back', $id);
                    $back = 'index.php?show=trade';
                    break;
                }

                if (!$is_undo && $saleMode === 'custom_price') {
                    $customRaw = preg_replace('/[^0-9.]/', '', (string) ($_POST['custom_price'] ?? ''));
                    $customPrice = (float) $customRaw;
                    if ($customPrice <= 0) {
                        flash("Please enter a valid selling price for {$name}.");
                        $back = $fromTab ? 'index.php?show=' . $fromTab : 'index.php';
                        break;
                    }
                    $pdo->prepare("UPDATE cars SET
                        listed_price = COALESCE(listed_price, price),
                        price = ?,
                        status = 'SOLD',
                        sold_at = NOW(),
                        traded_at = NULL,
                        trade_car_name = NULL,
                        trade_stock_id = NULL,
                        reserved_at = NULL,
                        reserve_expires_at = NULL
                        WHERE id = ?")->execute([$customPrice, $id]);
                    $pdo->prepare("UPDATE uw_reservation_holds SET state = 'confirmed' WHERE car_id = ? AND state IN ('active', 'expired')")->execute([$id]);
                    $pdo->prepare("UPDATE vehicle_bookings SET state = 'confirmed' WHERE car_id = ? AND state = 'pending'")->execute([$id]);
                    flash("$name marked as sold for " . peso($customPrice) . ".", 'put_back', $id);
                    $back = $fromTab ? 'index.php?show=' . $fromTab : 'index.php';
                    break;
                }

                $pdo->prepare("UPDATE cars SET
                    status = 'SOLD',
                    sold_at = NOW(),
                    traded_at = NULL,
                    trade_car_name = NULL,
                    trade_stock_id = NULL,
                    reserved_at = NULL,
                    reserve_expires_at = NULL
                    WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'confirmed' WHERE car_id = ? AND state IN ('active', 'expired')")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'confirmed' WHERE car_id = ? AND state = 'pending'")->execute([$id]);
                $is_undo ? flash("Undone. $name is sold again.") : flash("$name marked as sold.", 'put_back', $id);
                $back = $is_undo ? 'index.php?show=sold' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php');
                break;

            case 'put_back':
                $pdo->prepare("UPDATE cars SET
                    status = 'AVAILABLE',
                    price = COALESCE(listed_price, price),
                    listed_price = NULL,
                    sold_at = NULL,
                    traded_at = NULL,
                    trade_car_name = NULL,
                    trade_stock_id = NULL,
                    reserved_at = NULL,
                    reserve_expires_at = NULL
                    WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'confirmed', 'expired')")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND state IN ('pending', 'confirmed')")->execute([$id]);
                $is_undo ? flash("Undone. $name is back on sale.") : flash("$name is back on sale.", 'mark_sold', $id);
                $defaultBack = $prevStatus === 'TRADED' ? 'index.php?show=trade' : 'index.php?show=sold';
                $back = $is_undo ? 'index.php' : ($fromTab ? 'index.php?show=' . $fromTab : $defaultBack);
                break;

            case 'reserve':
                $maxDays = max(1, (int) ($uw_settings['max_hold_days'] ?? 7));
                $days = (int) ($_POST['days'] ?? 0);
                $customerName = trim((string) ($_POST['customer_name'] ?? ''));
                $customerContact = trim((string) ($_POST['customer_contact'] ?? ''));

                if (!$is_undo && ($days < 1 || $days > $maxDays || $customerName === '' || $customerContact === '')) {
                    flash("Please enter a valid hold duration (1–{$maxDays} days), buyer's name, and mobile number or email to reserve {$name}.");
                    $back = $fromTab ? 'index.php?show=' . $fromTab : 'index.php';
                    break;
                }

                $createdAt = date('Y-m-d H:i:s');
                if ($is_undo) {
                    $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = COALESCE(reserved_at, ?), reserve_expires_at = reserve_expires_at WHERE id = ?")
                        ->execute([$createdAt, $id]);
                    flash("Undone. $name is reserved again.");
                    $back = 'index.php?show=reserved';
                } else {
                    $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
                    $deposit = (float) ($car['booking_deposit'] ?? 0);
                    if ($deposit <= 0) {
                        $deposit = (float) ($uw_settings['default_deposit'] ?? 5000);
                    }
                    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                    $rand = '';
                    for ($ri = 0; $ri < 4; $ri++) {
                        $rand .= $chars[random_int(0, strlen($chars) - 1)];
                    }
                    $ref = 'UW-H' . date('ymd') . '-' . $rand;
                    $staffHash = hash('sha256', 'staff:' . ($_SESSION['user_id'] ?? 0));

                    $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'expired')")->execute([$id]);
                    $pdo->prepare("INSERT INTO uw_reservation_holds
                        (car_id, reference, owner_hash, customer_name, customer_contact, total, deposit, expires_at, created_at, state)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')")
                        ->execute([$id, $ref, $staffHash, $customerName, $customerContact, (float) ($car['price'] ?? 0), $deposit, $expiresAt, $createdAt]);

                    $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = ?, reserve_expires_at = ? WHERE id = ?")
                        ->execute([$createdAt, $expiresAt, $id]);

                    $dayLabel = $days === 1 ? '1 day' : "{$days} days";
                    flash("$name reserved for {$customerName} ({$dayLabel}) and moved to Reserved.", 'unreserve', $id);
                    $back = 'index.php?show=reserved';
                }
                break;

            case 'unreserve':
                $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', reserved_at = NULL, reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'confirmed', 'expired')")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND state IN ('pending', 'confirmed')")->execute([$id]);
                $is_undo ? flash("Undone. $name is no longer reserved.") : flash("$name is back on sale.", 'reserve', $id);
                $back = $is_undo ? 'index.php' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php?show=reserved');
                break;

            case 'remove':
                $pdo->prepare('UPDATE cars SET deleted_at = NOW() WHERE id = ?')->execute([$id]);
                flash("$name was removed from the website.", 'restore', $id);
                $back = in_array($prevStatus, SOLD_STATUSES, true) ? 'index.php?show=sold' : ($prevStatus === 'TRADED' ? 'index.php?show=trade' : ($prevStatus === 'RESERVED' ? 'index.php?show=reserved' : 'index.php'));
                break;

            case 'restore':
                $pdo->prepare('UPDATE cars SET deleted_at = NULL WHERE id = ?')->execute([$id]);
                flash("$name is back.");
                $back = in_array($prevStatus, SOLD_STATUSES, true) ? 'index.php?show=sold' : ($prevStatus === 'TRADED' ? 'index.php?show=trade' : ($prevStatus === 'RESERVED' ? 'index.php?show=reserved' : 'index.php'));
                break;
        }
        $wp_sync_ids = [$id];
        require __DIR__ . '/wp-sync.php';
    }
    header('Location: ' . $back);
    exit;
}

$show = strtolower(trim((string) ($_GET['show'] ?? 'sale')));
if (!in_array($show, ['sale', 'reserved', 'other', 'trade', 'sold'], true)) {
    $show = 'sale';
}
$search = trim($_GET['q'] ?? '');
$soldPeriod = trim($_GET['period'] ?? 'all');
$soldFrom = trim($_GET['from'] ?? '');
$soldTo = trim($_GET['to'] ?? '');

$cars = [];
$reserveInfo = [];
if ($pdo) {
    $cars = $pdo->query('SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY COALESCE(sold_at, traded_at, reserved_at, created_at) DESC, id DESC')->fetchAll();

    // Load latest active/confirmed booking or hold info for Reserved units.
    try {
        $bRows = $pdo->query("SELECT car_id, reference, state, customer_name, customer_contact, booking_type, rental_start, rental_end, expires_at
            FROM vehicle_bookings WHERE state IN ('pending', 'confirmed') ORDER BY id DESC")->fetchAll();
        foreach ($bRows as $r) {
            $cid = (int) $r['car_id'];
            if (!isset($reserveInfo[$cid])) {
                $reserveInfo[$cid] = $r + ['source' => 'booking'];
            }
        }
        $hRows = $pdo->query("SELECT car_id, reference, state, customer_name, customer_contact, expires_at
            FROM uw_reservation_holds WHERE state IN ('active', 'confirmed', 'expired') ORDER BY id DESC")->fetchAll();
        foreach ($hRows as $r) {
            $cid = (int) $r['car_id'];
            if (!isset($reserveInfo[$cid])) {
                $reserveInfo[$cid] = $r + ['source' => 'hold'];
            }
        }
    } catch (Throwable $e) {
        $reserveInfo = [];
    }
}

$for_sale = [];
$reserved = [];
$other_status = [];
$traded_all = [];
$trade_months = [];
$sold_all = [];
$sold_months = [];

foreach ($cars as $car) {
    $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
    if (in_array($status, SOLD_STATUSES, true) && !empty($car['sold_at'])) {
        $ym = date('Y-m', strtotime($car['sold_at']));
        $sold_months[$ym] = date('F Y', strtotime($car['sold_at']));
    }
    if ($status === 'TRADED' && !empty($car['traded_at'])) {
        $ym = date('Y-m', strtotime($car['traded_at']));
        $trade_months[$ym] = date('F Y', strtotime($car['traded_at']));
    }
    $searchHaystack = $car['name'] . ' ' . $car['brand'] . ' ' . $car['stock_id'] . ' ' . ($car['trade_car_name'] ?? '') . ' ' . ($car['trade_stock_id'] ?? '');
    if ($search !== '' && stripos($searchHaystack, $search) === false) {
        continue;
    }
    if (in_array($status, SOLD_STATUSES, true)) {
        $sold_all[] = $car;
    } elseif ($status === 'TRADED') {
        $traded_all[] = $car;
    } elseif ($status === 'RESERVED') {
        $reserved[] = $car;
    } elseif ($status === 'MAINTENANCE') {
        $other_status[] = $car;
    } else {
        $for_sale[] = $car;
    }
}
krsort($sold_months);
krsort($trade_months);

$todayStr = date('Y-m-d');
$weekStartStr = date('Y-m-d', strtotime('monday this week'));
$monthStartStr = date('Y-m-01');
$yearStartStr = date('Y-01-01');

function filter_by_date_range(array $items, string $dateField, string $period, string $from, string $to, string $todayStr, string $weekStartStr, string $monthStartStr, string $yearStartStr): array {
    $out = [];
    foreach ($items as $car) {
        $d = !empty($car[$dateField]) ? date('Y-m-d', strtotime($car[$dateField])) : '';
        $ym = !empty($car[$dateField]) ? date('Y-m', strtotime($car[$dateField])) : '';

        if ($period === 'today' && $d !== $todayStr) {
            continue;
        } elseif ($period === 'week' && ($d === '' || $d < $weekStartStr)) {
            continue;
        } elseif ($period === 'month' && ($d === '' || $d < $monthStartStr)) {
            continue;
        } elseif ($period === 'year' && ($d === '' || $d < $yearStartStr)) {
            continue;
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period) && $ym !== $period) {
            continue;
        }
        if ($from !== '' && ($d === '' || $d < $from)) {
            continue;
        }
        if ($to !== '' && ($d === '' || $d > $to)) {
            continue;
        }
        $out[] = $car;
    }
    return $out;
}

function group_by_date_desc(array $items, string $dateField): array {
    $grouped = [];
    foreach ($items as $car) {
        $key = !empty($car[$dateField]) ? date('Y-m-d', strtotime($car[$dateField])) : 'undated';
        $grouped[$key][] = $car;
    }
    uksort($grouped, function ($a, $b) {
        if ($a === 'undated') return 1;
        if ($b === 'undated') return -1;
        return strcmp($b, $a);
    });
    return $grouped;
}

// Date filtering & grouping for Sold and Trade tabs
$sold = filter_by_date_range($sold_all, 'sold_at', $soldPeriod, $soldFrom, $soldTo, $todayStr, $weekStartStr, $monthStartStr, $yearStartStr);
$sold_by_date = group_by_date_desc($sold, 'sold_at');

$traded = filter_by_date_range($traded_all, 'traded_at', $soldPeriod, $soldFrom, $soldTo, $todayStr, $weekStartStr, $monthStartStr, $yearStartStr);
$traded_by_date = group_by_date_desc($traded, 'traded_at');

$list = match ($show) {
    'reserved' => $reserved,
    'other'    => $other_status,
    'trade'    => $traded,
    'sold'     => $sold,
    default    => $for_sale,
};

// Summary numbers, counted from all cars (not search-filtered).
$summary = ['for_sale' => 0, 'reserved' => 0, 'other' => 0, 'stock_value' => 0, 'month_count' => 0, 'month_value' => 0, 'year_count' => 0, 'year_value' => 0, 'last_sold' => null];
$sell_days = [];
foreach ($cars as $car) {
    $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
    if ($status === 'TRADED') {
        continue;
    }
    if (!in_array($status, SOLD_STATUSES, true)) {
        if ($status === 'RESERVED') {
            $summary['reserved']++;
        } elseif ($status === 'MAINTENANCE') {
            $summary['other']++;
        } else {
            $summary['for_sale']++;
            $summary['stock_value'] += (float) $car['price'];
        }
        continue;
    }
    if (empty($car['sold_at'])) {
        continue;
    }
    $sold_time = strtotime($car['sold_at']);
    if (date('Y', $sold_time) === date('Y')) {
        $summary['year_count']++;
        $summary['year_value'] += (float) $car['price'];
        if (date('m', $sold_time) === date('m')) {
            $summary['month_count']++;
            $summary['month_value'] += (float) $car['price'];
        }
    }
    if (!$summary['last_sold'] || $sold_time > strtotime($summary['last_sold']['sold_at'])) {
        $summary['last_sold'] = $car;
    }
    $sell_days[] = max(0, (int) floor(($sold_time - strtotime($car['created_at'])) / 86400));
}
$summary['avg_days'] = $sell_days ? (int) round(array_sum($sell_days) / count($sell_days)) : null;

$expired_reserved_count = 0;
foreach ($reserved as $rCar) {
    if (!empty($rCar['reserve_expires_at']) && strtotime($rCar['reserve_expires_at']) <= time()) {
        $expired_reserved_count++;
    }
}

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

page_start('Our Cars', 'cars');
show_flash();
?>

<?php if (!$pdo): ?>
  <div class="notice notice-error">
    Cannot reach the database. Please make sure XAMPP (Apache and MySQL) is running, then refresh this page.
  </div>
<?php endif; ?>

<?php if ($expired_reserved_count > 0): ?>
  <div class="notice notice-warning" role="alert">
    <strong>Reservation duration passed:</strong>
    <?= $expired_reserved_count ?> <?= $expired_reserved_count === 1 ? 'unit has' : 'units have' ?> reached the reservation deadline and the buyer has not checked in yet.
    <?php if ($show !== 'reserved'): ?>
      <a href="index.php?show=reserved" class="link">Review in Reserved &rarr;</a>
    <?php else: ?>
      Click <strong>End Reservation</strong> on the unit below to put it back on sale.
    <?php endif; ?>
  </div>
<?php endif; ?>

<div class="welcome">
  <div>
    <p class="welcome-date"><?= h(date('l, j F Y')) ?></p>
    <h1><?= h($greeting) ?>, <?= h($_SESSION['full_name'] ?? 'there') ?></h1>
  </div>
  <div class="welcome-actions">
    <a href="<?= h(MESSENGER_INBOX_URL) ?>" class="btn btn-messenger btn-big" target="_blank" rel="noopener">Open Messenger inbox</a>
    <a href="car.php" class="btn btn-primary btn-big">+ Add a car</a>
  </div>
</div>

<section class="stats" aria-label="Summary">
  <div class="stat">
    <p class="stat-label">Available for sale</p>
    <p class="stat-value"><?= $summary['for_sale'] ?></p>
    <p class="stat-note"><?= h(peso($summary['stock_value'])) ?> total</p>
  </div>
  <div class="stat stat-amber">
    <p class="stat-label">Reserved units</p>
    <p class="stat-value"><?= $summary['reserved'] ?></p>
    <p class="stat-note"><?= $summary['other'] ? $summary['other'] . ' in maintenance' : 'Separate from For Sale' ?></p>
  </div>
  <div class="stat stat-green">
    <p class="stat-label">Sold in <?= h(date('F')) ?></p>
    <p class="stat-value"><?= $summary['month_count'] ?> <?= $summary['month_count'] === 1 ? 'car' : 'cars' ?></p>
    <p class="stat-note"><?= $summary['month_value'] ? h(peso($summary['month_value'])) : 'No sales yet this month' ?></p>
  </div>
  <div class="stat stat-green">
    <p class="stat-label">Sold in <?= h(date('Y')) ?></p>
    <p class="stat-value"><?= $summary['year_count'] ?> <?= $summary['year_count'] === 1 ? 'car' : 'cars' ?></p>
    <p class="stat-note">
      <?php if ($summary['last_sold']): ?>
        Last: <?= h($summary['last_sold']['name']) ?>, <?= h(date('j M', strtotime($summary['last_sold']['sold_at']))) ?>
      <?php else: ?>
        No sales yet this year
      <?php endif; ?>
      <?php if ($summary['avg_days'] !== null): ?>
        <br>Avg. <?= $summary['avg_days'] ?> <?= $summary['avg_days'] === 1 ? 'day' : 'days' ?> to sell
      <?php endif; ?>
    </p>
  </div>
</section>

<h2 class="section-title">Our Cars</h2>

<div class="tabs" role="tablist">
  <a href="index.php<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'sale' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'sale' ? 'true' : 'false' ?>">
    For sale <span class="count"><?= count($for_sale) ?></span>
  </a>
  <a href="index.php?show=reserved<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'reserved' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'reserved' ? 'true' : 'false' ?>">
    Reserved <span class="count"><?= count($reserved) ?></span>
  </a>
  <a href="index.php?show=other<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'other' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'other' ? 'true' : 'false' ?>">
    Maintenance <span class="count"><?= count($other_status) ?></span>
  </a>
  <a href="index.php?show=trade<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'trade' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'trade' ? 'true' : 'false' ?>">
    Trade <span class="count"><?= count($traded_all) ?></span>
  </a>
  <a href="index.php?show=sold<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'sold' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'sold' ? 'true' : 'false' ?>">
    Sold <span class="count"><?= count($sold_all) ?></span>
  </a>
</div>

<?php if ($show === 'sold' || $show === 'trade'):
  $isTradeTab = $show === 'trade';
  $periodMonths = $isTradeTab ? $trade_months : $sold_months;
?>
  <form class="sold-filter-bar" method="get" action="index.php">
    <input type="hidden" name="show" value="<?= h($show) ?>">
    <div class="sold-filter-inputs">
      <div class="sold-filter-item sold-filter-search">
        <label for="q"><?= $isTradeTab ? 'Search traded cars' : 'Search sold cars' ?></label>
        <input type="search" id="q" name="q" value="<?= h($search) ?>" placeholder="<?= $isTradeTab ? 'Name, brand, stock ID, or traded car' : 'Name, brand, or stock ID' ?>">
      </div>
      <div class="sold-filter-item">
        <label for="period">Date period</label>
        <select id="period" name="period">
          <option value="all" <?= $soldPeriod === 'all' ? 'selected' : '' ?>>All dates</option>
          <option value="today" <?= $soldPeriod === 'today' ? 'selected' : '' ?>>Today</option>
          <option value="week" <?= $soldPeriod === 'week' ? 'selected' : '' ?>>This week</option>
          <option value="month" <?= $soldPeriod === 'month' ? 'selected' : '' ?>>This month</option>
          <option value="year" <?= $soldPeriod === 'year' ? 'selected' : '' ?>>This year</option>
          <?php foreach ($periodMonths as $ym => $label): ?>
            <option value="<?= h($ym) ?>" <?= $soldPeriod === $ym ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="sold-filter-item">
        <label for="from">From date</label>
        <input type="date" id="from" name="from" value="<?= h($soldFrom) ?>">
      </div>
      <div class="sold-filter-item">
        <label for="to">To date</label>
        <input type="date" id="to" name="to" value="<?= h($soldTo) ?>">
      </div>
    </div>
    <div class="sold-filter-footer">
      <div class="sold-filter-actions">
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($search !== '' || $soldPeriod !== 'all' || $soldFrom !== '' || $soldTo !== ''): ?>
          <a href="index.php?show=<?= h($show) ?>" class="btn btn-light">Clear</a>
        <?php endif; ?>
      </div>
      <?php if (!$isTradeTab): ?>
        <a href="export.php?year=<?= h(date('Y')) ?>" class="btn btn-light sold-export-btn">Download <?= h(date('Y')) ?> sales list (Excel)</a>
      <?php endif; ?>
    </div>
  </form>
<?php elseif (count($cars) > 6 || $search !== ''): ?>
  <form class="search" method="get" action="index.php">
    <input type="hidden" name="show" value="<?= h($show) ?>">
    <label for="q" class="sr-only">Find a car</label>
    <input type="search" id="q" name="q" value="<?= h($search) ?>" placeholder="Find a car by name or brand">
    <button type="submit" class="btn btn-light">Find</button>
    <?php if ($search !== ''): ?><a href="index.php?show=<?= h($show) ?>" class="link">Show all</a><?php endif; ?>
  </form>
<?php endif; ?>

<?php if (!$list): ?>
  <div class="empty">
    <?php if ($search !== '' || (in_array($show, ['sold', 'trade'], true) && ($soldPeriod !== 'all' || $soldFrom !== '' || $soldTo !== ''))): ?>
      No cars match those filters.
    <?php elseif ($show === 'reserved'): ?>
      No reserved cars right now. Units marked as Reserved appear here instead of For sale.
    <?php elseif ($show === 'other'): ?>
      No units under maintenance right now.
    <?php elseif ($show === 'trade'): ?>
      No traded cars yet. When a car is traded, select <strong>Sold</strong> &rarr; <strong>Traded</strong> and it will appear here.
    <?php elseif ($show === 'sold'): ?>
      No sold cars yet. When a car is sold, press <strong>Sold</strong> and it will move here.
    <?php else: ?>
      No cars for sale right now. Press <strong>+ Add a car</strong> to put one on the website.
    <?php endif; ?>
  </div>
<?php elseif ($show === 'trade'): ?>
  <?php foreach ($traded_by_date as $dateKey => $groupCars):
    $groupTotal = array_sum(array_map(fn($c) => (float) $c['price'], $groupCars));
    $dateHeading = $dateKey === 'undated' ? 'Date not recorded' : date('l, j F Y', strtotime($dateKey));
  ?>
    <section class="sold-date-group">
      <div class="sold-date-header trade-date-header">
        <h3><?= h($dateHeading) ?></h3>
        <span class="sold-date-summary"><?= count($groupCars) ?> <?= count($groupCars) === 1 ? 'car' : 'cars' ?> &middot; <?= h(peso($groupTotal)) ?></span>
      </div>
      <div class="car-grid">
        <?php foreach ($groupCars as $car):
          $photos = car_photos($car);
        ?>
          <article class="car-card">
            <a href="car.php?id=<?= (int) $car['id'] ?>" class="car-photo">
              <?php if ($photos): ?>
                <img src="<?= h($cars_img_url . $photos[0]) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="no-photo">No photo yet</span>
              <?php endif; ?>
              <span class="badge badge-traded">Traded</span>
            </a>
            <div class="car-body">
              <h2 class="car-name"><?= h($car['name']) ?></h2>
              <p class="car-price"><?= h(peso($car['price'])) ?></p>
              <p class="car-meta">
                <?= h(implode(' · ', array_filter([$car['mileage'], $car['transmission'], $car['body_type']]))) ?>
              </p>
              <?php if (!empty($car['trade_car_name']) || !empty($car['trade_stock_id'])): ?>
                <div class="trade-info-box">
                  <strong>Traded with:</strong> <?= h($car['trade_car_name'] ?: 'N/A') ?><br>
                  <strong>Stock no.:</strong> <?= h($car['trade_stock_id'] ?: 'N/A') ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($car['traded_at'])): ?>
                <p class="car-meta" style="margin-top:.45rem;">Traded on <?= h(date('j F Y, g:i A', strtotime($car['traded_at']))) ?></p>
              <?php endif; ?>
            </div>
            <div class="car-actions">
              <a href="car.php?id=<?= (int) $car['id'] ?>" class="btn btn-light">Edit</a>
              <form method="post" action="index.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
                <input type="hidden" name="from_tab" value="trade">
                <button type="submit" name="action" value="put_back" class="btn btn-light">Put back on sale</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php elseif ($show === 'sold'): ?>
  <?php foreach ($sold_by_date as $dateKey => $groupCars):
    $groupTotal = array_sum(array_map(fn($c) => (float) $c['price'], $groupCars));
    $dateHeading = $dateKey === 'undated' ? 'Date not recorded' : date('l, j F Y', strtotime($dateKey));
  ?>
    <section class="sold-date-group">
      <div class="sold-date-header">
        <h3><?= h($dateHeading) ?></h3>
        <span class="sold-date-summary"><?= count($groupCars) ?> <?= count($groupCars) === 1 ? 'car' : 'cars' ?> &middot; <?= h(peso($groupTotal)) ?></span>
      </div>
      <div class="car-grid">
        <?php foreach ($groupCars as $car):
          $photos = car_photos($car);
        ?>
          <article class="car-card">
            <a href="car.php?id=<?= (int) $car['id'] ?>" class="car-photo">
              <?php if ($photos): ?>
                <img src="<?= h($cars_img_url . $photos[0]) ?>" alt="" loading="lazy">
              <?php else: ?>
                <span class="no-photo">No photo yet</span>
              <?php endif; ?>
              <span class="badge badge-sold">Sold</span>
            </a>
            <div class="car-body">
              <h2 class="car-name"><?= h($car['name']) ?></h2>
              <p class="car-price"><?= h(peso($car['price'])) ?></p>
              <p class="car-meta">
                <?= h(implode(' · ', array_filter([$car['mileage'], $car['transmission'], $car['body_type']]))) ?>
              </p>
              <?php if (!empty($car['sold_at'])): ?>
                <p class="car-meta">Sold on <?= h(date('j F Y, g:i A', strtotime($car['sold_at']))) ?></p>
              <?php endif; ?>
            </div>
            <div class="car-actions">
              <a href="car.php?id=<?= (int) $car['id'] ?>" class="btn btn-light">Edit</a>
              <form method="post" action="index.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
                <input type="hidden" name="from_tab" value="sold">
                <button type="submit" name="action" value="put_back" class="btn btn-light">Put back on sale</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
<?php else: ?>
  <div class="car-grid">
    <?php foreach ($list as $car):
        $photos = car_photos($car);
        $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
        $rInfo = $reserveInfo[(int) $car['id']] ?? null;
    ?>
      <article class="car-card">
        <a href="car.php?id=<?= (int) $car['id'] ?>" class="car-photo">
          <?php if ($photos): ?>
            <img src="<?= h($cars_img_url . $photos[0]) ?>" alt="" loading="lazy">
          <?php else: ?>
            <span class="no-photo">No photo yet</span>
          <?php endif; ?>
          <?php if ($status === 'RESERVED'): ?>
            <span class="badge badge-reserved">Reserved</span>
          <?php elseif ($status === 'MAINTENANCE'): ?>
            <span class="badge badge-maintenance">Maintenance</span>
          <?php endif; ?>
        </a>
        <div class="car-body">
          <h2 class="car-name"><?= h($car['name']) ?></h2>
          <p class="car-price"><?= h(peso($car['price'])) ?></p>
          <p class="car-meta">
            <?= h(implode(' · ', array_filter([$car['mileage'], $car['transmission'], $car['body_type']]))) ?>
          </p>
          <?php if ($status === 'RESERVED'):
            $isReserveExpired = !empty($car['reserve_expires_at']) && strtotime($car['reserve_expires_at']) <= time();
          ?>
            <div class="reservation-box <?= $isReserveExpired ? 'is-expired' : '' ?>">
              <?php if ($isReserveExpired): ?>
                <strong class="expired-label">Reservation duration passed!</strong><br>
              <?php endif; ?>
              <?php if ($rInfo): ?>
                <strong><?= $rInfo['state'] === 'confirmed' ? 'Confirmed' : 'Reserved for' ?>: <?= h($rInfo['customer_name']) ?></strong>
                (<?= h($rInfo['customer_contact']) ?>)<br>
                Ref: <?= h($rInfo['reference']) ?>
                <?php if (!empty($car['reserve_expires_at'])): ?>
                  <br><?= $isReserveExpired ? 'Ended' : 'Expires' ?> <?= h(date('M j, Y g:i A', strtotime($car['reserve_expires_at']))) ?>
                <?php endif; ?>
              <?php elseif (!empty($car['reserved_at'])): ?>
                Reserved since <?= h(date('M j, Y g:i A', strtotime($car['reserved_at']))) ?>
                <?php if (!empty($car['reserve_expires_at'])): ?>
                  <br><?= $isReserveExpired ? 'Ended' : 'Expires' ?> <?= h(date('M j, Y g:i A', strtotime($car['reserve_expires_at']))) ?>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($isReserveExpired): ?>
                <br><span>Buyer hasn't visited yet? Click <strong>End Reservation</strong> to put back on sale.</span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="car-actions">
          <a href="car.php?id=<?= (int) $car['id'] ?>" class="btn btn-light">Edit</a>
          <?php if ($status === 'AVAILABLE'):
            $carDeposit = (float) ($car['booking_deposit'] ?? 0) > 0
              ? (float) $car['booking_deposit']
              : (float) ($uw_settings['default_deposit'] ?? 5000);
          ?>
            <button type="button"
              class="btn btn-light js-staff-reserve-btn"
              data-car-id="<?= (int) $car['id'] ?>"
              data-car-name="<?= h($car['name']) ?>"
              data-deposit="<?= h(number_format($carDeposit)) ?>">Reserve</button>
            <button type="button"
              class="btn btn-sold action-span-2 js-staff-sold-btn"
              data-car-id="<?= (int) $car['id'] ?>"
              data-car-name="<?= h($car['name']) ?>"
              data-car-price="<?= h((float) ($car['price'] ?? 0)) ?>"
              data-from-tab="<?= h($show) ?>">Sold</button>
          <?php elseif ($status === 'RESERVED'):
            $isReserveExpired = !empty($car['reserve_expires_at']) && strtotime($car['reserve_expires_at']) <= time();
          ?>
            <button type="button"
              class="btn btn-sold js-staff-sold-btn"
              data-car-id="<?= (int) $car['id'] ?>"
              data-car-name="<?= h($car['name']) ?>"
              data-car-price="<?= h((float) ($car['price'] ?? 0)) ?>"
              data-from-tab="<?= h($show) ?>">Sold</button>
            <form method="post" action="index.php" class="action-span-2">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="reserved">
              <button type="submit" name="action" value="unreserve" class="btn <?= $isReserveExpired ? 'btn-danger confirm-danger-solid' : 'btn-light' ?>">End Reservation</button>
            </form>
          <?php else: ?>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="other">
              <button type="submit" name="action" value="put_back" class="btn btn-light">Now Available</button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php $maxHoldDays = max(1, (int) ($uw_settings['max_hold_days'] ?? 7)); ?>
<div class="confirm-modal" id="staff-reserve-modal" role="dialog" aria-modal="true" aria-labelledby="staff-reserve-title" hidden>
  <div class="confirm-modal-backdrop" data-close-reserve></div>
  <div class="confirm-modal-box reserve-modal-box">
    <div class="reserve-modal-head">
      <h2 id="staff-reserve-title">Reserve This Car</h2>
      <button type="button" class="reserve-modal-close" id="staff-reserve-x" data-close-reserve aria-label="Close">&times;</button>
    </div>
    <p class="reserve-car-subtitle" id="staff-reserve-car-name"></p>
    <form method="post" action="index.php" id="staff-reserve-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="reserve">
      <input type="hidden" name="from_tab" value="<?= h($show) ?>">
      <input type="hidden" name="id" id="staff-reserve-car-id" value="">
      <input type="hidden" name="days" id="staff-reserve-days-value" value="1">

      <div class="field">
        <label>How many days will this car be reserved? <span class="required" aria-hidden="true">*</span></label>
        <div class="hold-duration-pills" id="staff-reserve-pills">
          <button type="button" class="hold-pill active" data-days="1">1 day</button>
          <button type="button" class="hold-pill" data-days="2">2 days</button>
          <button type="button" class="hold-pill" data-days="3">3 days</button>
          <button type="button" class="hold-pill" data-days="custom">Custom</button>
        </div>
      </div>

      <div class="field" id="staff-custom-days-wrap" hidden>
        <label for="staff-custom-days">Number of days (1–<?= $maxHoldDays ?>) <span class="required" aria-hidden="true">*</span></label>
        <input type="number" id="staff-custom-days" min="1" max="<?= $maxHoldDays ?>" value="<?= min(4, $maxHoldDays) ?>">
      </div>

      <div class="field">
        <label for="staff-reserve-name">Buyer's Name <span class="required" aria-hidden="true">*</span></label>
        <input type="text" id="staff-reserve-name" name="customer_name" required placeholder="e.g. Juan Dela Cruz">
      </div>

      <div class="field">
        <label for="staff-reserve-contact">Mobile Number or Email <span class="required" aria-hidden="true">*</span></label>
        <input type="text" id="staff-reserve-contact" name="customer_contact" required placeholder="e.g. 0917-123-4567 or email">
      </div>

      <p class="hint reserve-deposit-note">
        Hold Deposit: <strong id="staff-reserve-deposit">₱5,000</strong>. You will be notified once the duration passes so you can click <strong>End Reservation</strong> if the buyer has not visited.
      </p>

      <p class="field-error" id="staff-reserve-error" hidden></p>

      <div class="confirm-modal-actions">
        <button type="button" class="btn btn-light" id="staff-reserve-cancel" data-close-reserve>Cancel</button>
        <button type="submit" class="btn btn-primary" id="staff-reserve-submit">Reserve Car</button>
      </div>
    </form>
  </div>
</div>

<div class="confirm-modal" id="staff-sold-modal" role="dialog" aria-modal="true" aria-labelledby="staff-sold-title" hidden>
  <div class="confirm-modal-backdrop" data-close-sold></div>
  <div class="confirm-modal-box reserve-modal-box">
    <div class="reserve-modal-head">
      <h2 id="staff-sold-title">Was this unit sold?</h2>
      <button type="button" class="reserve-modal-close" id="staff-sold-x" data-close-sold aria-label="Close">&times;</button>
    </div>
    <p class="reserve-car-subtitle" id="staff-sold-car-name"></p>

    <form method="post" action="index.php" id="staff-sold-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="mark_sold">
      <input type="hidden" name="id" id="staff-sold-car-id" value="">
      <input type="hidden" name="from_tab" id="staff-sold-from-tab" value="<?= h($show) ?>">

      <!-- Step 1: Confirm whether unit was sold -->
      <div id="sold-step-1">
        <p>Please confirm if <strong id="sold-step1-car"></strong> was sold or traded.</p>
        <div class="confirm-modal-actions">
          <button type="button" class="btn btn-light" data-close-sold>Cancel</button>
          <button type="button" class="btn btn-primary" id="sold-step1-yes">Yes, continue</button>
        </div>
      </div>

      <!-- Step 2: Set price, different price, or traded -->
      <div id="sold-step-2" hidden>
        <p style="margin-bottom:.75rem;">Was it bought at its set selling price (<strong id="sold-set-price-label"></strong>), at a different amount, or traded?</p>

        <div class="sold-choice-list" id="sold-choice-list">
          <label class="sold-choice-card is-selected">
            <input type="radio" name="sale_mode" value="set_price" checked>
            <span>
              <span class="sold-choice-title">Set selling price</span>
              <span class="sold-choice-sub" id="sold-choice-set-sub">Bought at ₱0</span>
            </span>
          </label>
          <label class="sold-choice-card">
            <input type="radio" name="sale_mode" value="custom_price">
            <span>
              <span class="sold-choice-title">Different selling price</span>
              <span class="sold-choice-sub">Specify the actual amount paid</span>
            </span>
          </label>
          <label class="sold-choice-card">
            <input type="radio" name="sale_mode" value="trade">
            <span>
              <span class="sold-choice-title">Traded for another car</span>
              <span class="sold-choice-sub">Move this unit to the Trade section</span>
            </span>
          </label>
        </div>

        <div class="field" id="sold-custom-price-wrap" hidden>
          <label for="staff-sold-custom-price">Actual selling price (PHP) <span class="required" aria-hidden="true">*</span></label>
          <input type="text" id="staff-sold-custom-price" name="custom_price" inputmode="numeric" placeholder="e.g. 810,000">
        </div>

        <div id="sold-trade-wrap" hidden>
          <div class="field">
            <label for="staff-trade-car-name">Traded car name <span class="required" aria-hidden="true">*</span></label>
            <input type="text" id="staff-trade-car-name" name="trade_car_name" placeholder="e.g. 2021 Honda City 1.5 RS">
          </div>
          <div class="field">
            <label for="staff-trade-stock-id">Traded car stock number <span class="required" aria-hidden="true">*</span></label>
            <input type="text" id="staff-trade-stock-id" name="trade_stock_id" placeholder="e.g. UW-HND05">
          </div>
        </div>

        <p class="field-error" id="staff-sold-error" hidden></p>

        <div class="confirm-modal-actions">
          <button type="button" class="btn btn-light" id="sold-step2-back">Back</button>
          <button type="button" class="btn btn-primary" id="sold-step2-next">Continue</button>
        </div>
      </div>

      <!-- Step 3: Final confirmation -->
      <div id="sold-step-3" hidden>
        <p style="margin-bottom:.75rem;">Please review and confirm the details below:</p>
        <div class="sold-summary-box" id="sold-summary-box"></div>
        <div class="confirm-modal-actions">
          <button type="button" class="btn btn-light" id="sold-step3-back">Back</button>
          <button type="submit" class="btn btn-primary" id="sold-final-submit">Confirm</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php page_end(); ?>
