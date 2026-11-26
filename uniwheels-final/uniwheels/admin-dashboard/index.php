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
                $pdo->prepare("UPDATE cars SET status = 'SOLD', sold_at = NOW(), reserved_at = NULL, reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'confirmed' WHERE car_id = ? AND state = 'active'")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'confirmed' WHERE car_id = ? AND state = 'pending'")->execute([$id]);
                $is_undo ? flash("Undone. $name is sold again.") : flash("$name marked as sold.", 'put_back', $id);
                $back = $is_undo ? 'index.php?show=sold' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php');
                break;

            case 'mark_rented':
                $pdo->prepare("UPDATE cars SET status = 'RENTED', sold_at = NULL, reserved_at = NULL, reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'confirmed' WHERE car_id = ? AND state = 'active'")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'confirmed' WHERE car_id = ? AND state = 'pending'")->execute([$id]);
                flash("$name marked as rented.", 'put_back', $id);
                $back = $fromTab ? 'index.php?show=' . $fromTab : 'index.php?show=other';
                break;

            case 'put_back':
                $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', sold_at = NULL, reserved_at = NULL, reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'confirmed')")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND state IN ('pending', 'confirmed')")->execute([$id]);
                $is_undo ? flash("Undone. $name is back on sale.") : flash("$name is back on sale.", 'mark_sold', $id);
                $back = $is_undo ? 'index.php' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php?show=sold');
                break;

            case 'reserve':
                $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = NOW(), reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $is_undo ? flash("Undone. $name is reserved again.") : flash("$name moved to Reserved.", 'unreserve', $id);
                $back = $is_undo ? 'index.php?show=reserved' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php');
                break;

            case 'unreserve':
                $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', reserved_at = NULL, reserve_expires_at = NULL WHERE id = ?")->execute([$id]);
                $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND state IN ('active', 'confirmed')")->execute([$id]);
                $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND state IN ('pending', 'confirmed')")->execute([$id]);
                $is_undo ? flash("Undone. $name is no longer reserved.") : flash("$name is back on sale.", 'reserve', $id);
                $back = $is_undo ? 'index.php' : ($fromTab ? 'index.php?show=' . $fromTab : 'index.php?show=reserved');
                break;

            case 'remove':
                $pdo->prepare('UPDATE cars SET deleted_at = NOW() WHERE id = ?')->execute([$id]);
                flash("$name was removed from the website.", 'restore', $id);
                $back = in_array($prevStatus, SOLD_STATUSES, true) ? 'index.php?show=sold' : ($prevStatus === 'RESERVED' ? 'index.php?show=reserved' : 'index.php');
                break;

            case 'restore':
                $pdo->prepare('UPDATE cars SET deleted_at = NULL WHERE id = ?')->execute([$id]);
                flash("$name is back.");
                $back = in_array($prevStatus, SOLD_STATUSES, true) ? 'index.php?show=sold' : ($prevStatus === 'RESERVED' ? 'index.php?show=reserved' : 'index.php');
                break;
        }
        $wp_sync_ids = [$id];
        require __DIR__ . '/wp-sync.php';
    }
    header('Location: ' . $back);
    exit;
}

$show = strtolower(trim((string) ($_GET['show'] ?? 'sale')));
if (!in_array($show, ['sale', 'reserved', 'other', 'sold'], true)) {
    $show = 'sale';
}
$search = trim($_GET['q'] ?? '');
$soldPeriod = trim($_GET['period'] ?? 'all');
$soldFrom = trim($_GET['from'] ?? '');
$soldTo = trim($_GET['to'] ?? '');

