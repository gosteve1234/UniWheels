<?php
/**
 * UniWheels Motors - Public Showroom Homepage
 */

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/app.php';

uw_ensure_schema($pdo);
uw_release_expired_holds($pdo);
$settings = uw_get_settings($pdo);

function esc($s) {
    return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');
}

const SOLD_CARS_SHOWN = 4;

function car_images(array $car) {
    $imgs = [];
    if (!empty($car['image_file'])) {
        $imgs[] = $car['image_file'];
    }
    if (!empty($car['gallery_images'])) {
        $extra = json_decode($car['gallery_images'], true);
        foreach ((array) $extra as $img) {
            if ($img && !in_array($img, $imgs, true)) {
                $imgs[] = $img;
            }
        }
    }
    return $imgs ?: ['car-1.jpg'];
}

function car_price_label($price, $status = 'AVAILABLE') {
    $price = (float) $price;
    if ($price > 0) {
        return '₱' . number_format($price);
    }
    return strtoupper((string) $status) === 'MAINTENANCE' ? 'N/A' : 'Ask for price';
}

$cars = [];
if ($pdo) {
    try {
        $cars = $pdo->query("SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        $cars = [];
    }
} elseif (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)) {
    $cars = require __DIR__ . '/sample-cars.php';
}

$active_cars = [];
$sold_recent_cars = [];
$now_ts = time();

foreach ($cars as $car) {
    $meta = uw_status_meta($car['status'] ?? 'AVAILABLE');
    $car['status'] = $meta['key'];
    $car['status_label'] = $meta['label'];
    $car['status_class'] = $meta['class'];
    $car['all_images'] = car_images($car);
    $car['photo_labels_map'] = uw_photo_labels($car);
    $car['age_label'] = uw_car_age_label($car);
    $car['model_year_resolved'] = uw_extract_year($car);
    $car['damage_status_label'] = uw_damage_label($car['damage_status'] ?? 'NONE');
    $isMaintenance = $car['status'] === 'MAINTENANCE';
    $hasDeposit = isset($car['booking_deposit']) && $car['booking_deposit'] !== '' && $car['booking_deposit'] !== null;
    if ($isMaintenance && !$hasDeposit) {
        $car['effective_deposit'] = null;
    } else {
        $car['effective_deposit'] = (float) ($car['booking_deposit'] ?? 0) > 0
            ? (float) $car['booking_deposit']
            : ($isMaintenance ? null : (float) ($settings['default_deposit'] ?? 5000));
    }
    $car['effective_policy'] = trim((string) ($car['booking_policy'] ?? '')) !== ''
        ? trim((string) $car['booking_policy'])
        : $settings['default_policy'];
    $car['reserve_expires_ts'] = !empty($car['reserve_expires_at']) ? strtotime($car['reserve_expires_at']) : null;
    $car['reserve_expires_label'] = !empty($car['reserve_expires_at']) ? date('F j, Y, g:i A', strtotime($car['reserve_expires_at'])) : null;

    $car['is_sold'] = $car['status'] === 'SOLD';
    $car['is_traded'] = $car['status'] === 'TRADED';
    $car['is_available'] = $car['status'] === 'AVAILABLE';
    $car['is_reduced'] = !$car['is_sold'] && !$car['is_traded'] && (float) ($car['old_price'] ?? 0) > (float) ($car['price'] ?? 0) && (float) ($car['price'] ?? 0) > 0;

    if ($car['is_traded']) {
        continue;
    }
    if ($car['is_sold']) {
        $sold_ts = !empty($car['sold_at']) ? strtotime($car['sold_at']) : false;
        if ($sold_ts !== false) {
            $days_ago = max(0, (int) floor(($now_ts - $sold_ts) / 86400));
            if ($days_ago <= 30) {
                $car['sold_days_ago'] = $days_ago;
                $car['sold_ago_label'] = $days_ago === 0
                    ? 'Sold today'
                    : ($days_ago === 1 ? 'Sold 1 day ago' : "Sold {$days_ago} days ago");
                $car['_sold_ts'] = $sold_ts;
                $sold_recent_cars[] = $car;
            }
        }
    } else {
        $active_cars[] = $car;
    }
}

