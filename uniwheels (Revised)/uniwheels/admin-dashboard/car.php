<?php
/**
 * Add a car (car.php) or edit one (car.php?id=5).
 * Only name, price and at least one photo are required; the rest is optional.
 */

require __DIR__ . '/lib.php';

const TRANSMISSIONS = ['Automatic', 'Manual'];
const BODY_TYPES = ['Sedan', 'SUV', 'Hatchback', 'MPV', 'Pickup', 'Van'];
const FUEL_TYPES = ['Gasoline', 'Diesel', 'Hybrid', 'Electric'];
const SEATINGS = ['2 Seats', '5 Seats', '7 Seats', '8 Seats', '12 Seats'];

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$car = ($pdo && $id) ? find_car($pdo, $id) : null;
if ($id && !$car) {
    flash('That car could not be found. It may have been removed.');
    header('Location: index.php');
    exit;
}
$is_new = !$car;

$defaults = [
    'name' => '', 'price' => '', 'brand' => '', 'mileage' => '', 'transmission' => 'Automatic',
    'body_type' => 'Sedan', 'fuel_type' => 'Gasoline', 'seating' => '5 Seats', 'finish' => '',
    'stock_id' => '', 'status' => 'AVAILABLE',
];
$form = $car ? array_merge($defaults, array_intersect_key($car, $defaults)) : $defaults;
$photos = $car ? car_photos($car) : [];
$errors = [];

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    foreach ($defaults as $key => $value) {
        $form[$key] = trim((string) ($_POST[$key] ?? $value));
    }
    $form['price'] = preg_replace('/[^0-9.]/', '', $form['price']);
    if (ctype_digit(str_replace(',', '', $form['mileage']))) {
        $form['mileage'] = number_format((int) str_replace(',', '', $form['mileage'])) . ' km';
    }
    if ($form['brand'] === '' && preg_match('/^(?:\d{4}\s+)?(\S+)/', $form['name'], $m)) {
        $form['brand'] = ucfirst($m[1]);
    }
    if (!isset(CAR_STATUSES[$form['status']])) {
        $form['status'] = 'AVAILABLE';
    }

    // Photos: keep the ones not ticked "remove", put the chosen cover first, add new uploads at the end.
    $remove = (array) ($_POST['remove_photo'] ?? []);
    $kept = array_values(array_diff($photos, $remove));
    $cover = $_POST['cover'] ?? '';
    if (in_array($cover, $kept, true)) {
        $kept = array_merge([$cover], array_diff($kept, [$cover]));
    }

    if ($form['name'] === '') {
        $errors['name'] = 'Please type the car name.';
    }
    if ((float) $form['price'] <= 0) {
        $errors['price'] = 'Please type the price.';
    }
    $has_new_photos = !empty($_FILES['photos']['name'][0]);
    if (!$kept && !$has_new_photos) {
        $errors['photos'] = 'Please add at least one photo.';
    }

    if (!$errors) {
        $all_photos = array_values(array_unique(array_merge($kept, save_uploaded_photos('photos', $cars_img_dir))));
        if (!$all_photos) {
            $errors['photos'] = 'The photos could not be saved. Please use JPG, PNG or WEBP photos.';
        }
    }

    if (!$errors) {
        $cover_photo = array_shift($all_photos);
        $gallery_json = $all_photos ? json_encode($all_photos) : null;
        $now_sold = in_array($form['status'], SOLD_STATUSES, true);

        if ($is_new) {
            if ($form['stock_id'] === '') {
                $form['stock_id'] = 'UW-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $form['brand']), 0, 3)) . date('ymdHi');
            }
            // wheel_name, front_fitment, rear_fitment, alloy_type and weight are old required columns the site no longer shows.
            $stmt = $pdo->prepare("INSERT INTO cars (name, brand, price, stock_id, mileage, transmission, body_type, fuel_type, seating, finish, status, sold_at, reserved_at, image_file, gallery_images, wheel_name, front_fitment, rear_fitment, alloy_type, weight)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, " . ($now_sold ? 'NOW()' : 'NULL') . ", " . ($form['status'] === 'RESERVED' ? 'NOW()' : 'NULL') . ", ?, ?, '', '', '', '', '')");
            $stmt->execute([$form['name'], $form['brand'], $form['price'], $form['stock_id'], $form['mileage'], $form['transmission'], $form['body_type'], $form['fuel_type'], $form['seating'], $form['finish'], $form['status'], $cover_photo, $gallery_json]);
            $id = (int) $pdo->lastInsertId();
            flash("{$form['name']} was added to the website.", 'remove', $id);
        } else {
            $was_sold = in_array(strtoupper($car['status']), SOLD_STATUSES, true);
            $sold_at_sql = $now_sold ? ($was_sold ? 'sold_at' : 'NOW()') : 'NULL';
            $was_reserved = strtoupper($car['status']) === 'RESERVED';
            $reserved_at_sql = $form['status'] === 'RESERVED' ? ($was_reserved ? 'COALESCE(reserved_at, NOW())' : 'NOW()') : 'NULL';
            // Lowering the price keeps the highest earlier price, so the website can show it crossed out.
            $new_price = (float) $form['price'];
            $old_price = $car['old_price'] !== null ? (float) $car['old_price'] : null;
            if ($new_price < (float) $car['price']) {
                $old_price = max((float) $old_price, (float) $car['price']);
            }
            if ($old_price !== null && ($new_price >= $old_price || !empty($_POST['hide_old_price']))) {
                $old_price = null;
            }
            $stmt = $pdo->prepare("UPDATE cars SET name = ?, brand = ?, price = ?, old_price = ?, stock_id = ?, mileage = ?, transmission = ?, body_type = ?, fuel_type = ?, seating = ?, finish = ?, status = ?, sold_at = $sold_at_sql, reserved_at = $reserved_at_sql, image_file = ?, gallery_images = ? WHERE id = ?");
            $stmt->execute([$form['name'], $form['brand'], $form['price'], $old_price, $form['stock_id'], $form['mileage'], $form['transmission'], $form['body_type'], $form['fuel_type'], $form['seating'], $form['finish'], $form['status'], $cover_photo, $gallery_json, $id]);
            flash("Changes to {$form['name']} were saved.");
        }
        $wp_sync_ids = [$id];
        require __DIR__ . '/wp-sync.php';
        header('Location: index.php' . ($now_sold ? '?show=sold' : ''));
        exit;
    }
}

