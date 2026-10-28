<?php
/**
 * UniWheels Motors - Public Showroom Homepage
 *
 * Built to need as little upkeep as possible: the hero is static (swap
 * assets/images/hero.jpg to change it) and the only thing that changes day to
 * day is the car list, which comes straight from the `cars` table.
 */

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';

function esc($s) {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}

const MESSENGER_URL = 'https://m.me/uniwheels';
const SOLD_STATUSES = ['SOLD', 'OUT OF STOCK'];
const SOLD_CARS_SHOWN = 4; // a few recent "SOLD" cards show the lot is moving

/** Collect the main photo plus any gallery photos into one list. */
function car_images(array $car) {
    $imgs = [];
    if (!empty($car['image_file'])) {
        $imgs[] = $car['image_file'];
    }
    if (!empty($car['gallery_images'])) {
        $extra = json_decode($car['gallery_images'], true);
        foreach ((array)$extra as $img) {
            if ($img && !in_array($img, $imgs, true)) {
                $imgs[] = $img;
            }
        }
    }
    return $imgs ?: ['car-1.jpg'];
}

function car_price_label($price) {
    $price = (float)$price;
    return $price > 0 ? '₱' . number_format($price) : 'Ask for price';
}

$cars = [];
if ($pdo) {
    try {
        $cars = $pdo->query("SELECT * FROM cars WHERE deleted_at IS NULL ORDER BY id DESC")->fetchAll();
    } catch (PDOException $e) {
        $cars = [];
    }
} elseif (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)) {
    // Local preview without a database: show sample cars so the layout can be checked.
    // Never on the live site, where fake cars would mislead buyers.
    $cars = require __DIR__ . '/sample-cars.php';
}

$available_cars = [];
$sold_cars = [];
foreach ($cars as $car) {
    $car['status'] = strtoupper(trim($car['status'] ?? '')) ?: 'AVAILABLE';
    $car['all_images'] = car_images($car);
    $car['is_sold'] = in_array($car['status'], SOLD_STATUSES, true);
    $car['is_reduced'] = !$car['is_sold'] && (float)($car['old_price'] ?? 0) > (float)($car['price'] ?? 0);
    if ($car['is_sold']) {
        $sold_cars[] = $car;
    } else {
        $available_cars[] = $car;
    }
}
$showroom_cars = array_merge($available_cars, array_slice($sold_cars, 0, SOLD_CARS_SHOWN));

// Filter options come from the cars in stock, so they never list something we don't have.
function distinct_values(array $cars, $field) {
    $values = [];
    foreach ($cars as $car) {
        $v = trim($car[$field] ?? '');
        if ($v !== '') {
            $values[strtolower($v)] = $v;
        }
    }
    asort($values);
    return $values; // slug => label
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
    'brand' => ['Brand', distinct_values($available_cars, 'brand')],
    'price' => ['Budget', []],
    'body'  => ['Body type', distinct_values($available_cars, 'body_type')],
    'trans' => ['Transmission', distinct_values($available_cars, 'transmission')],
];
foreach ($available_cars as $car) {
    $key = price_range_key((float)($car['price'] ?? 0));
    if ($key) {
        $filter_options['price'][1][$key] = PRICE_RANGES[$key][0];
    }
}
uksort($filter_options['price'][1], fn($a, $b) => array_search($a, array_keys(PRICE_RANGES)) <=> array_search($b, array_keys(PRICE_RANGES)));
// A dropdown with only one choice is pointless, so drop it.
$filter_options = array_filter($filter_options, fn($f) => count($f[1]) > 1);
$show_filters = count($available_cars) > 1;

include __DIR__ . '/header.php';
?>

