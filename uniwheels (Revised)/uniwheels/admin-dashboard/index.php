<?php
/**
 * UniWheels staff home: the list of our cars, split into "For sale" and "Sold".
 * Quick actions (reserve, mark as sold, put back on sale, remove, undo) are posted here.
 */

require __DIR__ . '/lib.php';

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $is_undo = !empty($_POST['is_undo']);
    $id = (int) ($_POST['id'] ?? 0);

    // Removed cars are still found so "restore" can bring them back.
    $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
    $stmt->execute([$id]);
    $car = $stmt->fetch();
    $back = 'index.php';

    if ($car) {
        $name = $car['name'];
        switch ($action) {
            case 'mark_sold':
                $pdo->prepare("UPDATE cars SET status = 'SOLD', sold_at = NOW(), reserved_at = NULL WHERE id = ?")->execute([$id]);
                $is_undo ? flash("Undone. $name is sold again.") : flash("$name marked as sold.", 'put_back', $id);
                $back = $is_undo ? 'index.php?show=sold' : 'index.php';
                break;

            case 'put_back':
                $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', sold_at = NULL, reserved_at = NULL WHERE id = ?")->execute([$id]);
                $is_undo ? flash("Undone. $name is for sale again.") : flash("$name is back on sale.", 'mark_sold', $id);
                $back = $is_undo ? 'index.php' : 'index.php?show=sold';
                break;

            case 'reserve':
                $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = NOW() WHERE id = ?")->execute([$id]);
                $is_undo ? flash("Undone. $name is reserved again.") : flash("$name is reserved.", 'unreserve', $id);
                break;

            case 'unreserve':
                $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', reserved_at = NULL WHERE id = ?")->execute([$id]);
                $is_undo ? flash("Undone. $name is not reserved.") : flash("$name is no longer reserved.", 'reserve', $id);
                break;

            case 'remove':
                $pdo->prepare('UPDATE cars SET deleted_at = NOW() WHERE id = ?')->execute([$id]);
                flash("$name was removed from the website.", 'restore', $id);
                $back = in_array(strtoupper($car['status']), SOLD_STATUSES, true) ? 'index.php?show=sold' : 'index.php';
                break;

            case 'restore':
                $pdo->prepare('UPDATE cars SET deleted_at = NULL WHERE id = ?')->execute([$id]);
                flash("$name is back.");
                $back = in_array(strtoupper($car['status']), SOLD_STATUSES, true) ? 'index.php?show=sold' : 'index.php';
                break;
        }
    }
    if ($car) {
        $wp_sync_ids = [$id];
        require __DIR__ . '/wp-sync.php';
    }
    header('Location: ' . $back);
    exit;
}

$show_sold = ($_GET['show'] ?? '') === 'sold';
$search = trim($_GET['q'] ?? '');

$cars = [];
if ($pdo) {
    $cars = $pdo->query('SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY COALESCE(sold_at, created_at) DESC, id DESC')->fetchAll();
}

$for_sale = [];
$sold = [];
foreach ($cars as $car) {
    if ($search !== '' && stripos($car['name'] . ' ' . $car['brand'] . ' ' . $car['stock_id'], $search) === false) {
        continue;
    }
    if (in_array(strtoupper($car['status']), SOLD_STATUSES, true)) {
        $sold[] = $car;
    } else {
        $for_sale[] = $car;
    }
}
$list = $show_sold ? $sold : $for_sale;