$brands = $pdo ? $pdo->query("SELECT DISTINCT brand FROM cars WHERE brand <> '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN) : [];

function select_options(array $options, $current) {
    if ($current !== '' && !in_array($current, $options, true)) {
        $options[] = $current;
    }
    foreach ($options as $option) {
        echo '<option' . ($option === $current ? ' selected' : '') . '>' . h($option) . '</option>';
    }
}

function field_error($errors, $key) {
    if (isset($errors[$key])) {
        echo '<p class="field-error" id="' . h($key) . '-error">' . h($errors[$key]) . '</p>';
    }
}

page_start($is_new ? 'Add a car' : 'Edit ' . $form['name'], 'cars');
?>

<p><a href="index.php" class="link">&larr; Back to Our Cars</a></p>
<h1><?= $is_new ? 'Add a car' : 'Edit car' ?></h1>

<?php if (!$pdo): ?>
  <div class="notice notice-error">Cannot reach the database. Please make sure XAMPP (Apache and MySQL) is running.</div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="notice notice-error" role="alert">Something is missing. Please check the boxes marked in red.</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="car-form" id="car-form" novalidate>
  <?= csrf_field() ?>
  <?php if (!$is_new): ?><input type="hidden" name="id" value="<?= (int) $car['id'] ?>"><?php endif; ?>

  <section class="form-section">
    <h2>Photos <span class="required">required</span></h2>
    <?php field_error($errors, 'photos'); ?>

    <?php if ($photos): ?>
      <p class="hint">The photo marked <strong>Cover</strong> is the one people see first.</p>
      <div class="photo-list">
        <?php foreach ($photos as $i => $photo): ?>
          <div class="photo-item">
            <img src="<?= h($cars_img_url . $photo) ?>" alt="">
            <label class="choice"><input type="radio" name="cover" value="<?= h($photo) ?>" <?= $i === 0 ? 'checked' : '' ?>> Cover</label>
            <label class="choice"><input type="checkbox" name="remove_photo[]" value="<?= h($photo) ?>"> Remove</label>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="hint">The first photo you choose will be the cover photo.</p>
    <?php endif; ?>

    <label class="btn btn-light btn-big upload-btn">
      + Add photos
      <input type="file" name="photos[]" id="photos" accept="image/jpeg,image/png,image/webp" multiple hidden>
    </label>
    <div class="photo-list" id="new-photos" aria-live="polite"></div>
  </section>

  <section class="form-section">
    <h2>About the car</h2>

    <div class="field">
      <label for="name">Car name <span class="required">required</span></label>
      <input type="text" id="name" name="name" value="<?= h($form['name']) ?>" placeholder="e.g. 2021 Toyota Vios 1.3 XLE" required <?= isset($errors['name']) ? 'aria-invalid="true" aria-describedby="name-error"' : '' ?>>
      <?php field_error($errors, 'name'); ?>
    </div>

    <div class="field">
      <label for="price">Price in pesos <span class="required">required</span></label>
      <input type="text" id="price" name="price" inputmode="numeric" value="<?= h($form['price'] !== '' ? number_format((float) $form['price']) : '') ?>" placeholder="e.g. 545,000" required <?= isset($errors['price']) ? 'aria-invalid="true" aria-describedby="price-error"' : '' ?>>
      <?php field_error($errors, 'price'); ?>
      <?php if ($car && $car['old_price'] !== null): ?>
        <p class="hint">The website shows this car as <strong>reduced from <?= h(peso($car['old_price'])) ?></strong>.</p>
        <label class="choice"><input type="checkbox" name="hide_old_price" value="1"> Stop showing the old price</label>
      <?php elseif ($car): ?>
        <p class="hint">Lowering the price? The website will show the old price crossed out.</p>
      <?php endif; ?>
    </div>

    <div class="field">
      <label for="status">Is it for sale?</label>
      <select id="status" name="status">
        <?php foreach (CAR_STATUSES as $value => $label): ?>
          <option value="<?= h($value) ?>" <?= strtoupper($form['status']) === $value ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </section>

  <details class="form-section" <?= $is_new ? '' : 'open' ?>>
    <summary><h2>More details <span class="optional">optional</span></h2></summary>

    <div class="field">
      <label for="brand">Brand</label>
      <input type="text" id="brand" name="brand" list="brand-list" value="<?= h($form['brand']) ?>" placeholder="Leave empty to take it from the name">
      <datalist id="brand-list"><?php foreach ($brands as $b): ?><option value="<?= h($b) ?>"><?php endforeach; ?></datalist>
    </div>

    <div class="field">
      <label for="mileage">Mileage</label>
      <input type="text" id="mileage" name="mileage" value="<?= h($form['mileage']) ?>" placeholder="e.g. 28,400 km">
    </div>

    <div class="field">
      <label for="transmission">Transmission</label>
      <select id="transmission" name="transmission"><?php select_options(TRANSMISSIONS, $form['transmission']); ?></select>
    </div>

    <div class="field">
      <label for="body_type">Body type</label>
      <select id="body_type" name="body_type"><?php select_options(BODY_TYPES, $form['body_type']); ?></select>
    </div>

    <div class="field">
      <label for="fuel_type">Fuel</label>
      <select id="fuel_type" name="fuel_type"><?php select_options(FUEL_TYPES, $form['fuel_type']); ?></select>
    </div>

    <div class="field">
      <label for="seating">Seats</label>
      <select id="seating" name="seating"><?php select_options(SEATINGS, $form['seating']); ?></select>
    </div>

    <div class="field">
      <label for="finish">Colour</label>
      <input type="text" id="finish" name="finish" value="<?= h($form['finish']) ?>" placeholder="e.g. Pearl White">
    </div>

    <div class="field">
      <label for="stock_id">Stock number</label>
      <input type="text" id="stock_id" name="stock_id" value="<?= h($form['stock_id']) ?>" placeholder="Leave empty and we will make one">
    </div>
  </details>

  <div class="form-buttons">
    <button type="submit" class="btn btn-primary btn-big" id="save-btn"><?= $is_new ? 'Add this car' : 'Save changes' ?></button>
    <a href="index.php" class="btn btn-light btn-big">Cancel</a>
  </div>
</form>

<?php if (!$is_new): ?>
  <section class="danger-zone">
    <h2>Remove this car</h2>
    <p>Takes the car off the website. You can bring it back with <strong>Undo</strong> right after.</p>
    <form method="post" action="index.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
      <button type="submit" name="action" value="remove" class="btn btn-danger">Remove this car</button>
    </form>
  </section>
<?php endif; ?>

<?php page_end(); ?>
