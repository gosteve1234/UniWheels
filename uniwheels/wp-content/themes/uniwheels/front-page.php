<?php
/**
 * UniWheels Front Page Template
 *
 * @package UniWheels
 */

get_header();

global $wpdb;
$table_exists = $wpdb->get_var("SHOW TABLES LIKE 'cars'");

// 1. Fixed 3D Showcase (For show only, not sellable, fixed pristine assets, displaying car brands)
$showcase_brands = [
    [
        'brand' => 'Ferrari',
        'wheel_name' => 'UW-F01 Monoblock Forged',
        'front_fitment' => '21" x 9.5J ET28',
        'rear_fitment' => '22" x 12.5J ET38',
        'finish' => 'Candy Dark Red',
        'alloy_type' => '6061-T6 Aerospace Forged',
        'weight' => '9.8 kg / wheel',
        'image_file' => 'car-1.png'
    ],
    [
        'brand' => 'Apollo',
        'wheel_name' => 'UW-R09 Carbon-Forged Track',
        'front_fitment' => '20" x 10.0J ET22',
        'rear_fitment' => '21" x 13.0J ET30',
        'finish' => 'Dark Crimson Tint',
        'alloy_type' => 'Carbon Barrel + Forged Center',
        'weight' => '8.4 kg / wheel',
        'image_file' => 'car-2.png'
    ],
    [
        'brand' => 'Nissan',
        'wheel_name' => 'UW-DD3 Deep Lip 3-Piece',
        'front_fitment' => '20" x 11.0J ET-5',
        'rear_fitment' => '20" x 12.5J ET-15',
        'finish' => 'Anodized Dark Red Lip',
        'alloy_type' => '3-Piece Modular Forged',
        'weight' => '10.5 kg / wheel',
        'image_file' => 'car-3.png'
    ],
    [
        'brand' => 'Porsche',
        'wheel_name' => 'UW-RS01 Centerlock Forged',
        'front_fitment' => '20" x 9.5J Centerlock',
        'rear_fitment' => '21" x 12.5J Centerlock',
        'finish' => 'Brushed Dark Red',
        'alloy_type' => '6061-T6 Monoblock Forged',
        'weight' => '8.9 kg / wheel',
        'image_file' => 'car-4.png'
    ],
    [
        'brand' => 'Lamborghini',
        'wheel_name' => 'UW-SV7 Carbon-Forged Monoblock',
        'front_fitment' => '20" x 9.5J ET25',
        'rear_fitment' => '21" x 12.5J ET35',
        'finish' => 'Candy Crimson Metallic',
        'alloy_type' => 'Aero-Forged Monoblock',
        'weight' => '9.2 kg / wheel',
        'image_file' => 'car-5.png'
    ],
    [
        'brand' => 'Chevrolet',
        'wheel_name' => 'UW-Z06 Concave Forged',
        'front_fitment' => '20" x 10.0J ET30',
        'rear_fitment' => '21" x 13.0J ET42',
        'finish' => 'Metallic Dark Wine Red',
        'alloy_type' => 'Deep Concave Forged Monoblock',
        'weight' => '9.6 kg / wheel',
        'image_file' => 'car-6.png'
    ],
    [
        'brand' => 'BMW',
        'wheel_name' => 'UW-M4 Deep Dish Modular',
        'front_fitment' => '20" x 10.5J ET12',
        'rear_fitment' => '20" x 11.5J ET08',
        'finish' => 'Gloss Red Chrome Lip',
        'alloy_type' => '2-Piece Welded Modular',
        'weight' => '10.2 kg / wheel',
        'image_file' => 'car-7.png'
    ],
    [
        'brand' => 'McLaren',
        'wheel_name' => 'UW-LT9 Ultra-Light Track',
        'front_fitment' => '19" x 9.0J ET18',
        'rear_fitment' => '20" x 11.5J ET26',
        'finish' => 'Candy Crimson Satin',
        'alloy_type' => 'Forged Magnesium/Alloy',
        'weight' => '7.9 kg / wheel',
        'image_file' => 'car-8.png'
    ]
];

// 2. Dynamic Vehicle Fleet Inventory
// AVAILABLE first (with NEW ARRIVALS <= 7 days at top), RESERVED at bottom, excluding SOLD
$catalog_cars = [];
$catalog_brands = [];

