<?php
/**
 * Add a car (car.php) or edit one (car.php?id=5).
 * All vehicle specifications, condition/history, and policy fields are required.
 */

require __DIR__ . '/lib.php';

const TRANSMISSIONS = ['Automatic', 'Manual'];
const BODY_TYPES = ['Sedan', 'SUV', 'Hatchback', 'MPV', 'Pickup', 'Van'];
const FUEL_TYPES = ['Gasoline', 'Diesel', 'Hybrid', 'Electric'];
const SEATINGS = ['2 Seats', '5 Seats', '7 Seats', '8 Seats', '12 Seats'];
const LISTING_TYPES = [
    'SALE' => 'For Sale',
    'RENT' => 'For Rent',
    'BOTH' => 'For Sale & Rent',
];

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$car = ($pdo && $id) ? find_car($pdo, $id) : null;
if ($id && !$car) {
    flash('That car could not be found. It may have been removed.');
    header('Location: index.php');
    exit;
}
$is_new = !$car;

$defaults = [
    'name'              => '',
    'listing_type'      => 'SALE',
    'price'             => '',
    'rental_rate'       => '',
    'booking_deposit'   => $uw_settings['default_deposit'] ?? '5000',
    'booking_policy'    => $uw_settings['default_policy'] ?? '',
    'brand'             => '',
    'model_year'        => '',
    'mileage'           => '',
    'transmission'      => 'Automatic',
    'body_type'         => 'Sedan',
    'fuel_type'         => 'Gasoline',
    'seating'           => '5 Seats',
    'finish'            => '',
    'stock_id'          => $is_new ? ('UW-' . date('ymdHi')) : '',
    'ownership_history' => '',
    'damage_status'     => 'NONE',
    'damage_report'     => '',
    'status'            => 'AVAILABLE',
];

$form = $car ? array_merge($defaults, array_intersect_key($car, $defaults)) : $defaults;
if ($car) {
    if (empty($form['model_year'])) {
        $extracted = uw_extract_year($car);
        $form['model_year'] = $extracted > 0 ? (string) $extracted : '';
    }
    if ((string) $form['booking_deposit'] === '') {
        $form['booking_deposit'] = $uw_settings['default_deposit'] ?? '5000';
    }
    if (trim((string) $form['booking_policy']) === '') {
        $form['booking_policy'] = $uw_settings['default_policy'] ?? '';
    }
    if (empty($form['damage_status'])) {
        $form['damage_status'] = 'NONE';
    }
}