<main id="mainContent">

  <!-- 1. HERO: static on purpose, so it never goes out of date -->
  <section id="top" class="hero-section">
    <div class="container hero-grid">
      <div class="hero-copy">
        <span class="section-tag-pill">Ride Your Dreams</span>
        <h1 class="hero-main-title">
          Quality secondhand cars <span class="highlight-text">you can trust</span>
        </h1>
        <p class="hero-lead-text">
          Hand-picked, inspected, and complete with original LTO papers. Come see them on our showroom floor, or message us and we'll answer right away.
        </p>

        <div class="hero-cta-row">
          <a href="#inventoryCatalogSection" class="btn-hero-primary">See Our Cars</a>
          <a href="<?php echo esc(MESSENGER_URL); ?>" target="_blank" rel="noopener noreferrer" class="btn-hero-messenger">
            <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
            <span>Message Us</span>
          </a>
        </div>

        <ul class="hero-trust-list">
          <li>Original LTO papers</li>
          <li>Trade-ins accepted</li>
          <li>Free test drives</li>
        </ul>
      </div>

      <div class="hero-photo-wrap">
        <img src="assets/images/hero.jpg" alt="A car on the UniWheels Motors showroom floor" class="hero-photo">
      </div>
    </div>
  </section>

  <!-- 2. SHOWROOM FLOOR: simple photo grid, no filters -->
  <section id="inventoryCatalogSection" class="catalog-section">
    <div class="container">

      <div class="catalog-header-row">
        <div>
          <span class="section-tag-pill">Showroom Floor</span>
          <h2 class="section-title-large">On the Floor Now</h2>
          <p class="section-desc-text">Tap a car for photos and details, or ask us about it on Messenger.</p>
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
        <p>We get new units every week. Tell us what you're looking for and we'll message you when it arrives.</p>
        <a href="<?php echo esc(MESSENGER_URL); ?>" target="_blank" rel="noopener noreferrer" class="btn-reset-filters">Tell Us What You Need</a>
      </div>

      <?php if (empty($available_cars)): ?>
        <div class="no-results-box">
          <h3>New arrivals are on the way</h3>
          <p>We get fresh units every week. Message us what you're looking for and we'll let you know as soon as it arrives.</p>
          <a href="<?php echo esc(MESSENGER_URL); ?>" target="_blank" rel="noopener noreferrer" class="btn-reset-filters">Tell Us What You Need</a>
        </div>
      <?php endif; ?>

      <?php if (!empty($showroom_cars)): ?>
        <div class="inventory-cards-grid">
          <?php foreach ($showroom_cars as $i => $car):
            $specs = array_filter([$car['transmission'] ?? '', $car['mileage'] ?? '', $car['fuel_type'] ?? '']);
            $photo_count = count($car['all_images']);
          ?>
            <article class="car-card<?php echo $car['is_sold'] ? ' is-sold' : ''; ?>"
                     data-car-index="<?php echo $i; ?>"
                     data-sold="<?php echo $car['is_sold'] ? '1' : '0'; ?>"
                     data-search="<?php echo esc(strtolower(($car['brand'] ?? '') . ' ' . $car['name'])); ?>"
                     data-brand="<?php echo esc(strtolower(trim($car['brand'] ?? ''))); ?>"
                     data-price="<?php echo esc(price_range_key((float)($car['price'] ?? 0))); ?>"
                     data-body="<?php echo esc(strtolower(trim($car['body_type'] ?? ''))); ?>"
                     data-trans="<?php echo esc(strtolower(trim($car['transmission'] ?? ''))); ?>">
              <button type="button" class="car-card-img-wrap js-car-details" aria-label="Photos and details: <?php echo esc($car['name']); ?>">
                <img src="assets/images/cars/<?php echo esc($car['all_images'][0]); ?>" alt="<?php echo esc($car['name']); ?>" class="car-card-photo" loading="lazy" onerror="this.onerror=null;this.src='assets/images/cars/car-1.jpg'">
                <?php if ($car['is_sold']): ?>
                  <span class="card-status-badge badge-sold">Sold</span>
                <?php elseif ($car['status'] === 'RESERVED'): ?>
                  <span class="card-status-badge badge-reserved">Reserved</span>
                <?php elseif ($car['is_reduced']): ?>
                  <span class="card-status-badge badge-reduced">Price reduced</span>
                <?php endif; ?>
                <?php if ($photo_count > 1): ?>
                  <span class="card-photo-count"><?php echo $photo_count; ?> photos</span>
                <?php endif; ?>
              </button>

              <div class="car-card-body">
                <h3 class="car-card-title"><?php echo esc($car['name']); ?></h3>
                <div class="car-card-price"><?php echo esc($car['is_sold'] ? 'Sold' : car_price_label($car['price'] ?? 0)); ?><?php if ($car['is_reduced']): ?> <s class="car-card-old-price"><?php echo esc(car_price_label($car['old_price'])); ?></s><?php endif; ?></div>
                <?php if ($specs): ?>
                  <p class="car-card-specs"><?php echo esc(implode(' · ', $specs)); ?></p>
                <?php endif; ?>

                <div class="car-card-actions">
                  <?php if (!$car['is_sold']): ?>
                    <button type="button" class="btn-card-messenger js-car-inquire">
                      <svg viewBox="0 0 28 28" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
                      <span>Ask about this car</span>
                    </button>
                  <?php endif; ?>
                  <button type="button" class="btn-card-link js-car-details">Photos &amp; details</button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- 3. TRADE-IN -->
  <section id="tradeInSection" class="tradein-section">
    <div class="container">
      <div class="tradein-banner-card">
        <div class="tradein-header-content">
          <h2 class="tradein-title">Trade In Your Old Car In 3 Easy Steps</h2>
          <p class="tradein-subtext">
            Upgrade your daily driver with zero hassle. We accept all major brands and models in good running condition. Receive an honest on-the-spot market appraisal you can apply toward any car in our showroom.
          </p>
        </div>

        <div class="tradein-steps-grid">
          <div class="step-card">
            <div class="step-badge">1</div>
            <h3 class="step-title">Tell Us About Your Car</h3>
            <p class="step-desc">Send us your car details (make, model, year, mileage) via Messenger, or bring it directly to our showroom for quick visual check.</p>
          </div>

          <div class="step-card">
            <div class="step-badge">2</div>
            <h3 class="step-title">Free Fair Appraisal</h3>
            <p class="step-desc">Our certified car appraisers give you a transparent, competitive valuation within 15 minutes with no hidden deductions.</p>
          </div>

          <div class="step-card">
            <div class="step-badge">3</div>
            <h3 class="step-title">Drive Home Your Upgrade</h3>
            <p class="step-desc">Use your agreed trade-in value as direct payment or downpayment. We handle the paperwork and you drive home your new car today!</p>
          </div>
        </div>

        <div class="tradein-action-bar">
          <a href="<?php echo esc(MESSENGER_URL); ?>?text=Hello%20UniWheels!%20I%20would%20like%20to%20get%20a%20free%20trade-in%20appraisal%20for%20my%20car." target="_blank" rel="noopener noreferrer" class="btn-tradein-messenger">
            <svg viewBox="0 0 28 28" width="22" height="22" fill="currentColor" aria-hidden="true"><path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/></svg>
            <span>Request Free Trade-In Appraisal on Messenger</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- 4. VISIT & CONTACT -->
  <section id="inquirySection" class="inquiry-section">
    <div class="container">
      <div class="showroom-visit-card">

        <div class="visit-header-block">
          <span class="section-tag-pill">Visit & Contact</span>
          <h2 class="section-title-large">Come See Our Cars In Person</h2>
          <p class="section-desc-text">
            Nothing beats seeing, touching, and test-driving a car yourself. Visit our bright and spacious showroom floor. Our friendly sales staff will gladly hand you the keys for an inspection.
          </p>
        </div>

        <div class="showroom-highlights-grid">
          <div class="highlight-item">
            <h4 class="highlight-title">Free Test Drives & Cold Starts</h4>
            <p class="highlight-desc">Start the engine, test the cold AC, and test-drive safely around our commercial area.</p>
          </div>

          <div class="highlight-item">
            <h4 class="highlight-title">Transparent Document Viewing</h4>
            <p class="highlight-desc">Inspect the original OR/CR, LTO verified receipts, and maintenance history on the spot.</p>
          </div>

          <div class="highlight-item">
            <h4 class="highlight-title">Comfortable Customer Lounge</h4>
            <p class="highlight-desc">Relax with complimentary refreshments while our team prepares your appraisal or papers.</p>
          </div>
        </div>

        <div class="showroom-contact-details">
          <div class="contact-box-grid">
            <div class="contact-box-item">
              <span class="contact-box-label">Showroom Location</span>
              <strong class="contact-box-value">123 Car City Commercial Highway</strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Operating Hours</span>
              <strong class="contact-box-value">Open Everyday: 8:30 AM – 6:30 PM</strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Phone & Mobile Inquiries</span>
              <strong class="contact-box-value">(02) 8123-4567 / 0917-123-4567</strong>
            </div>

            <div class="contact-box-item contact-box-action">
              <span class="contact-box-label">Instant Chat</span>
              <a href="<?php echo esc(MESSENGER_URL); ?>" target="_blank" rel="noopener noreferrer" class="link-showroom-chat">
                Chat Directly on Messenger &rarr;
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
  window.UNIWHEELS_MESSENGER_URL = <?php echo json_encode(MESSENGER_URL); ?>;
</script>

<?php
include __DIR__ . '/footer.php';
