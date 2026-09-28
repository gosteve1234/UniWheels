<?php
/**
 * UniWheels Front Page Template
 *
 * @package UniWheels
 */

get_header();

global $wpdb;
$db_cars = [];
$all_cars = [];
$table_exists = $wpdb->get_var("SHOW TABLES LIKE 'cars'");

if ($table_exists) {
    // 1. Get all RECOMMENDED cars for hero casino spinning showcase (excluding SOLD)
    $hero_cars = $wpdb->get_results("SELECT * FROM cars WHERE is_recommended = 1 AND status != 'SOLD' ORDER BY id ASC", ARRAY_A);
    if (empty($hero_cars)) {
        $hero_cars = $wpdb->get_results("SELECT * FROM cars WHERE status != 'SOLD' ORDER BY is_recommended DESC, id ASC LIMIT 8", ARRAY_A);
    }

    // 2. Get ALL ACTIVE inventory vehicles for dynamic catalog (excluding SOLD)
    $catalog_cars = $wpdb->get_results("SELECT * FROM cars WHERE status != 'SOLD' ORDER BY id ASC", ARRAY_A);

    // 3. Dynamically fetch ALL unique brands currently in active inventory (allows staff/owner to add any brand)
    $catalog_brands = $wpdb->get_col("SELECT DISTINCT brand FROM cars WHERE status != 'SOLD' AND brand IS NOT NULL AND brand != '' ORDER BY brand ASC");

    // 4. Get all vehicles for inquiry dropdown
    $all_cars = $wpdb->get_results("SELECT id, name, stock_id, price FROM cars WHERE status != 'SOLD' ORDER BY name ASC", ARRAY_A);
}

// Fallback if DB query returned empty
if (empty($hero_cars)) {
    $hero_cars = [
        [
            'id' => 1,
            'name' => 'Ferrari SF90 Stradale Crimson Edition',
            'wheel_name' => 'UW-F01 Monoblock Forged',
            'status' => 'AVAILABLE',
            'front_fitment' => '21" x 9.5J ET28',
            'rear_fitment' => '22" x 12.5J ET38',
            'finish' => 'Candy Dark Red',
            'alloy_type' => '6061-T6 Aerospace Forged',
            'weight' => '9.8 kg / wheel',
            'image_file' => 'car-1.png'
        ],
        [
            'id' => 2,
            'name' => 'Apollo Intensa Emozione Carbon/Crimson',
            'wheel_name' => 'UW-R09 Carbon-Forged Track',
            'status' => 'BESPOKE',
            'front_fitment' => '20" x 10.0J ET22',
            'rear_fitment' => '21" x 13.0J ET30',
            'finish' => 'Dark Crimson Tint',
            'alloy_type' => 'Carbon Barrel + Forged Center',
            'weight' => '8.4 kg / wheel',
            'image_file' => 'car-2.png'
        ],
        [
            'id' => 3,
            'name' => 'Nissan GT-R Nismo Widebody Edition',
            'wheel_name' => 'UW-DD3 Deep Lip 3-Piece',
            'status' => 'RECOMMENDED',
            'front_fitment' => '20" x 11.0J ET-5',
            'rear_fitment' => '20" x 12.5J ET-15',
            'finish' => 'Anodized Dark Red Lip',
            'alloy_type' => '3-Piece Modular Forged',
            'weight' => '10.5 kg / wheel',
            'image_file' => 'car-3.png'
        ],
        [
            'id' => 5,
            'name' => 'Lamborghini Revuelto V12 Hybrid',
            'wheel_name' => 'UW-SV7 Carbon-Forged Monoblock',
            'status' => 'AVAILABLE',
            'front_fitment' => '20" x 9.5J ET25',
            'rear_fitment' => '21" x 12.5J ET35',
            'finish' => 'Candy Crimson Metallic',
            'alloy_type' => 'Aero-Forged Monoblock',
            'weight' => '9.2 kg / wheel',
            'image_file' => 'car-5.png'
        ],
        [
            'id' => 6,
            'name' => 'Chevrolet Corvette C8 Z06 Carbon Aero',
            'wheel_name' => 'UW-Z06 Concave Forged',
            'status' => 'AVAILABLE',
            'front_fitment' => '20" x 10.0J ET30',
            'rear_fitment' => '21" x 13.0J ET42',
            'finish' => 'Metallic Dark Wine Red',
            'alloy_type' => 'Deep Concave Forged Monoblock',
            'weight' => '9.6 kg / wheel',
            'image_file' => 'car-6.png'
        ],
        [
            'id' => 8,
            'name' => 'McLaren 765LT Carbon Longtail',
            'wheel_name' => 'UW-LT9 Ultra-Light Track',
            'status' => 'BESPOKE',
            'front_fitment' => '19" x 9.0J ET18',
            'rear_fitment' => '20" x 11.5J ET26',
            'finish' => 'Candy Crimson Satin',
            'alloy_type' => 'Forged Magnesium/Alloy',
            'weight' => '7.9 kg / wheel',
            'image_file' => 'car-8.png'
        ]
    ];
}