// Summary numbers, counted from all cars (not just the search results).
$summary = ['for_sale' => 0, 'reserved' => 0, 'stock_value' => 0, 'month_count' => 0, 'month_value' => 0, 'year_count' => 0, 'year_value' => 0, 'last_sold' => null];
$sell_days = [];
foreach ($cars as $car) {
    $status = strtoupper($car['status']);
    if (!in_array($status, SOLD_STATUSES, true)) {
        $summary['for_sale']++;
        $summary['reserved'] += $status === 'RESERVED' ? 1 : 0;
        $summary['stock_value'] += (float) $car['price'];
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
    <p class="stat-label">Cars for sale</p>
    <p class="stat-value"><?= $summary['for_sale'] ?></p>
    <p class="stat-note"><?= $summary['reserved'] ? $summary['reserved'] . ' of them reserved' : 'None reserved' ?></p>
  </div>
  <div class="stat">
    <p class="stat-label">Value of cars for sale</p>
    <p class="stat-value"><?= h(peso($summary['stock_value'])) ?></p>
    <p class="stat-note">Total of all asking prices</p>
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
        <br>Takes about <?= $summary['avg_days'] ?> <?= $summary['avg_days'] === 1 ? 'day' : 'days' ?> to sell
      <?php endif; ?>
    </p>
  </div>
</section>

<h2 class="section-title">Our Cars</h2>

<div class="tabs" role="tablist">
  <a href="index.php<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>" class="tab <?= $show_sold ? '' : 'active' ?>" role="tab" aria-selected="<?= $show_sold ? 'false' : 'true' ?>">
    For sale <span class="count"><?= count($for_sale) ?></span>
  </a>
  <a href="index.php?show=sold<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>" class="tab <?= $show_sold ? 'active' : '' ?>" role="tab" aria-selected="<?= $show_sold ? 'true' : 'false' ?>">
    Sold <span class="count"><?= count($sold) ?></span>
  </a>
</div>

<?php if (count($cars) > 6 || $search !== ''): ?>
  <form class="search" method="get" action="index.php">
    <?php if ($show_sold): ?><input type="hidden" name="show" value="sold"><?php endif; ?>
    <label for="q" class="sr-only">Find a car</label>
    <input type="search" id="q" name="q" value="<?= h($search) ?>" placeholder="Find a car by name or brand">
    <button type="submit" class="btn btn-light">Find</button>
    <?php if ($search !== ''): ?><a href="index.php<?= $show_sold ? '?show=sold' : '' ?>" class="link">Show all</a><?php endif; ?>
  </form>
<?php endif; ?>

<?php if ($show_sold): ?>
  <p class="export"><a href="export.php?year=<?= h(date('Y')) ?>" class="btn btn-light">Download <?= h(date('Y')) ?> sales list (Excel)</a></p>
<?php endif; ?>

<?php if (!$list): ?>
  <div class="empty">
    <?php if ($search !== ''): ?>
      No cars match "<?= h($search) ?>".
    <?php elseif ($show_sold): ?>
      No sold cars yet. When a car is sold, press <strong>Mark as sold</strong> on it and it will move here.
    <?php else: ?>
      No cars for sale right now. Press <strong>+ Add a car</strong> to put one on the website.
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="car-grid">
    <?php foreach ($list as $car):
        $photos = car_photos($car);
        $status = strtoupper($car['status']);
    ?>
      <article class="car-card">
        <a href="car.php?id=<?= (int) $car['id'] ?>" class="car-photo">
          <?php if ($photos): ?>
            <img src="<?= h($cars_img_url . $photos[0]) ?>" alt="" loading="lazy">
          <?php else: ?>
            <span class="no-photo">No photo yet</span>
          <?php endif; ?>
          <?php if ($status === 'RESERVED'): ?><span class="badge badge-reserved">Reserved</span><?php endif; ?>
        </a>
        <div class="car-body">
          <h2 class="car-name"><?= h($car['name']) ?></h2>
          <p class="car-price"><?= h(peso($car['price'])) ?></p>
          <p class="car-meta">
            <?= h(implode(' · ', array_filter([$car['mileage'], $car['transmission'], $car['body_type']]))) ?>
          </p>
          <?php if ($show_sold && !empty($car['sold_at'])): ?>
            <p class="car-meta">Sold on <?= h(date('j F Y', strtotime($car['sold_at']))) ?></p>
          <?php endif; ?>
        </div>
        <div class="car-actions">
          <a href="car.php?id=<?= (int) $car['id'] ?>" class="btn btn-light">Edit</a>
          <form method="post" action="index.php">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
            <?php if ($show_sold): ?>
              <button type="submit" name="action" value="put_back" class="btn btn-light">Put back on sale</button>
            <?php else: ?>
              <button type="submit" name="action" value="mark_sold" class="btn btn-sold">Mark as sold</button>
            <?php endif; ?>
          </form>
          <?php if (!$show_sold): ?>
            <form method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
              <?php if ($status === 'RESERVED'): ?>
                <button type="submit" name="action" value="unreserve" class="btn btn-light">Not reserved</button>
              <?php else: ?>
                <button type="submit" name="action" value="reserve" class="btn btn-light">Reserve</button>
              <?php endif; ?>
            </form>
            <button type="button" class="btn btn-light js-copy-post" data-post="<?= h(facebook_post($car)) ?>">Copy Facebook post</button>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php page_end(); ?>