if ($table_exists) {
    $catalog_cars = $wpdb->get_results("
        SELECT * FROM cars 
        WHERE status != 'SOLD' 
        ORDER BY 
            CASE WHEN status = 'AVAILABLE' THEN 0 WHEN status = 'RESERVED' THEN 1 ELSE 2 END,
            (created_at >= NOW() - INTERVAL 7 DAY) DESC,
            created_at DESC,
            id DESC
    ", ARRAY_A);

    $catalog_brands = $wpdb->get_col("SELECT DISTINCT brand FROM cars WHERE status != 'SOLD' AND brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
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
          Interactive marquee showcase of our handpicked builds and bespoke forged wheel engineering.
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

        <!-- 3D Carousel Cylinder Track (Fixed showcase, brand names displayed) -->
        <div class="casino-cylinder-stage" id="casinoCylinder">
          <?php foreach ($showcase_brands as $idx => $car): 
              $num_pad = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
              $img_url = get_template_directory_uri() . '/assets/images/cars/' . $car['image_file'];
          ?>
            <div class="casino-car-item" 
                 data-index="<?php echo $idx; ?>" 
                 data-brand="<?php echo esc_attr($car['brand']); ?>"
                 data-wheel="<?php echo esc_attr($car['wheel_name']); ?>" 
                 data-front="<?php echo esc_attr($car['front_fitment']); ?>" 
                 data-rear="<?php echo esc_attr($car['rear_fitment']); ?>" 
                 data-finish="<?php echo esc_attr($car['finish']); ?>" 
                 data-alloy="<?php echo esc_attr($car['alloy_type']); ?>" 
                 data-weight="<?php echo esc_attr($car['weight']); ?>" 
                 data-image="<?php echo esc_url($img_url); ?>">
              
              <div class="casino-cutout-wrap">
                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($car['brand']); ?>" class="casino-cutout-img" loading="eager" draggable="false">
                <div class="casino-car-glow"></div>
              </div>

              <!-- Floating Index Pill (Displays Brand name as per Requirement e) -->
              <div class="casino-floating-tag">
                <span class="casino-tag-num"><?php echo esc_html($num_pad); ?> //</span>
                <span class="casino-tag-name"><?php echo esc_html($car['brand']); ?></span>
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

        <!-- Vehicle Specification Cards Grid (Features #4, #1, #f, #h, #j, #l) -->
        <div class="inventory-cards-grid" id="inventoryCardsGrid">
          <?php foreach ($catalog_cars as $car): 
              $c_img = get_template_directory_uri() . '/assets/images/cars/' . $car['image_file'];
              $c_price = floatval($car['price'] ?: 0);
              $is_negotiable = ($c_price <= 0);
              $c_brand = $car['brand'] ?: 'UniWheels';
              $c_stock = $car['stock_id'] ?: ('UW-' . $car['id']);
              $c_mileage = $car['mileage'] ?: 'Delivery Miles';
              $c_transmission = $car['transmission'] ?: 'Dual-Clutch DCT';
              $c_body = $car['body_type'] ?: 'Coupe';
              $c_fuel = $car['fuel_type'] ?: 'Gasoline';
              $c_seating = $car['seating'] ?: '2 Seats';
              $c_est_monthly = $is_negotiable ? 0 : round(($c_price * 0.8 * (0.059/12) * pow(1 + 0.059/12, 60)) / (pow(1 + 0.059/12, 60) - 1));
              
              // Automatic time-based NEW ARRIVAL tag: disappears after 7 days (Requirement f)
              $is_new_arrival = (strtotime($car['created_at']) >= strtotime('-7 days'));
              $is_reserved = (strtoupper($car['status']) === 'RESERVED');
          ?>
            <div class="inventory-car-card <?php echo $is_reserved ? 'is-reserved' : ''; ?>" 
                 data-id="<?php echo esc_attr($car['id']); ?>"
                 data-name="<?php echo esc_attr(strtolower($car['name'])); ?>"
                 data-brand="<?php echo esc_attr(strtolower($c_brand)); ?>"
                 data-stock="<?php echo esc_attr(strtolower($c_stock)); ?>"
                 data-price="<?php echo esc_attr($c_price); ?>"
                 data-status="<?php echo esc_attr(strtolower($car['status'])); ?>"
                 data-transmission="<?php echo esc_attr(strtolower($c_transmission)); ?>"
                 data-body="<?php echo esc_attr(strtolower($c_body)); ?>"
                 data-fuel="<?php echo esc_attr($c_fuel); ?>"
                 data-mileage="<?php echo esc_attr($c_mileage); ?>"
                 data-seating="<?php echo esc_attr($c_seating); ?>">
              
              <!-- Card Top Header: Badges & Price -->
              <div class="card-top-row">
                <div class="badge-row" style="display:flex; align-items:center; flex-wrap:wrap; gap:5px;">
                  <span class="stock-badge">#<?php echo esc_html($c_stock); ?></span>
                  <?php if ($is_new_arrival): ?>
                    <span class="badge-new-arrival" title="New Arrival (Posted within 7 days)">✦ NEW ARRIVAL</span>
                  <?php endif; ?>
                  <span class="status-badge <?php echo esc_attr(strtolower($car['status'])); ?>">● <?php echo esc_html($car['status']); ?></span>
                </div>
                <div class="price-box">
                  <?php if (!$is_negotiable): ?>
                    <div class="car-price-main">₱<?php echo number_format($c_price, 0); ?></div>
                    <div class="car-amortization-tag" onclick='openFinancialCalculator(<?php echo json_encode($car); ?>)' title="Click to view rough monthly amortization estimate">
                      Est. ₱<?php echo number_format($c_est_monthly, 0); ?>/mo ⓘ
                    </div>
                  <?php else: ?>
                    <div class="car-price-main negotiable" style="font-size:1.15rem; color:var(--wheels-neon); font-weight:800; letter-spacing:0.04em;">NEGOTIABLE</div>
                    <div class="car-amortization-tag" onclick='openFinancialCalculator(<?php echo json_encode($car); ?>)' title="Click to calculate proposed custom offer">
                      Open for Offer ⓘ
                    </div>
                  <?php endif; ?>
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

              <!-- Vehicle Specification Grid Chips -->
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

              <!-- Card Action CTAs (Inquire via Messenger only; disabled if RESERVED) -->
              <div class="card-actions-bar">
                <?php if (!$is_reserved): ?>
                  <!-- Active Inquire via Messenger (Pre-fills without auto-sending) -->
                  <button type="button" class="btn-card-messenger" onclick='inquireMessengerLead(<?php echo json_encode($car); ?>)' title="Inquire via Messenger with pre-filled vehicle details">
                    <svg viewBox="0 0 28 28" width="16" height="16" fill="currentColor">
                      <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                    </svg>
                    <span>Inquire via Messenger</span>
                  </button>

                  <div class="card-actions-sub-row">
                    <button type="button" class="btn-card-calc" onclick='openFinancialCalculator(<?php echo json_encode($car); ?>)' title="Calculate down payments and monthly amortizations" style="width:100%;">
                      <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16" y2="18"></line><path d="M16 10h.01"></path><path d="M12 10h.01"></path><path d="M8 10h.01"></path><path d="M12 14h.01"></path><path d="M8 14h.01"></path><path d="M12 18h.01"></path><path d="M8 18h.01"></path></svg>
                      <span>Finance Calculator</span>
                    </button>
                  </div>
                <?php else: ?>
                  <!-- Disabled Actions when RESERVED (Requirement j) -->
                  <button type="button" class="btn-card-messenger disabled" disabled title="This vehicle is currently reserved.">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Vehicle Reserved</span>
                  </button>

                  <div class="card-actions-sub-row">
                    <button type="button" class="btn-card-calc disabled" disabled style="width:100%;">
                      <span>Reserved by Client</span>
                    </button>
                  </div>
                <?php endif; ?>
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

                <!-- Action CTA: Messenger Only -->
                <div class="calc-cta-stack">
                  <button type="button" class="btn-calc-messenger" onclick="inquireMessengerWithFinancing()" title="Open Messenger with this calculated financing plan">
                    <svg viewBox="0 0 28 28" width="18" height="18" fill="currentColor">
                      <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                    </svg>
                    <span>Inquire via Messenger with this Plan</span>
                  </button>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<?php
get_footer();