// Keep Available units at the top, followed by Reserved, then Under Maintenance.
usort($active_cars, function ($a, $b) {
    $rank = fn($s) => match ($s) {
        'AVAILABLE'   => 0,
        'RESERVED'    => 1,
        'MAINTENANCE' => 2,
        default       => 3,
    };
    $ra = $rank($a['status']);
    $rb = $rank($b['status']);
    if ($ra !== $rb) {
        return $ra <=> $rb;
    }
    return ((int) $b['id']) <=> ((int) $a['id']);
});

usort($sold_recent_cars, fn($a, $b) => ($b['_sold_ts'] <=> $a['_sold_ts']) ?: (((int) $b['id']) <=> ((int) $a['id'])));

$showroom_cars = array_merge($active_cars, $sold_recent_cars);

function distinct_values(array $cars, $field) {
    $values = [];
    foreach ($cars as $car) {
        $v = trim($car[$field] ?? '');
        if ($v !== '') {
            $values[strtolower($v)] = $v;
        }
    }
    asort($values);
    return $values;
}

const PRICE_RANGES = [
    'under-500k' => ['Under ₱500,000', 0, 500000],
    '500k-800k'  => ['₱500,000 – ₱800,000', 500000, 800000],
    '800k-1m'    => ['₱800,000 – ₱1,000,000', 800000, 1000000],
    'over-1m'    => ['Above ₱1,000,000', 1000000, INF],
];

function price_range_key($price) {
    foreach (PRICE_RANGES as $key => [, $min, $max]) {
        if ($price > 0 && $price >= $min && $price < $max) {
            return $key;
        }
    }
    return '';
}

$filter_options = [
    'brand' => ['Brand', distinct_values($active_cars, 'brand')],
    'price' => ['Budget', []],
    'body'  => ['Body type', distinct_values($active_cars, 'body_type')],
    'trans' => ['Transmission', distinct_values($active_cars, 'transmission')],
];
foreach ($active_cars as $car) {
    $key = price_range_key((float) ($car['price'] ?? 0));
    if ($key) {
        $filter_options['price'][1][$key] = PRICE_RANGES[$key][0];
    }
}
uksort($filter_options['price'][1], fn($a, $b) => array_search($a, array_keys(PRICE_RANGES)) <=> array_search($b, array_keys(PRICE_RANGES)));
$filter_options = array_filter($filter_options, fn($f) => count($f[1]) > 1);
$show_filters = count($active_cars) > 1;
$messenger_url = $settings['messenger_url'] ?: UW_MESSENGER_DEFAULT;

include __DIR__ . '/header.php';
?>