if (empty($catalog_cars)) {
    $catalog_cars = $hero_cars;
}
if (empty($catalog_brands)) {
    $catalog_brands = ['Apollo', 'BMW', 'Chevrolet', 'Ferrari', 'Lamborghini', 'McLaren', 'Nissan', 'Porsche'];
}
?>

<main>
  <section id="showcase" class="showcase-section">
    <div class="container">
      
      <!-- Section Header -->
      <div class="section-header">
        <div class="section-pill-tag">
          <span class="section-pill-dot"></span>
          Bespoke Engineering & Exotic Fleet
        </div>
        <h1 class="section-title">
          SHOWROOM <span>FLEET</span>
        </h1>
        <p class="section-subtitle">
          Spin and roll through our handpicked recommended builds.
        </p>
      </div>

      <!-- Casino 3D Turntable / Roulette Reel Showcase -->
      <div class="casino-stage-container" id="casinoStageContainer">
        
        <!-- Turntable Floor Glow & Rings -->
        <div class="casino-turntable-floor">
          <div class="casino-floor-ring ring-outer"></div>
          <div class="casino-floor-ring ring-inner"></div>
        </div>
        <div class="casino-floor-spotlight"></div>

        <!-- 3D Carousel Cylinder Track -->
        <div class="casino-cylinder-stage" id="casinoCylinder">
          <?php foreach ($hero_cars as $idx => $car): 
              $num_pad = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
              $img_url = get_template_directory_uri() . '/assets/images/cars/' . $car['image_file'];
          ?>
            <div class="casino-car-item" 
                 data-index="<?php echo $idx; ?>" 
                 data-id="<?php echo esc_attr($car['id']); ?>" 
                 data-name="<?php echo esc_attr($car['name']); ?>" 
                 data-brand="<?php echo esc_attr($car['brand'] ?? 'UniWheels'); ?>"
                 data-price="<?php echo esc_attr($car['price'] ?? 0); ?>"
                 data-stock="<?php echo esc_attr($car['stock_id'] ?? 'UW-' . $car['id']); ?>"
                 data-mileage="<?php echo esc_attr($car['mileage'] ?? 'Delivery Miles'); ?>"
                 data-transmission="<?php echo esc_attr($car['transmission'] ?? 'Dual-Clutch DCT'); ?>"
                 data-body="<?php echo esc_attr($car['body_type'] ?? 'Coupe'); ?>"
                 data-fuel="<?php echo esc_attr($car['fuel_type'] ?? 'Gasoline'); ?>"
                 data-seating="<?php echo esc_attr($car['seating'] ?? '2 Seats'); ?>"
                 data-wheel="<?php echo esc_attr($car['wheel_name']); ?>" 
                 data-status="<?php echo esc_attr($car['status']); ?>" 
                 data-front="<?php echo esc_attr($car['front_fitment']); ?>" 
                 data-rear="<?php echo esc_attr($car['rear_fitment']); ?>" 
                 data-finish="<?php echo esc_attr($car['finish']); ?>" 
                 data-alloy="<?php echo esc_attr($car['alloy_type']); ?>" 
                 data-weight="<?php echo esc_attr($car['weight']); ?>" 
                 data-image="<?php echo esc_url($img_url); ?>">
              
              <div class="casino-cutout-wrap">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($car['name']); ?>" class="casino-cutout-img" loading="eager" draggable="false">
                <div class="casino-car-glow"></div>
              </div>

              <!-- Floating Index Pill -->
              <div class="casino-floating-tag">
                <span class="casino-tag-num"><?php echo esc_html($num_pad); ?> //</span>
                <span class="casino-tag-name"><?php echo esc_html($car['name']); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Left & Right Roll Casino Controls -->
        <button type="button" class="casino-nav-btn roll-left" id="casinoRollLeft" aria-label="Roll Left">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <button type="button" class="casino-nav-btn roll-right" id="casinoRollRight" aria-label="Roll Right">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>

        <!-- Carousel Indicators / Dot Ticks -->
        <div class="casino-indicators" id="casinoIndicators"></div>

      </div>

      <?php
        $first_hero = !empty($hero_cars) ? $hero_cars[0] : null;
        $init_price = $first_hero && !empty($first_hero['price']) ? floatval($first_hero['price']) : 0;
        $init_price_str = $init_price > 0 ? ('₱' . number_format($init_price, 0)) : '₱ Bespoke';
        $init_name = $first_hero ? $first_hero['name'] : 'Luxury Vehicle';
        $init_wheel = $first_hero ? $first_hero['wheel_name'] : '';
        $init_stock = $first_hero ? ($first_hero['stock_id'] ?: ('UW-' . $first_hero['id'])) : 'UW-01';
        $init_front = $first_hero ? ($first_hero['front_fitment'] ?: '-') : '-';
        $init_rear = $first_hero ? ($first_hero['rear_fitment'] ?: '-') : '-';
        $init_finish = $first_hero ? ($first_hero['finish'] ?: '-') : '-';
        $init_alloy = $first_hero ? ($first_hero['alloy_type'] ?: '-') : '-';
        $init_weight = $first_hero ? ($first_hero['weight'] ?: '-') : '-';
        $init_status = $first_hero ? ($first_hero['status'] ?: 'AVAILABLE') : 'AVAILABLE';
      ?>
      <!-- Active Front Vehicle Cockpit HUD -->
      <div class="casino-active-hud" id="casinoActiveHud">
        <div class="hud-top-bar">
          <div class="hud-badge-group">
            <span class="hud-rec-badge">★ RECOMMENDED</span>
            <span class="hud-status-badge" id="hudStatusBadge">● <?php echo esc_html(strtoupper($init_status)); ?></span>
            <span class="hud-price-badge" id="hudPriceBadge"><?php echo esc_html($init_price_str); ?></span>
          </div>
          <div class="hud-index-tracker" id="hudIndexTracker">
            <span id="hudCurrentNum">01</span> / <span id="hudTotalNum"><?php echo str_pad(count($hero_cars), 2, '0', STR_PAD_LEFT); ?></span>
          </div>
        </div>

        <div class="hud-main-content">
          <div class="hud-title-col">
            <h2 class="hud-car-title" id="hudCarTitle"><?php echo esc_html($init_name); ?></h2>
            <div class="hud-wheel-title" id="hudWheelTitle"><?php echo esc_html($init_wheel); ?></div>
            <div class="hud-stock-sub" id="hudStockSub">STOCK #<?php echo esc_html(strtoupper($init_stock)); ?></div>
          </div>

          <div class="hud-specs-grid">
            <div class="hud-spec-item">
              <span class="hud-k">Front Fitment</span>
              <span class="hud-v" id="hudFront"><?php echo esc_html($init_front); ?></span>
            </div>
            <div class="hud-spec-item">
              <span class="hud-k">Rear Fitment</span>
              <span class="hud-v" id="hudRear"><?php echo esc_html($init_rear); ?></span>
            </div>
            <div class="hud-spec-item">
              <span class="hud-k">Wheel Finish</span>
              <span class="hud-v" id="hudFinish"><?php echo esc_html($init_finish); ?></span>
            </div>
            <div class="hud-spec-item">
              <span class="hud-k">Alloy / Construction</span>
              <span class="hud-v" id="hudAlloy"><?php echo esc_html($init_alloy); ?></span>
            </div>
            <div class="hud-spec-item">
              <span class="hud-k">Wheel Weight</span>
              <span class="hud-v" id="hudWeight"><?php echo esc_html($init_weight); ?></span>
            </div>
          </div>
        </div>

        <!-- Spacious Full-Width Cockpit Action Bar (3 Spacious Luxury Buttons) -->
        <div class="hud-actions-bar">
          <button type="button" class="btn-hud-messenger" id="hudMessengerBtn" title="Inquire via Messenger with pre-filled vehicle ID and details">
            <svg viewBox="0 0 28 28" width="18" height="18" fill="currentColor">
              <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
            </svg>
            <span>Inquire via Messenger</span>
          </button>
          
          <button type="button" class="btn-hud-finance" id="hudFinanceBtn" title="Calculate rough estimates for down payments & monthly amortizations">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
            <span>Finance Calculator</span>
          </button>

          <button type="button" class="btn-hud-inquire" id="hudInquireBtn" title="Schedule Showroom Viewing">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Showroom Consultation</span>
          </button>
        </div>
      </div>

      <!-- ========================================================
           DYNAMIC INVENTORY SEARCH AND FILTERS (FLOWCHART FEATURE #1 & #4)
           ======================================================== -->
      <div id="inventoryCatalogSection" class="catalog-section-wrapper">
        <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
          <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem;">
            <div>
              <h2 class="section-title" style="margin: 0; font-size:2.2rem;">
                VEHICLE <span>FLEET</span>
              </h2>
            </div>
            <div id="filterResultCount" class="filter-count-badge">
              Showing <?php echo count($catalog_cars); ?> Luxury Units
            </div>
          </div>
        </div>

        <!-- Dynamic Filter Control Bar (Feature #1) -->
        <div class="catalog-filter-bar">
          <!-- Live Keyword Search -->
          <div class="filter-group search-group">
            <label class="filter-label" for="catalogSearchInput">Search Model / Stock</label>
            <div class="search-input-wrap">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              <input type="text" id="catalogSearchInput" class="catalog-search-input" placeholder="Search Ferrari, GT3, UW-...">
            </div>
          </div>

          <!-- Dynamic Brand Filter (Populated from DB - allows employees/owners to add any brand) -->
          <div class="filter-group">
            <label class="filter-label" for="filterBrand">Brand / Marque</label>
            <select id="filterBrand" class="catalog-filter-select">
              <option value="all">All Brands (<?php echo count($catalog_brands); ?>)</option>
              <?php foreach ($catalog_brands as $b): ?>
                <option value="<?php echo esc_attr(strtolower($b)); ?>"><?php echo esc_html($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Budget Range Filter -->
          <div class="filter-group">
            <label class="filter-label" for="filterBudget">Budget / Price</label>
            <select id="filterBudget" class="catalog-filter-select">
              <option value="all">All Price Ranges</option>
              <option value="under-150k">Under ₱150,000</option>
              <option value="150k-300k">₱150,000 – ₱300,000</option>
              <option value="300k-600k">₱300,000 – ₱600,000</option>
              <option value="over-600k">₱600,000+ (Ultra-Exotics)</option>
            </select>
          </div>

          <!-- Transmission Filter -->
          <div class="filter-group">
            <label class="filter-label" for="filterTransmission">Transmission</label>
            <select id="filterTransmission" class="catalog-filter-select">
              <option value="all">All Transmissions</option>
              <option value="dual-clutch">Dual-Clutch (DCT)</option>
              <option value="automatic">Automatic</option>
              <option value="manual">Manual</option>
              <option value="sequential">Sequential</option>
            </select>
          </div>

          <!-- Body Type Filter -->
          <div class="filter-group">
            <label class="filter-label" for="filterBodyType">Body Configuration</label>
            <select id="filterBodyType" class="catalog-filter-select">
              <option value="all">All Body Types</option>
              <option value="coupe">Coupe</option>
              <option value="hypercar">Hypercar</option>
              <option value="track">Track Special</option>
              <option value="convertible">Convertible / Targa</option>
            </select>
          </div>

          <!-- Reset Button -->
          <div class="filter-group action-group">
            <button type="button" id="btnResetFilters" class="btn-reset-filters" title="Reset all search filters">
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
              <span>Reset</span>
            </button>
          </div>
        </div>

        <!-- Vehicle Specification Cards Grid (Feature #4) -->
        <div class="inventory-cards-grid" id="inventoryCardsGrid">
          <?php foreach ($catalog_cars as $car): 
              $c_img = get_template_directory_uri() . '/assets/images/cars/' . $car['image_file'];
              $c_price = floatval($car['price'] ?: 0);
              $c_brand = $car['brand'] ?: 'UniWheels';
              $c_stock = $car['stock_id'] ?: ('UW-' . $car['id']);
              $c_mileage = $car['mileage'] ?: 'Delivery Miles';
              $c_transmission = $car['transmission'] ?: 'Dual-Clutch DCT';
              $c_body = $car['body_type'] ?: 'Coupe';
              $c_fuel = $car['fuel_type'] ?: 'Gasoline';
              $c_seating = $car['seating'] ?: '2 Seats';
              $c_est_monthly = round(($c_price * 0.8 * (0.059/12) * pow(1 + 0.059/12, 60)) / (pow(1 + 0.059/12, 60) - 1));
          ?>
            <div class="inventory-car-card" 
                 data-id="<?php echo esc_attr($car['id']); ?>"
                 data-name="<?php echo esc_attr(strtolower($car['name'])); ?>"
                 data-brand="<?php echo esc_attr(strtolower($c_brand)); ?>"
                 data-stock="<?php echo esc_attr(strtolower($c_stock)); ?>"
                 data-price="<?php echo esc_attr($c_price); ?>"
                 data-transmission="<?php echo esc_attr(strtolower($c_transmission)); ?>"
                 data-body="<?php echo esc_attr(strtolower($c_body)); ?>"
                 data-fuel="<?php echo esc_attr($c_fuel); ?>"
                 data-mileage="<?php echo esc_attr($c_mileage); ?>"
                 data-seating="<?php echo esc_attr($c_seating); ?>">
              
              <!-- Card Top Header: Badges & Price -->
              <div class="card-top-row">
                <div class="badge-row">
                  <span class="stock-badge">#<?php echo esc_html($c_stock); ?></span>
                  <span class="status-badge <?php echo esc_attr(strtolower(str_replace(' ', '-', $car['status']))); ?>">● <?php echo esc_html($car['status']); ?></span>
                </div>
                <div class="price-box">
                  <div class="car-price-main">₱<?php echo number_format($c_price, 0); ?></div>
                  <div class="car-amortization-tag" onclick='openFinancialCalculator(<?php echo json_encode($car); ?>)' title="Click to view rough monthly amortization estimate">
                    Est. ₱<?php echo number_format($c_est_monthly, 0); ?>/mo ⓘ
                  </div>
                </div>
              </div>

              <!-- Car Cutout Image Presentation -->
              <div class="card-image-stage">
                <img src="<?php echo esc_url($c_img); ?>" alt="<?php echo esc_attr($car['name']); ?>" class="card-car-img" loading="lazy">
                <div class="card-ambient-glow"></div>
              </div>

              <!-- Brand & Name -->
              <div class="card-title-group">
                <span class="card-marque-pill"><?php echo esc_html($c_brand); ?></span>
                <h3 class="card-car-name"><?php echo esc_html($car['name']); ?></h3>
                <div class="card-wheel-sub"><?php echo esc_html($car['wheel_name']); ?></div>
              </div>

              <!-- Vehicle Specification Grid Chips (Feature #4) -->
              <div class="card-specs-grid">
                <div class="spec-chip">
                  <span class="spec-label">Mileage</span>
                  <span class="spec-value"><?php echo esc_html($c_mileage); ?></span>
                </div>
                <div class="spec-chip">
                  <span class="spec-label">Transmission</span>
                  <span class="spec-value"><?php echo esc_html($c_transmission); ?></span>
                </div>
                <div class="spec-chip">
                  <span class="spec-label">Powertrain</span>
                  <span class="spec-value"><?php echo esc_html($c_fuel); ?></span>
                </div>
                <div class="spec-chip">
                  <span class="spec-label">Seating</span>
                  <span class="spec-value"><?php echo esc_html($c_seating); ?></span>
                </div>
              </div>

              <!-- Card Action CTAs (Features #2, #5, #7) -->
              <div class="card-actions-bar">
                <!-- One-Click Automated Messenger Redirect (Feature #2 & #7) -->
                <button type="button" class="btn-card-messenger" onclick='inquireMessengerLead(<?php echo json_encode($car); ?>)' title="Inquire via Messenger with pre-filled vehicle ID and details">
                  <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor">
                    <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                  </svg>
                  <span>Inquire via Messenger</span>
                </button>

                <div class="card-actions-sub-row">
                  <!-- Financial Calculator Modal Trigger (Feature #5) -->
                  <button type="button" class="btn-card-calc" onclick='openFinancialCalculator(<?php echo json_encode($car); ?>)' title="Calculate down payments and monthly amortizations">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                    <span>Finance Calc</span>
                  </button>

                  <!-- Face-to-Face Showroom Consultation Anchor -->
                  <a href="#inquirySection" class="btn-card-inquire" onclick="selectCarForConsultation('<?php echo esc_js($car['name']); ?>')" title="Schedule Private In-Person Showroom Viewing">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span>Showroom Talk</span>
                  </a>
                </div>
              </div>

            </div>
          <?php endforeach; ?>
        </div>

        <!-- No Matching Results Message -->
        <div id="noCatalogResults" class="no-results-box" style="display:none;">
          <div style="font-size: 2.2rem; margin-bottom: 0.5rem;">🔍</div>
          <h3>No Vehicles Found</h3>
          <p>No luxury vehicles matched your current filter criteria. Try broadening your budget or selecting all marques.</p>
          <button type="button" class="btn btn-outline" onclick="resetAllCatalogFilters()">Reset All Filters</button>
        </div>

      </div>

      <!-- ========================================================
           INTERACTIVE FINANCIAL ESTIMATE CALCULATOR MODAL (FEATURE #5)
           ======================================================== -->
      <div id="financeCalcModal" class="calc-modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
        <div class="calc-modal-dialog">
          <div class="calc-modal-header">
            <div style="display:flex; align-items:center; gap:10px;">
              <div class="calc-icon-badge">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
              </div>
              <div>
                <h3 class="calc-modal-title">Financial Estimate Calculator</h3>
                <span class="calc-modal-subtitle" id="calcVehicleSubtitle">Configure down payment & monthly amortization</span>
              </div>
            </div>
            <button type="button" class="calc-modal-close" onclick="closeFinancialCalculator()">&times;</button>
          </div>

          <div class="calc-modal-body">
            <div class="calc-grid-layout">
              
              <!-- Left: Inputs -->
              <div class="calc-inputs-pane">
                <div class="calc-field-group">
                  <label class="calc-label" for="calcVehiclePrice">Vehicle Acquisition Price (₱ PHP)</label>
                  <div class="calc-input-wrap">
                    <span class="calc-currency-prefix">₱</span>
                    <input type="number" id="calcVehiclePrice" class="calc-num-input" value="550000" step="5000" oninput="runFinancialCalculation()">
                  </div>
                </div>

                <!-- Down Payment Slider -->
                <div class="calc-field-group">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <label class="calc-label" style="margin:0;">Down Payment</label>
                    <div class="calc-dp-stats">
                      <span id="calcDpPercentDisplay" style="color:var(--wheels-neon); font-family:var(--font-mono); font-weight:800;">20%</span>
                      <span style="color:var(--text-muted); margin:0 4px;">•</span>
                      <span id="calcDpAmountDisplay" style="color:#fff; font-family:var(--font-mono); font-weight:700;">₱110,000</span>
                    </div>
                  </div>
                  <input type="range" id="calcDpSlider" class="calc-slider" min="10" max="70" step="5" value="20" oninput="handleDownPaymentSlider(this.value)">
                  <div style="display:flex; justify-content:space-between; font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono); margin-top:3px;">
                    <span>10% (Min)</span>
                    <span>20% (Default)</span>
                    <span>50%</span>
                    <span>70% (Max)</span>
                  </div>
                </div>

                <!-- Loan Term Selector (Months) -->
                <div class="calc-field-group">
                  <label class="calc-label">Amortization Term (Months)</label>
                  <div class="calc-term-pills" id="calcTermPills">
                    <button type="button" class="term-pill" data-months="24" onclick="selectCalcTerm(24)">24 Mos</button>
                    <button type="button" class="term-pill" data-months="36" onclick="selectCalcTerm(36)">36 Mos</button>
                    <button type="button" class="term-pill" data-months="48" onclick="selectCalcTerm(48)">48 Mos</button>
                    <button type="button" class="term-pill active" data-months="60" onclick="selectCalcTerm(60)">60 Mos</button>
                    <button type="button" class="term-pill" data-months="72" onclick="selectCalcTerm(72)">72 Mos</button>
                  </div>
                </div>

                <!-- Interest Rate (APR) -->
                <div class="calc-field-group">
                  <label class="calc-label" for="calcAprRate">Estimated Annual Interest Rate (APR %)</label>
                  <div class="calc-input-wrap">
                    <input type="number" id="calcAprRate" class="calc-num-input" value="5.9" step="0.1" min="0" max="25" oninput="runFinancialCalculation()">
                    <span class="calc-percent-suffix">%</span>
                  </div>
                </div>
              </div>

              <!-- Right: Calculated Telemetry & CTAs -->
              <div class="calc-results-pane">
                <div class="calc-hero-gauge">
                  <div class="gauge-label">ESTIMATED MONTHLY AMORTIZATION</div>
                  <div class="gauge-amount" id="gaugeMonthlyDisplay">₱8,486</div>
                  <div class="gauge-terms" id="gaugeTermsSub">Per month for 60 months @ 5.9% APR</div>
                </div>

                <div class="calc-breakdown-table">
                  <div class="breakdown-row">
                    <span>Down Payment Required:</span>
                    <strong id="gaugeDownPayment">₱110,000</strong>
                  </div>
                  <div class="breakdown-row">
                    <span>Principal Financed:</span>
                    <strong id="gaugeFinancedPrincipal">₱440,000</strong>
                  </div>
                  <div class="breakdown-row">
                    <span>Total Loan Interest:</span>
                    <strong id="gaugeTotalInterest">₱69,160</strong>
                  </div>
                  <div class="breakdown-row total-row">
                    <span>Total Amortized Cost:</span>
                    <strong id="gaugeTotalCost">₱609,160</strong>
                  </div>
                </div>

                <!-- Action CTAs: Messenger + Showroom -->
                <div class="calc-cta-stack">
                  <button type="button" class="btn-calc-messenger" onclick="inquireMessengerWithFinancing()" title="Open Messenger with this calculated financing plan">
                    <svg viewBox="0 0 28 28" width="18" height="18" fill="currentColor">
                      <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                    </svg>
                    <span>Inquire via Messenger with this Plan</span>
                  </button>

                  <button type="button" class="btn-calc-showroom" onclick="applyFinancingToShowroomForm()">
                    <span>Schedule On-Site Viewing with this Plan &rarr;</span>
                  </button>
                </div>
                
                <div style="font-size:0.68rem; color:var(--text-muted); text-align:center; margin-top:8px;">
                  * Rough estimates based on standard tier-1 credit. Final rate & term finalized on-site at dealership lot.
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      <!-- Interactive Inquiry Section ("Inquire us" Form) -->
      <div id="inquirySection" class="inquiry-wrapper">
        <div class="inquiry-grid">
          
          <!-- Left Info Panel -->
          <div class="inquiry-info-pane">
            <div class="section-pill-tag" style="margin-bottom: 1rem;">
              <span class="section-pill-dot"></span>
              VIP In-Person Consultation
            </div>
            <h3>DIRECT DEALERSHIP <span>SHOWROOM TALK</span></h3>
            <p>
              Skip impersonal online forms and complex technical guesswork. We believe luxury vehicle acquisitions are best experienced face-to-face. Schedule a dedicated private showroom appointment with our senior acquisition directors.
            </p>

            <div class="benefits-list">
              <div class="benefit-item">
                <div class="benefit-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="benefit-text">
                  <h4>Dedicated Private Showroom Host</h4>
                  <p>Enjoy private 1-on-1 showroom access, key-in-hand vehicle walkthroughs, cold starts, and immediate answering of every detail.</p>
                </div>
              </div>

              <div class="benefit-item">
                <div class="benefit-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                </div>
                <div class="benefit-text">
                  <h4>On-The-Spot Trade & Valuation</h4>
                  <p>Bring your current vehicle for instant physical inspection and guaranteed appraisal while we review your acquisition terms.</p>
                </div>
              </div>

              <div class="benefit-item">
                <div class="benefit-icon">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
                <div class="benefit-text">
                  <h4>Door-to-Door Enclosed Transport</h4>
                  <p>White-glove enclosed transport shipped door-to-door with complete logistics support and insurance coverage worldwide.</p>
                </div>
              </div>
            </div>

            <!-- Brand Logo Box -->
            <div style="display: flex; align-items: center; gap: 15px; padding: 14px 20px; background: rgba(0,0,0,0.5); border-radius: 12px; border: 1px solid var(--border-subtle);">
              <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo.png' ); ?>" alt="UniWheels Emblem" style="height: 58px; width: auto;">
              <div>
                <div style="font-family: var(--font-heading); font-weight: 800; font-size: 1.1rem; color: #fff;">UNI<span style="color:var(--wheels-crimson);">WHEELS</span> MOTORS</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Premier Exotic & High-Performance Dealership</div>
              </div>
            </div>
          </div>

          <!-- Right Interactive Form -->
          <div class="inquiry-form-card">
            <div style="margin-bottom: 1.25rem;">
              <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 0.35rem;">
                <span style="font-family:var(--font-heading); font-size:0.75rem; font-weight:800; color:var(--wheels-neon); letter-spacing:0.12em; text-transform:uppercase;">
                  ● Direct Showroom Inquiry
                </span>
                <span style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);">
                  Face-To-Face Consultation
                </span>
              </div>
              <h4 style="font-family:var(--font-heading); font-size:1.25rem; font-weight:800; color:#fff; margin:0 0 4px 0;">
                Schedule A Private Showroom Meeting
              </h4>
              <p style="font-size:0.8rem; color:var(--text-sub); line-height:1.4; margin:0;">
                No guesswork needed. Connect directly with our dealership concierge to arrange an in-person viewing, test drive, or custom consultation.
              </p>
            </div>

            <!-- Consultation Type Selector -->
            <div class="form-group" style="margin-bottom:1rem;">
              <label class="form-label">Consultation Type</label>
              <div class="pill-selector" id="inquiryTypeSelector">
                <span class="pill-option active" data-value="Showroom Visit (Face-to-Face)">Showroom Visit (Face-to-Face)</span>
                <span class="pill-option" data-value="Private VIP Viewing & Test Drive">Private Viewing & Test Drive</span>
                <span class="pill-option" data-value="Vehicle Acquisition & Trade-In">Vehicle Acquisition & Trade-In</span>
                <span class="pill-option" data-value="Custom Fleet Sourcing">Custom Fleet Sourcing</span>
              </div>
              <input type="hidden" name="inquiry_type" id="inquiryTypeInput" value="Showroom Visit (Face-to-Face)">
            </div>

            <form id="inquiryForm" method="POST" action="<?php echo esc_url( home_url('/submit-inquiry.php') ); ?>">
              <div class="form-group">
                <label class="form-label" for="selectedCarInput">Target Vehicle / Fleet Focus</label>
                <?php if (!empty($all_cars)): ?>
                  <select name="selected_car" id="selectedCarInput" class="form-control" style="background:#14141f; color:#fff;" required>
                    <?php foreach ($all_cars as $ac): ?>
                      <option value="<?php echo esc_attr($ac['name']); ?>"><?php echo esc_html($ac['name']); ?></option>
                    <?php endforeach; ?>
                    <option value="General Showroom Fleet (Multiple Cars)">General Showroom Fleet (Multiple Cars)</option>
                    <option value="Custom Vehicle Sourcing Request">Custom Vehicle Sourcing Request</option>
                  </select>
                <?php else: ?>
                  <input type="text" name="selected_car" id="selectedCarInput" class="form-control" placeholder="Select one of the cars above or enter custom vehicle" value="Ferrari SF90 Stradale Crimson Edition" required>
                <?php endif; ?>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="clientName">Full Name *</label>
                  <input type="text" name="client_name" id="clientName" class="form-control" placeholder="e.g. Julian Thorne" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="clientPhone">Phone / WhatsApp Number *</label>
                  <input type="tel" name="client_phone" id="clientPhone" class="form-control" placeholder="e.g. +1 (555) 234-5678" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label" for="clientEmail">Email Address *</label>
                  <input type="email" name="client_email" id="clientEmail" class="form-control" placeholder="e.g. julian@domain.com" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="preferredDate">Preferred Date / Timing</label>
                  <input type="text" name="preferred_date" id="preferredDate" class="form-control" placeholder="e.g. Tomorrow at 2:00 PM, Saturday morning...">
                </div>
              </div>

              <!-- Quick Time Window Suggestions -->
              <div class="form-group" style="margin-top:-0.5rem; margin-bottom:1.15rem;">
                <div style="font-size:0.7rem; color:var(--text-muted); margin-bottom:5px; text-transform:uppercase; letter-spacing:0.05em;">Quick Suggestions:</div>
                <div class="pill-selector" id="quickDatePills" style="gap:6px;">
                  <button type="button" class="pill-option" data-date="Today (Immediate Walk-in)">Today (Immediate)</button>
                  <button type="button" class="pill-option" data-date="Tomorrow Afternoon (2:00 PM)">Tomorrow Afternoon</button>
                  <button type="button" class="pill-option" data-date="This Saturday Morning (11:00 AM)">This Saturday Morning</button>
                  <button type="button" class="pill-option" data-date="Next Week Flexible Window">Next Week Flexible</button>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label" for="clientMessage">Inquiry Details & In-Person Meeting Notes</label>
                <textarea name="client_message" id="clientMessage" class="form-control" rows="3" placeholder="Tell us what you would like to discuss (e.g., trade-in inspection, cold start & private test drive, wheel customization, or acquisition terms)..."></textarea>
              </div>

              <button type="submit" class="btn-submit-inquiry" id="submitInquiryBtn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Request Face-To-Face Consultation</span>
              </button>

              <div id="inquiryStatusFeedback" style="display:none; margin-top:1rem; padding:1.25rem; border-radius:10px; font-size:0.85rem; line-height:1.5;"></div>
            </form>
          </div>

        </div>
      </div>

    </div>
  </section>
</main>

<?php
get_footer();
