<?php
/**
 * Shared setup for the staff pages: session, sign-in check, database, small helpers.
 * Every admin page except login.php starts with: require __DIR__ . '/lib.php';
 */

date_default_timezone_set('Asia/Manila');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$suppress_db_die = true;
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/app.php';

uw_ensure_schema($pdo);
uw_release_expired_holds($pdo);
$uw_settings = uw_get_settings($pdo);

$cars_img_dir = __DIR__ . '/../assets/images/cars';
$cars_img_url = '../assets/images/cars/';

const CAR_STATUSES = [
    'AVAILABLE'   => 'For sale (Available)',
    'RESERVED'    => 'Reserved',
    'MAINTENANCE' => 'Under maintenance',
    'TRADED'      => 'Traded',
    'SOLD'        => 'Sold',
];
const SOLD_STATUSES = ['SOLD', 'OUT OF STOCK'];

// Dashboard reminders: a car for sale longer than this, or reserved longer than this, is flagged.
const SLOW_DAYS = 60;
const RESERVED_DAYS = 7;

const MESSENGER_INBOX_URL = 'https://business.facebook.com/latest/inbox';
const MESSENGER_URL = 'https://m.me/uniwheels';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function peso($price) {
    return (float) $price > 0 ? '₱' . number_format((float) $price) : 'No price yet';
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

function check_csrf() {
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('This page was open too long. Please go back, refresh and try again.');
    }
}

function flash($message, $undo_action = null, $car_id = null) {
    $_SESSION['flash'] = [
        'message' => $message,
        'undo' => $undo_action ? ['action' => $undo_action, 'id' => (int) $car_id] : null,
    ];
}

function take_flash() {
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

function find_car(PDO $pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([(int) $id]);
    return $stmt->fetch() ?: null;
}

function car_gallery($car) {
    $gallery = json_decode($car['gallery_images'] ?? '', true);
    return is_array($gallery) ? array_values(array_filter($gallery, 'is_string')) : [];
}

function car_photos($car) {
    $photos = array_merge([$car['image_file'] ?? ''], car_gallery($car));
    return array_values(array_unique(array_filter($photos)));
}

function save_uploaded_photos($input_name, $dir) {
    $saved = [];
    if (empty($_FILES[$input_name]['name']) || !is_array($_FILES[$input_name]['name'])) {
        return $saved;
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/avif' => 'avif'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    foreach ($_FILES[$input_name]['name'] as $i => $orig_name) {
        if ($_FILES[$input_name]['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $tmp = $_FILES[$input_name]['tmp_name'][$i];
        $ext = $types[$finfo->file($tmp)] ?? null;
        if (!$ext) {
            continue;
        }
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', pathinfo($orig_name, PATHINFO_FILENAME)));
        $base = trim(substr($base, 0, 40), '-') ?: 'car';
        $name = $base . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $ext;
        if (move_uploaded_file($tmp, $dir . '/' . $name)) {
            $saved[$i] = $name;
        }
    }
    return $saved;
}

function attention_items($pdo) {
    if (!$pdo) {
        return [];
    }
    $items = [];
    $cars = $pdo->query("SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY id DESC")->fetchAll();
    foreach ($cars as $car) {
        $status = strtoupper($car['status']);
        if (in_array($status, SOLD_STATUSES, true) || $status === 'TRADED') {
            continue;
        }
        $days_listed = (int) floor((time() - strtotime($car['created_at'])) / 86400);
        if ($status === 'RESERVED') {
            if (!empty($car['reserve_expires_at']) && strtotime($car['reserve_expires_at']) <= time()) {
                $expLabel = date('M j, g:i A', strtotime($car['reserve_expires_at']));
                $items[] = [$car, "reservation period ended on {$expLabel}. If the buyer hasn't visited, click End Reservation.", 'index.php?show=reserved'];
            } elseif (empty($car['reserve_expires_at']) && !empty($car['reserved_at'])) {
                $days_reserved = (int) floor((time() - strtotime($car['reserved_at'])) / 86400);
                if ($days_reserved > RESERVED_DAYS) {
                    $items[] = [$car, "has been reserved for $days_reserved days. Did the buyer confirm?", 'index.php?show=reserved'];
                }
            }
        } elseif ($status === 'AVAILABLE' && $days_listed > SLOW_DAYS) {
            $items[] = [$car, "has been for sale $days_listed days. Consider lowering the price."];
        }
        $missing = [];
        if (count(car_photos($car)) < 2) {
            $missing[] = 'more photos';
        }
        if (trim((string) $car['mileage']) === '') {
            $missing[] = 'mileage';
        }
        if (empty($car['ownership_history']) || empty($car['damage_report'])) {
            $missing[] = 'condition & history';
        }
        if ($missing) {
            $items[] = [$car, 'needs ' . implode(', ', $missing) . '.'];
        }
    }
    return $items;
}

function page_start($title, $active = '') {
    global $pdo;
    $alerts = attention_items($pdo);
    ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?> - UniWheels Staff</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css?v=<?= @filemtime(__DIR__ . '/admin.css') ?: '1' ?>">
</head>
<body>
  <header class="topbar">
    <a class="topbar-brand" href="index.php">UNI<span>WHEELS</span> <small>Staff</small></a>
    <nav class="topbar-nav">
      <details class="alerts">
        <summary aria-label="Reminders: <?= count($alerts) ?>">
          <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path d="M12 3 2 21h20L12 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 10v5M12 18h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
          <?php if ($alerts): ?><span class="alerts-dot"><?= count($alerts) ?></span><?php endif; ?>
        </summary>
        <div class="alerts-panel">
          <p class="alerts-title">Reminders</p>
          <?php if (!$alerts): ?>
            <p class="alerts-empty">All good. Nothing to check right now.</p>
          <?php else: ?>
            <ul>
              <?php foreach ($alerts as $alert):
                $car = $alert[0];
                $sentence = $alert[1];
                $alertUrl = $alert[2] ?? ('car.php?id=' . (int) $car['id']);
              ?>
                <li><a href="<?= h($alertUrl) ?>"><strong><?= h($car['name']) ?></strong> <?= h($sentence) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </details>
      <a href="index.php" class="<?= $active === 'cars' ? 'active' : '' ?>">Our Cars</a>
      <a href="settings.php" class="<?= $active === 'settings' ? 'active' : '' ?>">Policies &amp; Content</a>
      <a href="../" target="_blank" rel="noopener">View website</a>
      <a href="account.php" class="<?= $active === 'account' ? 'active' : '' ?>">My account</a>
      <a href="logout.php">Sign out</a>
    </nav>
  </header>
  <main class="page">
<?php
}

function page_end() {
    ?>
  </main>
  <script src="admin.js?v=<?= @filemtime(__DIR__ . '/admin.js') ?: '1' ?>"></script>
</body>
</html>
<?php
}

function show_flash() {
    $flash = take_flash();
    if (!$flash) {
        return;
    }
    ?>
    <div class="flash" role="status">
      <span><?= h($flash['message']) ?></span>
      <?php if ($flash['undo']): ?>
        <form method="post" action="index.php">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="<?= h($flash['undo']['action']) ?>">
          <input type="hidden" name="id" value="<?= (int) $flash['undo']['id'] ?>">
          <input type="hidden" name="is_undo" value="1">
          <button type="submit" class="btn btn-light">Undo</button>
        </form>
      <?php endif; ?>
    </div>
    <?php
}