$cars = [];
$reserveInfo = [];
if ($pdo) {
    $cars = $pdo->query('SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY COALESCE(sold_at, reserved_at, created_at) DESC, id DESC')->fetchAll();

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
            FROM uw_reservation_holds WHERE state IN ('active', 'confirmed') ORDER BY id DESC")->fetchAll();
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
$sold_all = [];
$sold_months = [];

foreach ($cars as $car) {
    $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
    if (in_array($status, SOLD_STATUSES, true) && !empty($car['sold_at'])) {
        $ym = date('Y-m', strtotime($car['sold_at']));
        $sold_months[$ym] = date('F Y', strtotime($car['sold_at']));
    }
    if ($search !== '' && stripos($car['name'] . ' ' . $car['brand'] . ' ' . $car['stock_id'], $search) === false) {
        continue;
    }
    if (in_array($status, SOLD_STATUSES, true)) {
        $sold_all[] = $car;
    } elseif ($status === 'RESERVED') {
        $reserved[] = $car;
    } elseif (in_array($status, ['RENTED', 'MAINTENANCE'], true)) {
        $other_status[] = $car;
    } else {
        $for_sale[] = $car;
    }
}
krsort($sold_months);

// Date filtering for Sold tab (Requirement H)
$sold = [];
$todayStr = date('Y-m-d');
$weekStartStr = date('Y-m-d', strtotime('monday this week'));
$monthStartStr = date('Y-m-01');
$yearStartStr = date('Y-01-01');

foreach ($sold_all as $car) {
    $soldDate = !empty($car['sold_at']) ? date('Y-m-d', strtotime($car['sold_at'])) : '';
    $soldYm = !empty($car['sold_at']) ? date('Y-m', strtotime($car['sold_at'])) : '';

    if ($soldPeriod === 'today' && $soldDate !== $todayStr) {
        continue;
    } elseif ($soldPeriod === 'week' && ($soldDate === '' || $soldDate < $weekStartStr)) {
        continue;
    } elseif ($soldPeriod === 'month' && ($soldDate === '' || $soldDate < $monthStartStr)) {
        continue;
    } elseif ($soldPeriod === 'year' && ($soldDate === '' || $soldDate < $yearStartStr)) {
        continue;
    } elseif (preg_match('/^\d{4}-\d{2}$/', $soldPeriod) && $soldYm !== $soldPeriod) {
        continue;
    }

    if ($soldFrom !== '' && ($soldDate === '' || $soldDate < $soldFrom)) {
        continue;
    }
    if ($soldTo !== '' && ($soldDate === '' || $soldDate > $soldTo)) {
        continue;
    }
    $sold[] = $car;
}

// Categorize sold cars by date (newest date first)
$sold_by_date = [];
foreach ($sold as $car) {
    $key = !empty($car['sold_at']) ? date('Y-m-d', strtotime($car['sold_at'])) : 'undated';
    $sold_by_date[$key][] = $car;
}
uksort($sold_by_date, function ($a, $b) {
    if ($a === 'undated') return 1;
    if ($b === 'undated') return -1;
    return strcmp($b, $a);
});

$list = match ($show) {
    'reserved' => $reserved,
    'other'    => $other_status,
    'sold'     => $sold,
    default    => $for_sale,
};

// Summary numbers, counted from all cars (not search-filtered).
$summary = ['for_sale' => 0, 'reserved' => 0, 'other' => 0, 'stock_value' => 0, 'month_count' => 0, 'month_value' => 0, 'year_count' => 0, 'year_value' => 0, 'last_sold' => null];
$sell_days = [];
foreach ($cars as $car) {
    $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
    if (!in_array($status, SOLD_STATUSES, true)) {
        if ($status === 'RESERVED') {
            $summary['reserved']++;
        } elseif (in_array($status, ['RENTED', 'MAINTENANCE'], true)) {
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
    <p class="stat-note"><?= $summary['other'] ? $summary['other'] . ' rented' : 'Separate from For Sale' ?></p>
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
    Rented <span class="count"><?= count($other_status) ?></span>
  </a>
  <a href="index.php?show=sold<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show === 'sold' ? 'active' : '' ?>" role="tab" aria-selected="<?= $show === 'sold' ? 'true' : 'false' ?>">
    Sold <span class="count"><?= count($sold_all) ?></span>
  </a>
</div>

<?php if ($show === 'sold'): ?>
  <form class="sold-filter-bar" method="get" action="index.php">
    <input type="hidden" name="show" value="sold">
    <div class="sold-filter-inputs">
      <div class="sold-filter-item sold-filter-search">
        <label for="q">Search sold cars</label>
        <input type="search" id="q" name="q" value="<?= h($search) ?>" placeholder="Name, brand, or stock ID">
      </div>
      <div class="sold-filter-item">
        <label for="period">Date period</label>
        <select id="period" name="period">
          <option value="all" <?= $soldPeriod === 'all' ? 'selected' : '' ?>>All dates</option>
          <option value="today" <?= $soldPeriod === 'today' ? 'selected' : '' ?>>Today</option>
          <option value="week" <?= $soldPeriod === 'week' ? 'selected' : '' ?>>This week</option>
          <option value="month" <?= $soldPeriod === 'month' ? 'selected' : '' ?>>This month</option>
          <option value="year" <?= $soldPeriod === 'year' ? 'selected' : '' ?>>This year</option>
          <?php foreach ($sold_months as $ym => $label): ?>
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
          <a href="index.php?show=sold" class="btn btn-light">Clear</a>
        <?php endif; ?>
      </div>
      <a href="export.php?year=<?= h(date('Y')) ?>" class="btn btn-light sold-export-btn">Download <?= h(date('Y')) ?> sales list (Excel)</a>
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
    <?php if ($search !== '' || ($show === 'sold' && ($soldPeriod !== 'all' || $soldFrom !== '' || $soldTo !== ''))): ?>
      No cars match those filters.
    <?php elseif ($show === 'reserved'): ?>
      No reserved cars right now. Units marked as Reserved appear here instead of For sale.
    <?php elseif ($show === 'other'): ?>
      No rented units right now.
    <?php elseif ($show === 'sold'): ?>
      No sold cars yet. When a car is sold, press <strong>Mark as sold</strong> and it will move here.
    <?php else: ?>
      No cars for sale right now. Press <strong>+ Add a car</strong> to put one on the website.
    <?php endif; ?>
  </div>
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
          <?php elseif ($status === 'RENTED'): ?>
            <span class="badge badge-rented">Rented</span>
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
          <?php if ($status === 'RESERVED'): ?>
            <div class="reservation-box">
              <?php if ($rInfo): ?>
                <strong><?= $rInfo['state'] === 'confirmed' ? 'Confirmed' : 'Hold' ?>: <?= h($rInfo['reference']) ?></strong><br>
                <?= h($rInfo['customer_name']) ?> (<?= h($rInfo['customer_contact']) ?>)
                <?php if ($rInfo['state'] !== 'confirmed' && !empty($car['reserve_expires_at'])): ?>
                  <br>Expires <?= h(date('M j, Y g:i A', strtotime($car['reserve_expires_at']))) ?>
                <?php endif; ?>
              <?php elseif (!empty($car['reserved_at'])): ?>
                Reserved since <?= h(date('M j, Y g:i A', strtotime($car['reserved_at']))) ?>
                <?php if (!empty($car['reserve_expires_at'])): ?>
                  <br>Expires <?= h(date('M j, Y g:i A', strtotime($car['reserve_expires_at']))) ?>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="car-actions">
          <a href="car.php?id=<?= (int) $car['id'] ?>" class="btn btn-light">Edit</a>
          <form method="post" action="index.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
            <input type="hidden" name="from_tab" value="<?= h($show) ?>">
            <button type="submit" name="action" value="mark_sold" class="btn btn-sold">Mark as sold</button>
          </form>
          <?php if ($status === 'RESERVED'): ?>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="reserved">
              <button type="submit" name="action" value="unreserve" class="btn btn-light">Release reserve</button>
            </form>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="reserved">
              <button type="submit" name="action" value="mark_rented" class="btn btn-light">Mark rented</button>
            </form>
          <?php elseif ($status === 'AVAILABLE'): ?>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="sale">
              <button type="submit" name="action" value="reserve" class="btn btn-light">Reserve</button>
            </form>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="sale">
              <button type="submit" name="action" value="mark_rented" class="btn btn-light">Mark rented</button>
            </form>
          <?php else: ?>
            <form method="post" action="index.php" class="action-span-2">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <input type="hidden" name="from_tab" value="other">
              <button type="submit" name="action" value="put_back" class="btn btn-light">Mark available</button>
            </form>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php page_end(); ?>