<main id="mainContent">

  <section id="top" class="hero-section">
    <div class="container hero-grid">
      <div class="hero-copy">
        <span class="section-tag-pill">Ride Your Dreams</span>
        <h1 class="hero-main-title"><?php echo esc($settings['hero_title']); ?></h1>
        <p class="hero-lead-text"><?php echo esc($settings['hero_subtitle']); ?></p>

        <div class="hero-cta-row">
          <a href="#inventoryCatalogSection" class="btn-hero-primary">See Our Cars</a>
          <a href="<?php echo esc($messenger_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-hero-messenger">
            <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
            <span>Message Us</span>
          </a>
        </div>

        <ul class="hero-trust-list">
          <li>Original LTO papers</li>
          <li>Verified vehicle history</li>
          <li>Hassle-free showroom visits</li>
        </ul>
      </div>

      <div class="hero-photo-wrap">
        <img src="assets/images/hero.jpg" alt="A car on the UniWheels Motors showroom floor" class="hero-photo">
      </div>
    </div>
  </section>

  <section id="inventoryCatalogSection" class="catalog-section">
    <div class="container">

      <div class="catalog-header-row">
        <div>
          <span class="section-tag-pill">Showroom Floor</span>
          <h2 class="section-title-large">On the Floor Now</h2>
          <p class="section-desc-text">Inspect condition reports, zoom photos, and inquire directly on Messenger.</p>
        </div>
      </div>

      <?php if ($show_filters): ?>
        <div class="catalog-filters-card" id="catalogFilters">
          <div class="filters-grid">
            <div class="filter-field search-field">
              <label class="filter-label" for="filterSearch">Search</label>
              <div class="input-with-icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="search" id="filterSearch" class="filter-input" data-filter="search" placeholder="e.g. Vios, Civic, Fortuner">
              </div>
            </div>

            <?php foreach ($filter_options as $name => [$label, $options]): ?>
              <div class="filter-field">
                <label class="filter-label" for="filter-<?php echo $name; ?>"><?php echo esc($label); ?></label>
                <select id="filter-<?php echo $name; ?>" class="filter-select" data-filter="<?php echo $name; ?>">
                  <option value="">Any</option>
                  <?php foreach ($options as $value => $text): ?>
                    <option value="<?php echo esc($value); ?>"><?php echo esc($text); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="filter-results-row">
            <span id="filterCount" aria-live="polite"></span>
            <button type="button" class="btn-clear-filters" id="filterClear" hidden>Clear filters</button>
          </div>
        </div>
      <?php endif; ?>

      <div class="no-results-box" id="filterNoResults" hidden>
        <h3>No cars match those filters</h3>
        <p>Try clearing a filter, or message us what you're looking for.</p>
        <a href="<?php echo esc($messenger_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-reset-filters">Tell Us What You Need</a>
      </div>

      <?php if (empty($active_cars)): ?>
        <div class="no-results-box">
          <h3>New arrivals are on the way</h3>
          <p>Message us what you're looking for and we'll notify you first.</p>
          <a href="<?php echo esc($messenger_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-reset-filters">Tell Us What You Need</a>
        </div>
      <?php endif; ?>

      <?php if (!empty($active_cars)): ?>
        <div class="inventory-cards-grid" id="activeInventoryGrid">
          <?php foreach ($active_cars as $i => $car):
            $ageShort = '';
            if ($car['model_year_resolved'] > 0) {
                $yrs = max(0, (int) date('Y') - $car['model_year_resolved']);
                $ageShort = $yrs === 0 ? 'Current yr' : "{$yrs} " . ($yrs === 1 ? 'yr' : 'yrs') . ' old';
            }
            $specs = array_filter([$ageShort, $car['mileage'] ?? '', $car['transmission'] ?? '', $car['fuel_type'] ?? '']);
            $photo_count = count($car['all_images']);
          ?>
            <article class="car-card"
                     data-car-id="<?php echo (int) $car['id']; ?>"
                     data-car-index="<?php echo $i; ?>"
                     data-sold="0"
                     data-status="<?php echo esc($car['status']); ?>"
                     data-search="<?php echo esc(strtolower(($car['brand'] ?? '') . ' ' . $car['name'])); ?>"
                     data-brand="<?php echo esc(strtolower(trim($car['brand'] ?? ''))); ?>"
                     data-price="<?php echo esc(price_range_key((float) ($car['price'] ?? 0))); ?>"
                     data-body="<?php echo esc(strtolower(trim($car['body_type'] ?? ''))); ?>"
                     data-trans="<?php echo esc(strtolower(trim($car['transmission'] ?? ''))); ?>">
              <button type="button" class="car-card-img-wrap js-car-details" aria-label="Photos and details: <?php echo esc($car['name']); ?>">
                <img src="assets/images/cars/<?php echo esc($car['all_images'][0]); ?>" alt="<?php echo esc($car['name']); ?>" class="car-card-photo" loading="lazy" onerror="this.onerror=null;this.src='assets/images/cars/car-1.jpg'">
                <span class="card-status-badge <?php echo esc($car['status_class']); ?> js-card-status-badge"><?php echo esc($car['status_label']); ?></span>
                <?php if ($car['is_reduced']): ?>
                  <span class="card-status-badge badge-reduced card-badge-secondary">Price reduced</span>
                <?php endif; ?>
                <?php if ($photo_count > 1): ?>
                  <span class="card-photo-count"><?php echo $photo_count; ?> photos</span>
                <?php endif; ?>
              </button>

              <div class="car-card-body">
                <h3 class="car-card-title"><?php echo esc($car['name']); ?></h3>
                <div class="car-card-price">
                  <?php echo esc(car_price_label($car['price'] ?? 0, $car['status'])); ?>
                  <?php if ($car['is_reduced']): ?>
                    <s class="car-card-old-price"><?php echo esc(car_price_label($car['old_price'])); ?></s>
                  <?php endif; ?>
                </div>
                <?php if ($specs): ?>
                  <p class="car-card-specs"><?php echo esc(implode(' · ', $specs)); ?></p>
                <?php endif; ?>

                <div class="car-card-actions js-card-actions">
                  <?php if ($car['is_available']): ?>
                    <button type="button" class="btn-card-book js-car-inquire">Inquire on Messenger</button>
                  <?php else: ?>
                    <button type="button" class="btn-card-book btn-card-disabled" disabled><?php echo esc($car['status_label']); ?></button>
                  <?php endif; ?>
                  <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($sold_recent_cars)): ?>
        <div class="sold-showcase-block" id="soldThisMonthSection">
          <div class="sold-showcase-header">
            <div>
              <span class="section-tag-pill sold-pill-tag">Recent Sales</span>
              <h3 class="sold-showcase-title">Sold This Month</h3>
              <p class="sold-showcase-sub">Verified units sold within the last 30 days.</p>
            </div>
          </div>
          <div class="inventory-cards-grid">
            <?php
              $active_offset = count($active_cars);
              foreach ($sold_recent_cars as $j => $car):
                $ageShort = '';
                if ($car['model_year_resolved'] > 0) {
                    $yrs = max(0, (int) date('Y') - $car['model_year_resolved']);
                    $ageShort = $yrs === 0 ? 'Current yr' : "{$yrs} " . ($yrs === 1 ? 'yr' : 'yrs') . ' old';
                }
                $specs = array_filter([$ageShort, $car['mileage'] ?? '', $car['transmission'] ?? '', $car['fuel_type'] ?? '']);
                $photo_count = count($car['all_images']);
            ?>
              <article class="car-card is-sold"
                       data-car-id="<?php echo (int) $car['id']; ?>"
                       data-car-index="<?php echo $active_offset + $j; ?>"
                       data-sold="1"
                       data-status="SOLD">
                <button type="button" class="car-card-img-wrap js-car-details" aria-label="Photos and details: <?php echo esc($car['name']); ?>">
                  <img src="assets/images/cars/<?php echo esc($car['all_images'][0]); ?>" alt="<?php echo esc($car['name']); ?>" class="car-card-photo" loading="lazy" onerror="this.onerror=null;this.src='assets/images/cars/car-1.jpg'">
                  <span class="card-status-badge <?php echo esc($car['status_class']); ?>"><?php echo esc($car['sold_ago_label']); ?></span>
                  <?php if ($photo_count > 1): ?>
                    <span class="card-photo-count"><?php echo $photo_count; ?> photos</span>
                  <?php endif; ?>
                </button>

                <div class="car-card-body">
                  <h3 class="car-card-title"><?php echo esc($car['name']); ?></h3>
                  <div class="car-card-price">Sold</div>
                  <?php if ($specs): ?>
                    <p class="car-card-specs"><?php echo esc(implode(' · ', $specs)); ?></p>
                  <?php endif; ?>

                  <div class="car-card-actions">
                    <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <section id="tradeInSection" class="tradein-section">
    <div class="container">
      <div class="tradein-banner-card">
        <div class="tradein-header-content">
          <h2 class="tradein-title">Trade In Your Car in 3 Easy Steps</h2>
          <p class="tradein-subtext">
            We accept all major brands in good running condition with a 15-minute market appraisal.
          </p>
        </div>

        <div class="tradein-steps-grid">
          <div class="step-card">
            <div class="step-badge">1</div>
            <h3 class="step-title">Share Your Car Info</h3>
            <p class="step-desc">Send your make, model, year, and mileage on Messenger or visit our showroom.</p>
          </div>

          <div class="step-card">
            <div class="step-badge">2</div>
            <h3 class="step-title">15-Minute Appraisal</h3>
            <p class="step-desc">Get a transparent valuation on the spot with no hidden deductions.</p>
          </div>

          <div class="step-card">
            <div class="step-badge">3</div>
            <h3 class="step-title">Drive Your Upgrade</h3>
            <p class="step-desc">Apply the trade-in value toward your chosen unit while we handle the papers.</p>
          </div>
        </div>

        <div class="tradein-action-bar">
          <a href="<?php echo esc($messenger_url); ?>?text=Hello%20UniWheels!%20I%20would%20like%20to%20get%20a%20free%20trade-in%20appraisal%20for%20my%20car." target="_blank" rel="noopener noreferrer" class="btn-tradein-messenger">
            <svg viewBox="0 0 28 28" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
            <span>Request Free Trade-In Appraisal</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <section id="inquirySection" class="inquiry-section">
    <div class="container">
      <div class="showroom-visit-card">

        <div class="visit-header-block">
          <span class="section-tag-pill">Visit &amp; Contact</span>
          <h2 class="section-title-large">See Our Cars In Person</h2>
          <p class="section-desc-text">
            Inspect the vehicle, review original documents, and take a test drive at our showroom.
          </p>
        </div>

        <div class="showroom-highlights-grid">
          <div class="highlight-item">
            <h4 class="highlight-title">Free Test Drives</h4>
            <p class="highlight-desc">Check the engine, cold AC, and handling safely around our lot.</p>
          </div>

          <div class="highlight-item">
            <h4 class="highlight-title">Original Documents</h4>
            <p class="highlight-desc">Review original OR/CR, LTO receipts, and service records on site.</p>
          </div>

          <div class="highlight-item">
            <h4 class="highlight-title">Customer Lounge</h4>
            <p class="highlight-desc">Complimentary refreshments while our staff prepares your paperwork.</p>
          </div>
        </div>

        <div class="showroom-contact-details">
          <div class="contact-box-grid">
            <div class="contact-box-item">
              <span class="contact-box-label">Showroom Location</span>
              <strong class="contact-box-value"><?php echo esc($settings['showroom_address']); ?></strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Operating Hours</span>
              <strong class="contact-box-value"><?php echo esc($settings['showroom_hours']); ?></strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Phone &amp; Mobile</span>
              <strong class="contact-box-value"><?php echo esc($settings['showroom_phone']); ?></strong>
            </div>

            <div class="contact-box-item contact-box-action">
              <span class="contact-box-label">Instant Chat</span>
              <a href="<?php echo esc($messenger_url); ?>" target="_blank" rel="noopener noreferrer" class="link-showroom-chat">
                Chat on Messenger &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<script>
  window.UNIWHEELS_CARS = <?php echo json_encode(array_values($showroom_cars), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
  window.UNIWHEELS_MESSENGER_URL = <?php echo json_encode($messenger_url); ?>;
  window.UNIWHEELS_SETTINGS = <?php echo json_encode([
      'defaultDeposit' => (float) ($settings['default_deposit'] ?? 5000),
      'maxHoldDays'    => (int) ($settings['max_hold_days'] ?? 7),
      'defaultPolicy'  => $settings['default_policy'],
  ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
</script>

<?php
include __DIR__ . '/footer.php';