$photos = $car ? car_photos($car) : [];
$existingLabels = $car ? uw_photo_labels($car) : [];
$errors = [];

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    foreach ($defaults as $key => $value) {
        $form[$key] = trim((string) ($_POST[$key] ?? $value));
    }
    $form['price'] = preg_replace('/[^0-9.]/', '', $form['price']);
    $form['rental_rate'] = preg_replace('/[^0-9.]/', '', $form['rental_rate']);
    $form['booking_deposit'] = preg_replace('/[^0-9.]/', '', $form['booking_deposit']);
    $form['model_year'] = preg_replace('/[^0-9]/', '', $form['model_year']);

    if (ctype_digit(str_replace(',', '', $form['mileage'])) && $form['mileage'] !== '') {
        $form['mileage'] = number_format((int) str_replace(',', '', $form['mileage'])) . ' km';
    }
    if (!isset(LISTING_TYPES[$form['listing_type']])) {
        $form['listing_type'] = 'SALE';
    }
    if (!isset(CAR_STATUSES[$form['status']])) {
        $form['status'] = 'AVAILABLE';
    }
    if (!isset(UW_DAMAGE_LABELS[$form['damage_status']])) {
        $form['damage_status'] = 'NONE';
    }

    // Photos: keep unselected-for-removal, put cover first, map labels cleanly.
    $remove = (array) ($_POST['remove_photo'] ?? []);
    $kept = array_values(array_diff($photos, $remove));
    $cover = $_POST['cover'] ?? '';
    if (in_array($cover, $kept, true)) {
        $kept = array_merge([$cover], array_diff($kept, [$cover]));
    }

    // Required validations (Requirement F)
    if ($form['name'] === '') {
        $errors['name'] = 'Please type the car name.';
    }
    if (in_array($form['listing_type'], ['SALE', 'BOTH'], true) && (float) $form['price'] <= 0) {
        $errors['price'] = 'Please enter the selling price.';
    }
    if (in_array($form['listing_type'], ['RENT', 'BOTH'], true) && (float) $form['rental_rate'] <= 0) {
        $errors['rental_rate'] = 'Please enter the daily rental rate.';
    }
    if ($form['booking_deposit'] === '' || (float) $form['booking_deposit'] < 0) {
        $errors['booking_deposit'] = 'Please enter the booking deposit (0 or higher).';
    }
    if ($form['booking_policy'] === '') {
        $errors['booking_policy'] = 'Please enter the booking & rental policy.';
    }
    if ($form['brand'] === '') {
        $errors['brand'] = 'Please enter the brand.';
    }
    $yr = (int) $form['model_year'];
    if ($yr < 1950 || $yr > ((int) date('Y') + 1)) {
        $errors['model_year'] = 'Please enter a valid 4-digit year model.';
    }
    if ($form['mileage'] === '') {
        $errors['mileage'] = 'Please enter the mileage.';
    }
    if ($form['transmission'] === '') {
        $errors['transmission'] = 'Please select the transmission.';
    }
    if ($form['body_type'] === '') {
        $errors['body_type'] = 'Please select the body type.';
    }
    if ($form['fuel_type'] === '') {
        $errors['fuel_type'] = 'Please select the fuel type.';
    }
    if ($form['seating'] === '') {
        $errors['seating'] = 'Please select the seating capacity.';
    }
    if ($form['finish'] === '') {
        $errors['finish'] = 'Please enter the colour.';
    }
    if ($form['stock_id'] === '') {
        $errors['stock_id'] = 'Please enter the stock number.';
    }
    if ($form['ownership_history'] === '') {
        $errors['ownership_history'] = 'Please enter the ownership history (e.g. 1 previous owner).';
    }
    if ($form['damage_report'] === '') {
        $errors['damage_report'] = 'Please enter the condition / damage disclosure report.';
    }

    $has_new_photos = !empty($_FILES['photos']['name'][0]);
    if (!$kept && !$has_new_photos) {
        $errors['photos'] = 'Please add at least one photo.';
    }

    if (!$errors) {
        $uploadedMap = save_uploaded_photos('photos', $cars_img_dir);
        $newPhotoLabels = (array) ($_POST['new_photo_labels'] ?? []);
        $postedExistingLabels = (array) ($_POST['existing_photo_labels'] ?? []);

        $labelMap = [];
        foreach ($kept as $file) {
            $cat = trim((string) ($postedExistingLabels[$file] ?? ($existingLabels[$file] ?? 'Exterior')));
            $labelMap[$file] = in_array($cat, UW_PHOTO_CATEGORIES, true) ? $cat : 'Exterior';
        }
        foreach ($uploadedMap as $idx => $file) {
            $cat = trim((string) ($newPhotoLabels[$idx] ?? 'Exterior'));
            $labelMap[$file] = in_array($cat, UW_PHOTO_CATEGORIES, true) ? $cat : 'Exterior';
        }

        $all_photos = array_values(array_unique(array_merge($kept, array_values($uploadedMap))));
        if (!$all_photos) {
            $errors['photos'] = 'The photos could not be saved. Please use JPG, PNG or WEBP photos.';
        }
    }

    if (!$errors) {
        $cover_photo = array_shift($all_photos);
        $gallery_json = $all_photos ? json_encode($all_photos) : null;
        $photo_labels_json = !empty($labelMap) ? json_encode($labelMap, JSON_UNESCAPED_UNICODE) : null;

        $priceVal = (float) ($form['price'] ?: 0);
        $rentalVal = $form['rental_rate'] !== '' ? (float) $form['rental_rate'] : null;
        $depositVal = (float) $form['booking_deposit'];
        $now_sold = in_array($form['status'], SOLD_STATUSES, true);

        if ($is_new) {
            $stmt = $pdo->prepare("INSERT INTO cars (
                    name, brand, model_year, listing_type, price, rental_rate, booking_deposit, booking_policy,
                    stock_id, mileage, transmission, body_type, fuel_type, seating, finish,
                    ownership_history, damage_status, damage_report, status, sold_at, reserved_at,
                    image_file, gallery_images, photo_labels,
                    wheel_name, front_fitment, rear_fitment, alloy_type, weight
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, " . ($now_sold ? 'NOW()' : 'NULL') . ", " . ($form['status'] === 'RESERVED' ? 'NOW()' : 'NULL') . ",
                    ?, ?, ?,
                    '', '', '', '', ''
                )");
            $stmt->execute([
                $form['name'], $form['brand'], (int) $form['model_year'], $form['listing_type'],
                $priceVal, $rentalVal, $depositVal, $form['booking_policy'],
                $form['stock_id'], $form['mileage'], $form['transmission'], $form['body_type'],
                $form['fuel_type'], $form['seating'], $form['finish'],
                $form['ownership_history'], $form['damage_status'], $form['damage_report'],
                $form['status'], $cover_photo, $gallery_json, $photo_labels_json,
            ]);
            $id = (int) $pdo->lastInsertId();
            flash("{$form['name']} was added to the website.", 'remove', $id);
        } else {
            $was_sold = in_array(strtoupper($car['status']), SOLD_STATUSES, true);
            $sold_at_sql = $now_sold ? ($was_sold ? 'sold_at' : 'NOW()') : 'NULL';
            $was_reserved = strtoupper($car['status']) === 'RESERVED';
            $reserved_at_sql = $form['status'] === 'RESERVED' ? ($was_reserved ? 'COALESCE(reserved_at, NOW())' : 'NOW()') : 'NULL';
            $reserve_exp_sql = $form['status'] === 'RESERVED' ? 'reserve_expires_at' : 'NULL';

            $old_price = $car['old_price'] !== null ? (float) $car['old_price'] : null;
            if ($priceVal > 0 && $priceVal < (float) $car['price']) {
                $old_price = max((float) $old_price, (float) $car['price']);
            }
            if ($old_price !== null && ($priceVal >= $old_price || !empty($_POST['hide_old_price']))) {
                $old_price = null;
            }

            $stmt = $pdo->prepare("UPDATE cars SET
                    name = ?, brand = ?, model_year = ?, listing_type = ?, price = ?, old_price = ?,
                    rental_rate = ?, booking_deposit = ?, booking_policy = ?,
                    stock_id = ?, mileage = ?, transmission = ?, body_type = ?, fuel_type = ?, seating = ?, finish = ?,
                    ownership_history = ?, damage_status = ?, damage_report = ?,
                    status = ?, sold_at = $sold_at_sql, reserved_at = $reserved_at_sql, reserve_expires_at = $reserve_exp_sql,
                    image_file = ?, gallery_images = ?, photo_labels = ?
                WHERE id = ?");
            $stmt->execute([
                $form['name'], $form['brand'], (int) $form['model_year'], $form['listing_type'],
                $priceVal, $old_price, $rentalVal, $depositVal, $form['booking_policy'],
                $form['stock_id'], $form['mileage'], $form['transmission'], $form['body_type'],
                $form['fuel_type'], $form['seating'], $form['finish'],
                $form['ownership_history'], $form['damage_status'], $form['damage_report'],
                $form['status'], $cover_photo, $gallery_json, $photo_labels_json, $id,
            ]);
            flash("Changes to {$form['name']} were saved.");
        }

        $wp_sync_ids = [$id];
        require __DIR__ . '/wp-sync.php';

        $redirectShow = '';
        if ($now_sold) {
            $redirectShow = '?show=sold';
        } elseif ($form['status'] === 'RESERVED') {
            $redirectShow = '?show=reserved';
        } elseif (in_array($form['status'], ['RENTED', 'MAINTENANCE'], true)) {
            $redirectShow = '?show=other';
        }
        header('Location: index.php' . $redirectShow);
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
  <div class="notice notice-error" role="alert">Please complete all required fields marked below.</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="car-form" id="car-form" novalidate>
  <?= csrf_field() ?>
  <?php if (!$is_new): ?><input type="hidden" name="id" value="<?= (int) $car['id'] ?>"><?php endif; ?>

  <section class="form-section">
    <h2>Photos</h2>
    <p class="hint">Upload exterior, interior, trunk, dashboard, tires, and damage close-up photos. Photos are automatically compressed for fast mobile loading.</p>
    <?php field_error($errors, 'photos'); ?>

    <?php if ($photos): ?>
      <p class="hint">The photo marked <strong>Cover</strong> appears first on the showroom grid.</p>
      <div class="photo-list">
        <?php foreach ($photos as $i => $photo):
          $curCat = $existingLabels[$photo] ?? ($i === 0 ? 'Exterior' : 'Other');
        ?>
          <div class="photo-item">
            <img src="<?= h($cars_img_url . $photo) ?>" alt="">
            <select name="existing_photo_labels[<?= h($photo) ?>]" class="photo-cat-select" aria-label="Photo category">
              <?php foreach (UW_PHOTO_CATEGORIES as $cat): ?>
                <option value="<?= h($cat) ?>" <?= $curCat === $cat ? 'selected' : '' ?>><?= h($cat) ?></option>
              <?php endforeach; ?>
            </select>
            <label class="choice"><input type="radio" name="cover" value="<?= h($photo) ?>" <?= $i === 0 ? 'checked' : '' ?>> Cover</label>
            <label class="choice"><input type="checkbox" name="remove_photo[]" value="<?= h($photo) ?>"> Remove</label>
          </div>
        <?php endforeach; ?>
      </div>
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

    <div class="field-row-2">
      <div class="field">
        <label for="listing_type">Listing type <span class="required">required</span></label>
        <select id="listing_type" name="listing_type" required>
          <?php foreach (LISTING_TYPES as $val => $label): ?>
            <option value="<?= h($val) ?>" <?= strtoupper($form['listing_type']) === $val ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label for="status">Availability status <span class="required">required</span></label>
        <select id="status" name="status" required>
          <?php foreach (CAR_STATUSES as $value => $label): ?>
            <option value="<?= h($value) ?>" <?= strtoupper($form['status']) === $value ? 'selected' : '' ?>><?= h($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="field-row-2">
      <div class="field" id="sale-price-field">
        <label for="price">Selling price in pesos <span class="required" id="price-req-tag">required</span></label>
        <input type="text" id="price" name="price" inputmode="numeric" value="<?= h($form['price'] !== '' && (float) $form['price'] > 0 ? number_format((float) $form['price']) : '') ?>" placeholder="e.g. 545,000" <?= isset($errors['price']) ? 'aria-invalid="true" aria-describedby="price-error"' : '' ?>>
        <?php field_error($errors, 'price'); ?>
        <?php if ($car && $car['old_price'] !== null): ?>
          <p class="hint">Showing reduced from <strong><?= h(peso($car['old_price'])) ?></strong>.</p>
          <label class="choice"><input type="checkbox" name="hide_old_price" value="1"> Stop showing old price</label>
        <?php endif; ?>
      </div>

      <div class="field" id="rental-rate-field">
        <label for="rental_rate">Daily rental rate (PHP / day) <span class="required" id="rate-req-tag">required for rent</span></label>
        <input type="text" id="rental_rate" name="rental_rate" inputmode="numeric" value="<?= h($form['rental_rate'] !== '' && (float) $form['rental_rate'] > 0 ? number_format((float) $form['rental_rate']) : '') ?>" placeholder="e.g. 2,200" <?= isset($errors['rental_rate']) ? 'aria-invalid="true" aria-describedby="rental_rate-error"' : '' ?>>
        <?php field_error($errors, 'rental_rate'); ?>
      </div>
    </div>

    <div class="field">
      <label for="booking_deposit">Reservation / Booking deposit (PHP) <span class="required">required</span></label>
      <input type="text" id="booking_deposit" name="booking_deposit" inputmode="numeric" value="<?= h($form['booking_deposit'] !== '' ? number_format((float) $form['booking_deposit']) : '') ?>" placeholder="e.g. 5,000" required <?= isset($errors['booking_deposit']) ? 'aria-invalid="true"' : '' ?>>
      <?php field_error($errors, 'booking_deposit'); ?>
    </div>
  </section>

  <!-- Requirement F: More details are REQUIRED (never optional or hidden) -->
  <section class="form-section">
    <h2>More details</h2>

    <div class="field-row-2">
      <div class="field">
        <label for="brand">Brand <span class="required">required</span></label>
        <input type="text" id="brand" name="brand" list="brand-list" value="<?= h($form['brand']) ?>" placeholder="e.g. Toyota" required <?= isset($errors['brand']) ? 'aria-invalid="true"' : '' ?>>
        <datalist id="brand-list"><?php foreach ($brands as $b): ?><option value="<?= h($b) ?>"><?php endforeach; ?></datalist>
        <?php field_error($errors, 'brand'); ?>
      </div>

      <div class="field">
        <label for="model_year">Year model <span class="required">required</span></label>
        <input type="number" id="model_year" name="model_year" min="1950" max="<?= (int) date('Y') + 1 ?>" value="<?= h($form['model_year']) ?>" placeholder="e.g. 2021" required <?= isset($errors['model_year']) ? 'aria-invalid="true"' : '' ?>>
        <?php field_error($errors, 'model_year'); ?>
      </div>
    </div>

    <div class="field-row-2">
      <div class="field">
        <label for="mileage">Mileage <span class="required">required</span></label>
        <input type="text" id="mileage" name="mileage" value="<?= h($form['mileage']) ?>" placeholder="e.g. 28,400 km" required <?= isset($errors['mileage']) ? 'aria-invalid="true"' : '' ?>>
        <?php field_error($errors, 'mileage'); ?>
      </div>

      <div class="field">
        <label for="stock_id">Stock number <span class="required">required</span></label>
        <input type="text" id="stock_id" name="stock_id" value="<?= h($form['stock_id']) ?>" placeholder="e.g. UW-TOY01" required <?= isset($errors['stock_id']) ? 'aria-invalid="true"' : '' ?>>
        <?php field_error($errors, 'stock_id'); ?>
      </div>
    </div>

    <div class="field-row-2">
      <div class="field">
        <label for="transmission">Transmission <span class="required">required</span></label>
        <select id="transmission" name="transmission" required><?php select_options(TRANSMISSIONS, $form['transmission']); ?></select>
        <?php field_error($errors, 'transmission'); ?>
      </div>

      <div class="field">
        <label for="body_type">Body type <span class="required">required</span></label>
        <select id="body_type" name="body_type" required><?php select_options(BODY_TYPES, $form['body_type']); ?></select>
        <?php field_error($errors, 'body_type'); ?>
      </div>
    </div>

    <div class="field-row-2">
      <div class="field">
        <label for="fuel_type">Fuel <span class="required">required</span></label>
        <select id="fuel_type" name="fuel_type" required><?php select_options(FUEL_TYPES, $form['fuel_type']); ?></select>
        <?php field_error($errors, 'fuel_type'); ?>
      </div>

      <div class="field">
        <label for="seating">Seats <span class="required">required</span></label>
        <select id="seating" name="seating" required><?php select_options(SEATINGS, $form['seating']); ?></select>
        <?php field_error($errors, 'seating'); ?>
      </div>
    </div>

    <div class="field">
      <label for="finish">Colour <span class="required">required</span></label>
      <input type="text" id="finish" name="finish" value="<?= h($form['finish']) ?>" placeholder="e.g. Pearl White" required <?= isset($errors['finish']) ? 'aria-invalid="true"' : '' ?>>
      <?php field_error($errors, 'finish'); ?>
    </div>
  </section>

  <!-- Requirement A: Vehicle Condition, Ownership History & Policies -->
  <section class="form-section">
    <h2>Vehicle Condition, History &amp; Policy</h2>

    <div class="field">
      <label for="ownership_history">Ownership history <span class="required">required</span></label>
      <input type="text" id="ownership_history" name="ownership_history" value="<?= h($form['ownership_history']) ?>" placeholder="e.g. 1 previous owner or Company-owned since 2021" required <?= isset($errors['ownership_history']) ? 'aria-invalid="true"' : '' ?>>
      <?php field_error($errors, 'ownership_history'); ?>
    </div>

    <div class="field">
      <label for="damage_status">Condition / Damage status <span class="required">required</span></label>
      <select id="damage_status" name="damage_status" required>
        <?php foreach (UW_DAMAGE_LABELS as $code => $label): ?>
          <option value="<?= h($code) ?>" <?= strtoupper($form['damage_status']) === $code ? 'selected' : '' ?>><?= h($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="damage_report">Damage disclosure &amp; condition report <span class="required">required</span></label>
      <textarea id="damage_report" name="damage_report" rows="3" placeholder="e.g. Minor scratch on rear bumper — photographed and documented before booking" required <?= isset($errors['damage_report']) ? 'aria-invalid="true"' : '' ?>><?= h($form['damage_report']) ?></textarea>
      <?php field_error($errors, 'damage_report'); ?>
    </div>

    <div class="field">
      <label for="booking_policy">Booking &amp; rental policy <span class="required">required</span></label>
      <textarea id="booking_policy" name="booking_policy" rows="3" required <?= isset($errors['booking_policy']) ? 'aria-invalid="true"' : '' ?>><?= h($form['booking_policy']) ?></textarea>
      <?php field_error($errors, 'booking_policy'); ?>
    </div>
  </section>

  <div class="form-buttons">
    <button type="submit" class="btn btn-primary btn-big" id="save-btn"><?= $is_new ? 'Add this car' : 'Save changes' ?></button>
    <a href="index.php" class="btn btn-light btn-big">Cancel</a>
  </div>
</form>

<?php if (!$is_new): ?>
  <section class="danger-zone">
    <h2>Remove this car</h2>
    <p>Takes the car off the website. You can bring it back with <strong>Undo</strong> right after.</p>
    <form method="post" action="index.php" id="remove-car-form">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $car['id'] ?>">
      <input type="hidden" name="action" value="remove">
      <button type="submit" class="btn btn-danger" id="remove-car-trigger">Remove this car</button>
    </form>
  </section>

  <div class="confirm-modal" id="remove-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="remove-modal-title" hidden>
    <div class="confirm-modal-backdrop" data-close-modal></div>
    <div class="confirm-modal-box">
      <h2 id="remove-modal-title">Remove this car unit?</h2>
      <p>Are you sure you want to remove <strong><?= h($form['name']) ?></strong> from the website? You can cancel if you are not sure, or confirm to remove this unit.</p>
      <div class="confirm-modal-actions">
        <button type="button" class="btn btn-light" id="remove-cancel-btn" data-close-modal>Cancel</button>
        <button type="button" class="btn btn-danger confirm-danger-solid" id="remove-confirm-btn">Yes, remove unit</button>
      </div>
    </div>
  </div>
<?php endif; ?>

<script>
  window.UW_PHOTO_CATEGORIES = <?= json_encode(UW_PHOTO_CATEGORIES) ?>;
</script>

<?php page_end(); ?>
