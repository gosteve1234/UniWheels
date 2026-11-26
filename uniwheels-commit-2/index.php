<?php
/**
 * UniWheels Motors - Certified Pre-Owned & Trade-In Specialists (Standalone Public Showroom)
 * Upgraded with Add-ons A–E: Vehicle Condition & History, Magnifier, Confirmation Popups,
 * Real-Time Reservation Hold & Countdown, Auto-Release, and ECMS Content Integration.
 */

require_once __DIR__ . '/includes/uniwheels-core.php';

// Helper: Safe HTML escaping
if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($s) { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$uw_pdo = uniwheels_get_pdo();
if ($uw_pdo) {
    uniwheels_ensure_schema($uw_pdo);
    uniwheels_release_expired_holds($uw_pdo);
    $GLOBALS['uniwheels_settings'] = uniwheels_get_settings($uw_pdo);
} else {
    $GLOBALS['uniwheels_settings'] = uniwheels_default_settings();
}
$uw_settings = $GLOBALS['uniwheels_settings'];

$hero_cars = [];
$catalog_cars = [];
$catalog_brands = [];
$status_counts = ['AVAILABLE' => 0, 'RESERVED' => 0, 'UNDER MAINTENANCE' => 0, 'SOLD' => 0];

if ($uw_pdo) {
    // Map active reservations by booking_ref for hold_days info
    $active_res_map = [];
    try {
        $res_rows = $uw_pdo->query("SELECT * FROM `reservations` WHERE `status` = 'ACTIVE'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res_rows as $rr) {
            $active_res_map[$rr['booking_ref']] = $rr;
        }
    } catch (Exception $e) {}

    // 1. Featured / spotlight cars (prioritize AVAILABLE & RESERVED)
    $raw_hero = $uw_pdo->query("SELECT * FROM `cars` WHERE `status` IN ('AVAILABLE', 'RESERVED') ORDER BY `is_featured` DESC, `is_recommended` DESC, `id` ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($raw_hero as $rc) {
        $ref = $rc['active_reservation_ref'] ?? '';
        $hero_cars[] = uniwheels_prepare_public_car($rc, $uw_settings, $active_res_map[$ref] ?? null);
    }

    // 2. Full catalog inventory sorted cleanly: AVAILABLE -> RESERVED -> UNDER MAINTENANCE -> SOLD
    $raw_catalog = $uw_pdo->query("
        SELECT * FROM `cars`
        ORDER BY FIELD(UPPER(`status`), 'AVAILABLE', 'RESERVED', 'UNDER MAINTENANCE', 'SOLD'), `is_featured` DESC, `id` ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($raw_catalog as $rc) {
        $st_key = strtoupper($rc['status'] ?: 'AVAILABLE');
        if (isset($status_counts[$st_key])) {
            $status_counts[$st_key]++;
        }
        $ref = $rc['active_reservation_ref'] ?? '';
        $catalog_cars[] = uniwheels_prepare_public_car($rc, $uw_settings, $active_res_map[$ref] ?? null);
    }

    // 3. Unique brands
    $catalog_brands = $uw_pdo->query("SELECT DISTINCT `brand` FROM `cars` WHERE `brand` IS NOT NULL AND `brand` != '' ORDER BY `brand` ASC")->fetchAll(PDO::FETCH_COLUMN);
}

// Fallback if database empty
if (empty($catalog_cars)) {
    $fallback = uniwheels_prepare_public_car([
        'id' => 1,
        'name' => '2021 Toyota Vios 1.3 XLE',
        'year_model' => 2021,
        'brand' => 'Toyota',
        'price' => 545000,
        'price_note' => 'Negotiable',
        'deposit_amount' => 10000,
        'mileage' => '28,400 km',
        'transmission' => 'Automatic',
        'body_type' => 'Sedan',
        'fuel_type' => 'Gasoline',
        'seating' => '5 Seats',
        'status' => 'AVAILABLE',
        'stock_id' => 'UW-TOY01',
        'plate_number' => 'DBN 2465',
        'image_file' => 'car-1.jpg',
        'gallery_images' => json_encode(['car-1-rear.jpg', 'car-1-interior.jpg', 'detail-dashboard-1.jpg', 'detail-tires-1.jpg', 'detail-damage-bumper-1.jpg']),
        'finish' => 'Silver Metallic',
        'ownership_history' => '1 previous owner — Casa maintained',
        'damage_disclosure' => 'Minor scratch on rear bumper — photographed and documented before reservation.'
    ], $uw_settings);
    $catalog_cars = [$fallback];
    $hero_cars = [$fallback];
    $catalog_brands = ['Toyota'];
    $status_counts['AVAILABLE'] = 1;
}
if (empty($hero_cars)) {
    $hero_cars = array_slice($catalog_cars, 0, 8);
}

$messenger_base = !empty($uw_settings['messenger_url']) ? $uw_settings['messenger_url'] : 'https://m.me/uniwheels';

include __DIR__ . '/header.php';
?>

<main id="mainContent">

  <!-- ========================================================
       1. HERO FEATURED SHOWCASE (ECMS-Driven + Magnifier Trigger)
       ======================================================== -->
  <section id="showcase" class="hero-showcase-section">
    <div class="container">
      
      <!-- Welcoming Dealership Tagline (Editable via Admin ECMS) -->
      <div class="hero-header-box">
        <h1 class="hero-main-title">
          <?php echo esc_html($uw_settings['hero_title_prefix']); ?>
          <span class="highlight-text"><?php echo esc_html($uw_settings['hero_title_highlight']); ?></span>
        </h1>
        <p class="hero-lead-text">
          <?php echo esc_html($uw_settings['hero_subtitle']); ?>
        </p>
      </div>

      <!-- Featured Vehicle Stage Card -->
      <div class="showcase-card-stage" id="showcaseStage">
        <?php 
          $first_car = $hero_cars[0];
          $f_price = floatval($first_car['price'] ?? 0);
          $f_price_str = $f_price > 0 ? ('₱' . number_format($f_price, 0)) : 'Inquire for Price';
          $f_img = !empty($first_car['image_file']) ? $first_car['image_file'] : 'car-1.jpg';
          $f_img_url = 'assets/images/cars/' . $f_img;
          $f_status = strtoupper(!empty($first_car['status']) ? $first_car['status'] : 'AVAILABLE');
          $f_status_slug = strtolower(str_replace(' ', '-', $f_status));
        ?>

        <div class="showcase-grid">
          <!-- Left: Big Vehicle Photo with Hover Zoom & Controls -->
          <div class="showcase-image-col">
            <div class="showcase-image-wrap magnifier-container" id="heroMagnifierWrap" title="Hover or tap to inspect vehicle details">
              <img id="showcaseImg" src="<?php echo esc_url($f_img_url); ?>" alt="<?php echo esc_attr($first_car['name']); ?>" class="showcase-main-photo magnifier-source-img" decoding="async" onerror="this.src='assets/images/cars/car-1.jpg'">
              <div class="magnifier-lens" id="heroMagnifierLens" aria-hidden="true"></div>
              <div class="showcase-badge-overlay">
                <span class="card-status-badge badge-<?php echo esc_attr($f_status_slug); ?>" id="showcaseStatusTag"><?php echo esc_html($f_status); ?></span>
                <span class="age-pill-badge" id="showcaseAgeBadge"><?php echo esc_html($first_car['short_age']); ?></span>
              </div>
              <span class="card-stock-tag" id="showcaseStockId">#<?php echo esc_html(strtoupper($first_car['stock_id'] ?: 'UW-01')); ?></span>

              <div class="magnifier-hint-bar">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                <span>Tap or hover on the photo to inspect vehicle details.</span>
              </div>

              <!-- Slideshow Indicator Dots -->
              <div class="showcase-photo-dots" id="showcasePhotoDots"></div>
            </div>

            <!-- Big, Friendly Next/Prev Navigation Buttons -->
            <div class="showcase-nav-row">
              <button type="button" class="btn-showcase-nav" id="showcasePrevBtn" aria-label="Previous Featured Car">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span>Previous Car</span>
              </button>
              <div class="showcase-counter" id="showcaseCounter">
                Spotlight <strong id="currentCarIdx">1</strong> of <?php echo count($hero_cars); ?>
              </div>
              <button type="button" class="btn-showcase-nav" id="showcaseNextBtn" aria-label="Next Featured Car">
                <span>Next Car</span>
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
              </button>
            </div>
          </div>

          <!-- Right: Specs, Honest Condition Preview & Reservation/Details Actions -->
          <div class="showcase-details-col">
            <h2 class="showcase-car-name" id="showcaseCarName"><?php echo esc_html($first_car['name']); ?> — <span class="car-age-inline" id="showcaseCarAgeInline"><?php echo esc_html($first_car['age_label']); ?></span></h2>
            
            <div class="showcase-price-box">
              <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                  <div class="price-label">Selling Price (<span id="showcasePriceNote"><?php echo esc_html($first_car['price_note'] ?? 'Negotiable'); ?></span>):</div>
                  <div class="price-value" id="showcasePriceValue"><?php echo esc_html($f_price_str); ?></div>
                </div>
                <div class="deposit-chip-box">
                  <span class="deposit-chip-label">Hold Deposit</span>
                  <strong class="deposit-chip-val" id="showcaseDepositVal">₱<?php echo number_format(floatval($first_car['deposit_amount'] ?? 10000), 0); ?></strong>
                </div>
              </div>
            </div>

            <!-- Live Countdown Banner if Hero Car is Reserved -->
            <div id="showcaseCountdownBanner" class="live-hold-banner" style="display:none;"></div>

            <!-- Everyday Driver Specifications + Ownership Preview -->
            <div class="showcase-specs-list" id="showcaseSpecsList">
              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Transmission &amp; Fuel</span>
                  <strong class="spec-v" id="specTrans"><?php echo esc_html(($first_car['transmission'] ?? 'Automatic') . ' • ' . ($first_car['fuel_type'] ?? 'Gasoline')); ?></strong>
                </div>
              </div>

              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Odometer</span>
                  <strong class="spec-v" id="specMileage"><?php echo esc_html($first_car['mileage'] ?? 'Low Mileage'); ?></strong>
                </div>
              </div>

              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Exterior &amp; Seating</span>
                  <strong class="spec-v" id="specSeats"><?php echo esc_html(($first_car['finish'] ?? 'Clean') . ' • ' . ($first_car['seating'] ?? '5 Seats')); ?></strong>
                </div>
              </div>

              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Ownership History</span>
                  <strong class="spec-v" id="specOwnership"><?php echo esc_html($first_car['ownership_history'] ?? '1 previous owner'); ?></strong>
                </div>
              </div>
            </div>

            <!-- Action Buttons: Reserve Car, View Full Profile & Condition, Messenger -->
            <div class="showcase-cta-group">
              <div class="showcase-cta-row-2">
                <button type="button" class="btn-reserve-primary" id="showcaseReserveBtn">
                  <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                  <span id="showcaseReserveBtnText">Book / Reserve This Car</span>
                </button>

                <button type="button" class="btn-showcase-details" id="showcaseDetailsBtn" title="Inspect condition history, damage report, policies &amp; zoomable gallery">
                  <span>Condition &amp; Details</span>
                </button>
              </div>

              <button type="button" class="btn-primary-messenger" id="showcaseMessengerBtn" title="Inquire about this car on Facebook Messenger">
                <svg viewBox="0 0 28 28" width="20" height="20" fill="currentColor">
                  <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                </svg>
                <span>Inquire via Messenger</span>
              </button>
            </div>

          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ========================================================
       2. CERTIFIED PRE-OWNED INVENTORY CATALOG (105 Price List Units)
       ======================================================== -->
  <section id="inventoryCatalogSection" class="catalog-section">
    <div class="container">
      
      <div class="catalog-header-row">
        <div>
          <span class="section-tag-pill"><?php echo esc_html($uw_settings['catalog_tag']); ?></span>
          <h2 class="section-title-large"><?php echo esc_html($uw_settings['catalog_title']); ?></h2>
          <p class="section-desc-text"><?php echo esc_html($uw_settings['catalog_subtitle']); ?></p>
        </div>
        <div class="availability-summary-pills">
          <span class="avail-pill pill-available">● <strong id="countAvailStat"><?php echo (int)$status_counts['AVAILABLE']; ?></strong> Available</span>
          <span class="avail-pill pill-reserved">⏳ <strong id="countResStat"><?php echo (int)$status_counts['RESERVED']; ?></strong> Reserved</span>
          <?php if ($status_counts['UNDER MAINTENANCE'] > 0): ?>
            <span class="avail-pill pill-maint">🔧 <strong><?php echo (int)$status_counts['UNDER MAINTENANCE']; ?></strong> Maintenance</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Friendly Filter Bar -->
      <div class="catalog-filters-card">
        <div class="filters-grid filters-grid-6">
          
          <!-- Search Bar -->
          <div class="filter-field search-field">
            <label class="filter-label" for="catalogSearchInput">Search Make, Model, Year, Color or Stock #</label>
            <div class="input-with-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              <input type="text" id="catalogSearchInput" class="filter-input" placeholder="e.g. 2024 Toyota Vios, Hilux, Montero, Hybrid...">
            </div>
          </div>

          <!-- Brand / Make -->
          <div class="filter-field">
            <label class="filter-label" for="filterBrand">Brand / Make</label>
            <select id="filterBrand" class="filter-select">
              <option value="all">All Brands (<?php echo count($catalog_brands); ?>)</option>
              <?php foreach ($catalog_brands as $b): ?>
                <option value="<?php echo esc_attr(strtolower($b)); ?>"><?php echo esc_html($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Budget Range -->
          <div class="filter-field">
            <label class="filter-label" for="filterBudget">Price Range</label>
            <select id="filterBudget" class="filter-select">
              <option value="all">All Prices</option>
              <option value="under-500k">Under ₱500,000</option>
              <option value="500k-800k">₱500,000 – ₱800,000</option>
              <option value="800k-1.2m">₱800,000 – ₱1,200,000</option>
              <option value="1.2m-2m">₱1,200,000 – ₱2,000,000</option>
              <option value="over-2m">Above ₱2,000,000</option>
            </select>
          </div>

          <!-- Body Configuration -->
          <div class="filter-field">
            <label class="filter-label" for="filterBodyType">Category</label>
            <select id="filterBodyType" class="filter-select">
              <option value="all">All Categories</option>
              <option value="sedan">Sedan</option>
              <option value="hatchback">Hatchback</option>
              <option value="suv">S.U.V.</option>
              <option value="mpv">7-Seater MPV</option>
              <option value="pickup">Pick Up Truck</option>
              <option value="van">Van / Trucks</option>
              <option value="motorcycle">Motorcycle</option>
            </select>
          </div>

          <!-- Real-Time Status -->
          <div class="filter-field">
            <label class="filter-label" for="filterStatus">Availability</label>
            <select id="filterStatus" class="filter-select">
              <option value="all">All Statuses</option>
              <option value="available">Available Only</option>
              <option value="reserved">Reserved / On Hold</option>
              <option value="under maintenance">Under Maintenance</option>
              <option value="sold">Sold</option>
            </select>
          </div>

          <!-- Transmission -->
          <div class="filter-field">
            <label class="filter-label" for="filterTransmission">Transmission</label>
            <select id="filterTransmission" class="filter-select">
              <option value="all">All</option>
              <option value="automatic">Automatic (A/T)</option>
              <option value="manual">Manual (M/T)</option>
            </select>
          </div>

        </div>

        <div class="filter-results-meta-bar">
          <span id="filterResultCount" class="filter-result-count">Showing <?php echo count($catalog_cars); ?> Vehicles</span>
          <button type="button" class="btn-inline-reset" onclick="resetAllCatalogFilters()">Reset Filters</button>
        </div>
      </div>

      <!-- Inventory Grid of Cars -->
      <div id="catalogGrid" class="inventory-cards-grid">
        <?php foreach ($catalog_cars as $car): 
          $c_id = (int)$car['id'];
          $c_price = floatval($car['price'] ?: 0);
          $c_price_str = $c_price > 0 ? ('₱' . number_format($c_price, 0)) : 'Inquire for Price';
          $c_orig = floatval($car['original_price'] ?? 0);
          $c_brand = strtolower($car['brand'] ?? 'toyota');
          $c_trans = strtolower($car['transmission'] ?? 'automatic');
          $c_body = strtolower($car['body_type'] ?? 'sedan');
          $c_stock = strtoupper($car['stock_id'] ?: ('UW-' . $c_id));
          $c_mileage = $car['mileage'] ?: 'Low Mileage';
          $c_seating = $car['seating'] ?: '5 Seats';
          $c_img = !empty($car['image_file']) ? $car['image_file'] : 'car-1.jpg';
          $c_img_url = 'assets/images/cars/' . $c_img;
          $c_status = strtoupper(!empty($car['status']) ? $car['status'] : 'AVAILABLE');
          $c_status_slug = strtolower(str_replace(' ', '-', $c_status));
          $c_all_images = !empty($car['all_images']) ? $car['all_images'] : [$c_img];
          $c_is_avail = ($c_status === 'AVAILABLE');
        ?>
          <div class="car-card <?php echo ($c_status === 'SOLD') ? 'is-sold' : ''; ?> <?php echo ($c_status === 'UNDER MAINTENANCE') ? 'is-maintenance' : ''; ?>" 
               id="carCard-<?php echo $c_id; ?>"
               data-id="<?php echo esc_attr($c_id); ?>"
               data-brand="<?php echo esc_attr($c_brand); ?>"
               data-price="<?php echo esc_attr($c_price); ?>"
               data-trans="<?php echo esc_attr($c_trans); ?>"
               data-body="<?php echo esc_attr($c_body); ?>"
               data-status="<?php echo esc_attr(strtolower($c_status)); ?>"
               data-name="<?php echo esc_attr(strtolower($car['name'] . ' ' . ($car['finish'] ?? '') . ' ' . ($car['fuel_type'] ?? ''))); ?>"
               data-stock="<?php echo esc_attr(strtolower($c_stock . ' ' . ($car['plate_number'] ?? ''))); ?>"
               data-images="<?php echo esc_attr(json_encode($c_all_images)); ?>">

            <!-- Car Photo & Real-Time Status Badge -->
            <div class="car-card-img-wrap" onclick="openCarDetailsById(<?php echo $c_id; ?>)" style="cursor:pointer;" title="Click to inspect full profile &amp; zoomable photos">
              <img src="<?php echo esc_url($c_img_url); ?>" alt="<?php echo esc_attr($car['name']); ?>" class="car-card-photo" loading="lazy" decoding="async" onerror="this.src='assets/images/cars/car-1.jpg'">
              <span class="card-status-badge badge-<?php echo esc_attr($c_status_slug); ?>" id="cardBadge-<?php echo $c_id; ?>"><?php echo esc_html($c_status); ?></span>
              <span class="card-stock-tag">#<?php echo esc_html($c_stock); ?></span>
              <span class="card-photo-count">🔍 <?php echo count($c_all_images); ?> Photos</span>
            </div>

            <!-- Card Body Content -->
            <div class="car-card-body">
              <div class="car-price-header">
                <div class="car-card-price"><?php echo esc_html($c_price_str); ?></div>
                <?php if ($c_orig > $c_price): ?>
                  <span class="car-markdown-pill" title="Price reduced">Markdown</span>
                <?php endif; ?>
              </div>

              <!-- Vehicle Title + Explicit Vehicle Age (Requirement A) -->
              <h3 class="car-card-title">
                <?php echo esc_html($car['name']); ?>
                <span class="car-card-age-sub">— <?php echo esc_html($car['short_age']); ?></span>
              </h3>
              
              <!-- Quick Specs Chips -->
              <div class="car-specs-chips">
                <span class="chip-item"><?php echo esc_html($car['transmission'] ?? 'Automatic'); ?></span>
                <span class="chip-item"><?php echo esc_html($c_mileage); ?></span>
                <span class="chip-item"><?php echo esc_html($car['fuel_type'] ?? 'Gasoline'); ?></span>
                <span class="chip-item"><?php echo esc_html($car['finish'] ?? 'Clean'); ?></span>
              </div>

              <!-- Ownership & Condition Transparency Snippet (Requirement A) -->
              <div class="card-condition-mini">
                <div class="cond-mini-line"><strong>History:</strong> <?php echo esc_html($car['ownership_history']); ?></div>
                <div class="cond-mini-line cond-mini-disclosure"><strong>Condition:</strong> Documented &amp; photographed</div>
              </div>

              <!-- Live Hold Countdown Strip when RESERVED (Requirement C) -->
              <div class="card-hold-countdown" id="cardHoldBox-<?php echo $c_id; ?>"
                   <?php if ($c_status === 'RESERVED' && !empty($car['reserved_until_iso'])): ?>
                     data-expires="<?php echo esc_attr($car['reserved_until_iso']); ?>"
                     data-days="<?php echo esc_attr($car['reserved_hold_days'] ?: 2); ?>"
                   <?php else: ?>
                     style="display:none;"
                   <?php endif; ?>>
                <?php if ($c_status === 'RESERVED' && !empty($car['reserved_until_formatted'])): ?>
                  ⏳ Reserved — Hold expires <?php echo esc_html($car['reserved_until_formatted']); ?>
                <?php endif; ?>
              </div>

              <!-- Action Buttons -->
              <div class="car-card-actions">
                <div class="card-actions-split">
                  <button type="button" class="btn-card-details" onclick="openCarDetailsById(<?php echo $c_id; ?>)">
                    <span>Inspect &amp; Details</span>
                  </button>

                  <button type="button"
                          class="btn-card-reserve <?php echo !$c_is_avail ? 'is-disabled' : ''; ?>"
                          id="cardReserveBtn-<?php echo $c_id; ?>"
                          onclick="initiateCarBookingById(<?php echo $c_id; ?>)"
                          <?php echo !$c_is_avail ? 'disabled' : ''; ?>>
                    <span><?php echo $c_is_avail ? 'Book / Reserve' : esc_html($c_status); ?></span>
                  </button>
                </div>

                <button type="button" class="btn-card-messenger" onclick="inquireMessengerById(<?php echo $c_id; ?>)" title="Inquire immediately on Messenger">
                  <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor">
                    <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                  </svg>
                  <span>Inquire via Messenger</span>
                </button>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Load More Button for Fast Mobile Rendering -->
      <div id="loadMoreWrap" class="load-more-wrap" style="display:none;">
        <button type="button" id="loadMoreCarsBtn" class="btn-load-more">
          Show More Vehicles
        </button>
      </div>

      <!-- No Matching Results Message -->
      <div id="noCatalogResults" class="no-results-box" style="display:none;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
        <h3>No Vehicles Matched Your Filter</h3>
        <p>Try clearing your price or category filter, or search for another brand.</p>
        <button type="button" class="btn-reset-filters" onclick="resetAllCatalogFilters()">Reset All Filters</button>
      </div>

    </div>
  </section>

  <!-- ========================================================
       3. CLEAR RESERVATION & VEHICLE CONDITION POLICIES (Requirement A & E)
       ======================================================== -->
  <section id="policiesSection" class="policies-section">
    <div class="container">
      <div class="catalog-header-row" style="margin-bottom:1.75rem;">
        <div>
          <span class="section-tag-pill">Customer Protection &amp; Transparency</span>
          <h2 class="section-title-large">Clear Reservation, Hold &amp; Inspection Policies</h2>
          <p class="section-desc-text">Know every detail upfront before placing a hold or completing your purchase.</p>
        </div>
      </div>

      <div class="policies-cards-grid">
        <div class="policy-card">
          <div class="policy-card-num">01 • Reservation Hold</div>
          <h3 class="policy-card-title">Temporary Unit Hold (1–<?php echo intval($uw_settings['max_hold_days'] ?? 7); ?> Days)</h3>
          <p class="policy-card-desc"><?php echo esc_html($uw_settings['policy_reservation']); ?></p>
        </div>

        <div class="policy-card">
          <div class="policy-card-num">02 • Automatic Release</div>
          <h3 class="policy-card-title">Countdown &amp; Fair Availability</h3>
          <p class="policy-card-desc"><?php echo esc_html($uw_settings['policy_release']); ?></p>
        </div>

        <div class="policy-card">
          <div class="policy-card-num">03 • Condition &amp; Damage Report</div>
          <h3 class="policy-card-title">Photographed Honest Disclosure</h3>
          <p class="policy-card-desc"><?php echo esc_html($uw_settings['policy_condition']); ?></p>
        </div>

        <div class="policy-card">
          <div class="policy-card-num">04 • Documentation &amp; LTO</div>
          <h3 class="policy-card-title">Verified Ownership &amp; OR/CR</h3>
          <p class="policy-card-desc"><?php echo esc_html($uw_settings['policy_papers']); ?></p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================================================
       4. TRADE-IN SPECIALISTS SECTION
       ======================================================== -->
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
          <a href="<?php echo esc_url($messenger_base . '?text=Hello%20UniWheels!%20I%20would%20like%20to%20get%20a%20free%20trade-in%20appraisal%20for%20my%20car.'); ?>" target="_blank" rel="noopener noreferrer" class="btn-tradein-messenger">
            <svg viewBox="0 0 28 28" width="22" height="22" fill="currentColor">
              <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
            </svg>
            <span>Request Free Trade-In Appraisal on Messenger</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- ========================================================
       5. SHOWROOM VISIT & DIRECT CONTACT SECTION (ECMS-Driven)
       ======================================================== -->
  <section id="inquirySection" class="inquiry-section">
    <div class="container">
      <div class="showroom-visit-card">
        
        <div class="visit-header-block">
          <span class="section-tag-pill">Visit &amp; Contact</span>
          <h2 class="section-title-large">Come See Our Cars In Person</h2>
          <p class="section-desc-text">
            Nothing beats seeing, touching, and test-driving a car yourself. Visit our bright and spacious showroom floor. Our friendly sales staff will gladly hand you the keys for an inspection.
          </p>
        </div>

        <div class="showroom-highlights-grid">
          <div class="highlight-item">
            <h4 class="highlight-title">Free Test Drives &amp; Cold Starts</h4>
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

        <!-- Direct Contact Details (Editable from Admin ECMS) -->
        <div class="showroom-contact-details">
          <div class="contact-box-grid">
            <div class="contact-box-item">
              <span class="contact-box-label">Showroom Location</span>
              <strong class="contact-box-value"><?php echo esc_html($uw_settings['contact_address']); ?></strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Operating Hours</span>
              <strong class="contact-box-value"><?php echo esc_html($uw_settings['contact_hours']); ?></strong>
            </div>

            <div class="contact-box-item">
              <span class="contact-box-label">Phone &amp; Mobile Inquiries</span>
              <strong class="contact-box-value"><?php echo esc_html($uw_settings['contact_phone']); ?></strong>
            </div>

            <div class="contact-box-item contact-box-action">
              <span class="contact-box-label">Instant Chat</span>
              <a href="<?php echo esc_url($messenger_base); ?>" target="_blank" rel="noopener noreferrer" class="link-showroom-chat">
                Chat Directly on Messenger &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<!-- Public Vehicle & ECMS Data Injected Safely (Admin-only agent names already stripped) -->
<script>
  window.UNIWHEELS_FEATURED_CARS = <?php echo json_encode(array_values($hero_cars), JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  window.UNIWHEELS_CATALOG_CARS  = <?php echo json_encode(array_values($catalog_cars), JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  window.UNIWHEELS_SETTINGS      = <?php echo json_encode($uw_settings, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
  window.THEME_BASE_URL          = '.';
  window.UNIWHEELS_API_URL       = './api.php';
</script>

<?php
include __DIR__ . '/footer.php';
