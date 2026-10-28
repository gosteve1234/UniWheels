<?php
/**
 * UniWheels Motors - Certified Pre-Owned Automotive & Trade-In Specialists
 * Public Showroom & Inventory Portal
 */

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';

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

$hero_cars = [];
$catalog_cars = [];
$catalog_brands = [];
$all_cars = [];

if ($pdo) {
    try {
        $table_check = $pdo->query("SHOW TABLES LIKE 'cars'")->fetch();
        if ($table_check) {
            // 1. Get featured/recommended cars for hero spotlight showcase
            $hero_cars = $pdo->query("SELECT * FROM cars ORDER BY (status = 'SOLD') ASC, is_featured DESC, is_recommended DESC, id ASC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

            // 2. Get inventory vehicles for catalog (sorted so AVAILABLE/RESERVED are displayed first, then SOLD)
            $catalog_cars = $pdo->query("SELECT * FROM cars ORDER BY (status = 'SOLD') ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

            // 3. Dynamically fetch unique brands
            $catalog_brands = $pdo->query("SELECT DISTINCT brand FROM cars WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);

            // 4. Vehicles for inquiry dropdown
            $all_cars = $pdo->query("SELECT id, name, stock_id, price FROM cars WHERE status != 'SOLD' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // Fallback array handles offline DB
    }
}

// Helper: Normalize multiple photos for slideshow features
if (!function_exists('uniwheels_prepare_car_images')) {
    function uniwheels_prepare_car_images($car_list) {
        foreach ($car_list as &$car) {
            $imgs = [];
            if (!empty($car['image_file'])) {
                $imgs[] = $car['image_file'];
            }
            if (!empty($car['gallery_images'])) {
                $extra = is_string($car['gallery_images']) ? json_decode($car['gallery_images'], true) : $car['gallery_images'];
                if (is_array($extra)) {
                    foreach ($extra as $gi) {
                        if (!empty($gi) && !in_array($gi, $imgs)) {
                            $imgs[] = $gi;
                        }
                    }
                }
            }
            if (empty($imgs)) {
                $imgs[] = 'car-1.jpg';
            }
            $car['all_images'] = $imgs;
        }
        return $car_list;
    }
}

$catalog_cars = uniwheels_prepare_car_images($catalog_cars);
$hero_cars = uniwheels_prepare_car_images($hero_cars);

// Fallback if database empty or offline
if (empty($catalog_cars)) {
    $catalog_cars = [
        [
            'id' => 1,
            'name' => '2021 Toyota Vios 1.3 XLE',
            'brand' => 'Toyota',
            'price' => 545000,
            'mileage' => '28,400 km',
            'transmission' => 'Automatic',
            'body_type' => 'Sedan',
            'fuel_type' => 'Gasoline',
            'seating' => '5 Seats',
            'status' => 'AVAILABLE',
            'stock_id' => 'UW-TOY01',
            'image_file' => 'car-1.jpg',
            'all_images' => ['car-1.jpg', 'car-1-rear.jpg', 'car-1-interior.jpg'],
            'finish' => 'Silver Metallic',
            'alloy_type' => 'Original Factory Paint & Condition',
            'weight' => '3-Month Warranty Included',
            'front_fitment' => '185/60 R15 Tires',
            'rear_fitment' => '1st Owner / Complete OR/CR',
            'wheel_name' => 'OEM 15" Alloy Wheels (85% Tires)'
        ],
        [
            'id' => 2,
            'name' => '2020 Honda Civic 1.8 E CVT',
            'brand' => 'Honda',
            'price' => 785000,
            'mileage' => '34,200 km',
            'transmission' => 'Automatic',
            'body_type' => 'Sedan',
            'fuel_type' => 'Gasoline',
            'seating' => '5 Seats',
            'status' => 'AVAILABLE',
            'stock_id' => 'UW-HND02',
            'image_file' => 'car-2.jpg',
            'all_images' => ['car-2.jpg', 'car-2-rear.jpg', 'car-2-interior.jpg'],
            'finish' => 'Taffeta White',
            'alloy_type' => 'Clean Papers / LTO Verified',
            'weight' => 'Free Change Oil & Detailing',
            'front_fitment' => '215/55 R16 Bridgestone',
            'rear_fitment' => 'Casa Maintained / Fresh In & Out',
            'wheel_name' => '16" Factory Alloy Wheels'
        ],
        [
            'id' => 3,
            'name' => '2019 Toyota Fortuner 2.4 G 4x2',
            'brand' => 'Toyota',
            'price' => 1050000,
            'mileage' => '52,800 km',
            'transmission' => 'Automatic',
            'body_type' => 'SUV',
            'fuel_type' => 'Diesel',
            'seating' => '7 Seats',
            'status' => 'AVAILABLE',
            'stock_id' => 'UW-FTN03',
            'image_file' => 'car-3.jpg',
            'all_images' => ['car-3.jpg'],
            'finish' => 'Pearl White',
            'alloy_type' => 'Inspected & Certified / Trade-In Ready',
            'weight' => 'Comprehensive History Checked',
            'front_fitment' => '265/65 R17 All-Terrain',
            'rear_fitment' => 'Diesel Turbo / High Fuel Efficiency',
            'wheel_name' => '17" Rugged Factory Alloys'
        ]
    ];
    $catalog_brands = ['Honda', 'Toyota'];
    $hero_cars = $catalog_cars;
}
if (empty($hero_cars)) {
    $hero_cars = $catalog_cars;
}

include __DIR__ . '/header.php';
?>

<main id="mainContent">

  <!-- ========================================================
       1. HERO FEATURED SHOWCASE (Simple, Accessible, High HCI)
       ======================================================== -->
  <section id="showcase" class="hero-showcase-section">
    <div class="container">
      
      <!-- Welcoming Dealership Tagline -->
      <div class="hero-header-box">
        <h1 class="hero-main-title">
          Quality Secondhand Cars <span class="highlight-text">You Can Trust</span>
        </h1>
        <p class="hero-lead-text">
          Inspected, clean, and complete with original LTO papers. Browse our inventory below or trade in your current car today.
        </p>
      </div>

      <!-- Featured Vehicle Stage Card -->
      <div class="showcase-card-stage" id="showcaseStage">
        <?php 
          $first_car = !empty($hero_cars) ? $hero_cars[0] : $catalog_cars[0];
          $f_price = floatval($first_car['price'] ?? 0);
          $f_price_str = $f_price > 0 ? ('₱' . number_format($f_price, 0)) : 'Inquire for Price';
          $f_img = !empty($first_car['image_file']) ? $first_car['image_file'] : 'car-1.jpg';
          $f_img_url = 'assets/images/cars/' . $f_img;
          $f_status = strtoupper(!empty($first_car['status']) ? $first_car['status'] : 'AVAILABLE');
          $f_status_class = strtolower($f_status);
        ?>

        <div class="showcase-grid">
          <!-- Left: Big Vehicle Photo with Clean Controls & Stock ID at Top-Right -->
          <div class="showcase-image-col">
            <div class="showcase-image-wrap">
              <img id="showcaseImg" src="<?php echo esc_url($f_img_url); ?>" alt="<?php echo esc_attr($first_car['name']); ?>" class="showcase-main-photo" onerror="this.src='assets/images/cars/car-1.jpg'">
              <div class="showcase-badge-overlay">
                <span class="card-status-badge badge-<?php echo esc_attr($f_status_class); ?>" id="showcaseStatusTag"><?php echo esc_html($f_status); ?></span>
              </div>
              <!-- Stock ID in the top right corner -->
              <span class="card-stock-tag" id="showcaseStockId">#<?php echo esc_html(strtoupper($first_car['stock_id'] ?: 'UW-01')); ?></span>

              <!-- Automatic Slideshow Indicator Dots / Counter -->
              <div class="showcase-photo-dots" id="showcasePhotoDots"></div>
            </div>

            <!-- Friendly Next/Prev Navigation Buttons -->
            <div class="showcase-nav-row">
              <button type="button" class="btn-showcase-nav" id="showcasePrevBtn" aria-label="Previous Featured Car">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
                <span>Previous Car</span>
              </button>
              <div class="showcase-counter" id="showcaseCounter">
                Car <strong id="currentCarIdx">1</strong> of <?php echo count($hero_cars); ?>
              </div>
              <button type="button" class="btn-showcase-nav" id="showcaseNextBtn" aria-label="Next Featured Car">
                <span>Next Car</span>
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
              </button>
            </div>
          </div>

          <!-- Right: Clear Specs & Actions -->
          <div class="showcase-details-col">
            <h2 class="showcase-car-name" id="showcaseCarName"><?php echo esc_html($first_car['name']); ?></h2>
            
            <div class="showcase-price-box">
              <div class="price-label">Cash / Selling Price:</div>
              <div class="price-value" id="showcasePriceValue"><?php echo esc_html($f_price_str); ?></div>
            </div>

            <!-- Everyday Driver Specifications Grid -->
            <div class="showcase-specs-list" id="showcaseSpecsList">
              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Transmission</span>
                  <strong class="spec-v" id="specTrans"><?php echo esc_html($first_car['transmission'] ?? 'Automatic'); ?></strong>
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
                  <span class="spec-k">Fuel Type</span>
                  <strong class="spec-v" id="specFuel"><?php echo esc_html($first_car['fuel_type'] ?? 'Gasoline'); ?></strong>
                </div>
              </div>

              <div class="spec-card">
                <div class="spec-meta">
                  <span class="spec-k">Seating</span>
                  <strong class="spec-v" id="specSeats"><?php echo esc_html($first_car['seating'] ?? '5 Seats'); ?></strong>
                </div>
              </div>
            </div>

            <!-- Action Buttons: View Car Details & Inquire via Messenger -->
            <div class="showcase-cta-group">
              <button type="button" class="btn-showcase-details" id="showcaseDetailsBtn" title="View full specifications and photo gallery">
                <span>View Car Details</span>
              </button>

              <button type="button" class="btn-primary-messenger" id="showcaseMessengerBtn" title="Inquire about this car on Facebook Messenger">
                <svg viewBox="0 0 28 28" width="22" height="22" fill="currentColor">
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
       2. CERTIFIED PRE-OWNED INVENTORY CATALOG
       ======================================================== -->
  <section id="inventoryCatalogSection" class="catalog-section">
    <div class="container">
      
      <div class="catalog-header-row">
        <div>
          <span class="section-tag-pill">Showroom Floor</span>
          <h2 class="section-title-large">Available Secondhand Cars</h2>
          <p class="section-desc-text">Every car is physically available in our showroom, sanitized, and ready for immediate release.</p>
        </div>
      </div>

      <!-- Friendly Filter Bar -->
      <div class="catalog-filters-card">
        <div class="filters-grid">
          
          <!-- Search Bar -->
          <div class="filter-field search-field">
            <label class="filter-label" for="catalogSearchInput">Search by Car Name or Model</label>
            <div class="input-with-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              <input type="text" id="catalogSearchInput" class="filter-input" placeholder="e.g. Vios, Civic, Fortuner, Automatic...">
            </div>
          </div>

          <!-- Brand / Make -->
          <div class="filter-field">
            <label class="filter-label" for="filterBrand">Car Brand / Make</label>
            <select id="filterBrand" class="filter-select">
              <option value="all">All Brands (<?php echo count($catalog_brands); ?>)</option>
              <?php foreach ($catalog_brands as $b): ?>
                <option value="<?php echo esc_attr(strtolower($b)); ?>"><?php echo esc_html($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Budget Range -->
          <div class="filter-field">
            <label class="filter-label" for="filterBudget">Budget / Price Range</label>
            <select id="filterBudget" class="filter-select">
              <option value="all">All Price Ranges</option>
              <option value="under-500k">Under ₱500,000</option>
              <option value="500k-800k">₱500,000 – ₱800,000</option>
              <option value="800k-1m">₱800,000 – ₱1,000,000</option>
              <option value="over-1m">Above ₱1,000,000</option>
            </select>
          </div>

          <!-- Body Configuration -->
          <div class="filter-field">
            <label class="filter-label" for="filterBodyType">Body Type</label>
            <select id="filterBodyType" class="filter-select">
              <option value="all">All Body Types</option>
              <option value="sedan">Sedan</option>
              <option value="suv">SUV</option>
              <option value="mpv">7-Seater MPV / Van</option>
              <option value="pickup">Pickup Truck</option>
              <option value="hatchback">Hatchback</option>
            </select>
          </div>

          <!-- Transmission -->
          <div class="filter-field">
            <label class="filter-label" for="filterTransmission">Transmission</label>
            <select id="filterTransmission" class="filter-select">
              <option value="all">All Transmissions</option>
              <option value="automatic">Automatic</option>
              <option value="manual">Manual</option>
            </select>
          </div>

        </div>
      </div>

      <!-- Inventory Grid of Cars -->
      <div id="catalogGrid" class="inventory-cards-grid">
        <?php foreach ($catalog_cars as $car): 
          $c_price = floatval($car['price'] ?: 0);
          $c_price_str = $c_price > 0 ? ('₱' . number_format($c_price, 0)) : 'Inquire for Price';
          $c_brand = strtolower($car['brand'] ?? 'toyota');
          $c_trans = strtolower($car['transmission'] ?? 'automatic');
          $c_body = strtolower($car['body_type'] ?? 'sedan');
          $c_stock = strtoupper($car['stock_id'] ?: ('UW-' . $car['id']));
          $c_mileage = $car['mileage'] ?: 'Low Mileage';
          $c_seating = $car['seating'] ?: '5 Seats';
          $c_img = !empty($car['image_file']) ? $car['image_file'] : 'car-1.jpg';
          $c_img_url = 'assets/images/cars/' . $c_img;
          $c_status = strtoupper(!empty($car['status']) ? $car['status'] : 'AVAILABLE');
          $c_status_class = strtolower($c_status);
          $c_all_images = !empty($car['all_images']) ? $car['all_images'] : [$c_img];
        ?>
          <div class="car-card <?php echo ($c_status === 'SOLD') ? 'is-sold' : ''; ?>" 
               data-id="<?php echo esc_attr($car['id']); ?>"
               data-brand="<?php echo esc_attr($c_brand); ?>"
               data-price="<?php echo esc_attr($c_price); ?>"
               data-trans="<?php echo esc_attr($c_trans); ?>"
               data-body="<?php echo esc_attr($c_body); ?>"
               data-status="<?php echo esc_attr($c_status); ?>"
               data-name="<?php echo esc_attr(strtolower($car['name'])); ?>"
               data-stock="<?php echo esc_attr(strtolower($c_stock)); ?>"
               data-images="<?php echo esc_attr(json_encode($c_all_images)); ?>">

            <!-- Car Photo & Status Badge -->
            <div class="car-card-img-wrap">
              <img src="<?php echo esc_url($c_img_url); ?>" alt="<?php echo esc_attr($car['name']); ?>" class="car-card-photo" loading="lazy" onerror="this.src='assets/images/cars/car-1.jpg'">
              <span class="card-status-badge badge-<?php echo esc_attr($c_status_class); ?>"><?php echo esc_html($c_status); ?></span>
              <span class="card-stock-tag">#<?php echo esc_html($c_stock); ?></span>
              <?php if (count($c_all_images) > 1): ?>
                <span class="card-photo-count" title="Hover over card to preview gallery"><?php echo count($c_all_images); ?> Photos</span>
              <?php endif; ?>
            </div>

            <!-- Card Body Content -->
            <div class="car-card-body">
              <div class="car-card-price"><?php echo esc_html($c_price_str); ?></div>
              <h3 class="car-card-title"><?php echo esc_html($car['name']); ?></h3>
              
              <!-- Quick Specs Pills -->
              <div class="car-specs-chips">
                <span class="chip-item"><?php echo esc_html($car['transmission'] ?? 'Automatic'); ?></span>
                <span class="chip-item"><?php echo esc_html($c_mileage); ?></span>
                <span class="chip-item"><?php echo esc_html($car['fuel_type'] ?? 'Gasoline'); ?></span>
                <span class="chip-item"><?php echo esc_html($c_seating); ?></span>
              </div>

              <!-- Action Buttons -->
              <div class="car-card-actions">
                <button type="button" class="btn-card-details" onclick='openCarDetailsModal(<?php echo json_encode($car); ?>)'>
                  <span>View Car Details</span>
                </button>

                <button type="button" class="btn-card-messenger" onclick='inquireMessengerLead(<?php echo json_encode($car); ?>)' title="Inquire immediately on Messenger">
                  <svg viewBox="0 0 28 28" width="18" height="18" fill="currentColor">
                    <path d="M14 2C7.373 2 2 6.945 2 13.045c0 3.477 1.745 6.577 4.473 8.577V26l4.24-2.327c1.045.291 2.155.455 3.287.455 6.627 0 12-4.945 12-11.045S20.627 2 14 2zm1.2 14.882l-3.073-3.282-6 3.282 6.6-7.009 3.145 3.282 5.928-3.282-6.6 7.009z"/>
                  </svg>
                  <span>Inquire via Messenger</span>
                </button>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- No Matching Results Message -->
      <div id="noCatalogResults" class="no-results-box" style="display:none;">
        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🔍</div>
        <h3>No Vehicles Matched Your Search</h3>
        <p>Try clearing your budget filter or searching for another brand. We receive fresh pre-owned trade-in units weekly!</p>
        <button type="button" class="btn-reset-filters" onclick="resetAllCatalogFilters()">Reset All Filters</button>
      </div>

    </div>
  </section>

  <!-- ========================================================
       3. TRADE-IN SPECIALISTS SECTION
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
          <!-- Step 1 -->
          <div class="step-card">
            <div class="step-badge">1</div>
            <h3 class="step-title">Tell Us About Your Car</h3>
            <p class="step-desc">Send us your car details (make, model, year, mileage) via Messenger, or bring it directly to our showroom for quick visual check.</p>
          </div>

          <!-- Step 2 -->
          <div class="step-card">
            <div class="step-badge">2</div>
            <h3 class="step-title">Free Fair Appraisal</h3>
            <p class="step-desc">Our certified car appraisers give you a transparent, competitive valuation within 15 minutes with no hidden deductions.</p>
          </div>

          <!-- Step 3 -->
          <div class="step-card">
            <div class="step-badge">3</div>
            <h3 class="step-title">Drive Home Your Upgrade</h3>
            <p class="step-desc">Use your agreed trade-in value as direct payment or downpayment. We handle the paperwork and you drive home your new car today!</p>
          </div>
        </div>

        <!-- Trade-in Messenger Action -->
        <div class="tradein-action-bar">
          <a href="https://m.me/uniwheels?text=Hello%20UniWheels!%20I%20would%20like%20to%20get%20a%20free%20trade-in%20appraisal%20for%20my%20car." target="_blank" rel="noopener noreferrer" class="btn-tradein-messenger">
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
       4. SHOWROOM VISIT & DIRECT CONTACT SECTION
       ======================================================== -->
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

        <!-- Direct Contact Details -->
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
              <a href="https://m.me/uniwheels" target="_blank" rel="noopener noreferrer" class="link-showroom-chat">
                Chat Directly on Messenger &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<!-- Car Details Modal Data Structure injected into JavaScript -->
<script>
  window.UNIWHEELS_FEATURED_CARS = <?php echo json_encode(array_values($hero_cars)); ?>;
  window.UNIWHEELS_CATALOG_CARS = <?php echo json_encode(array_values($catalog_cars)); ?>;
  window.THEME_BASE_URL = '.';
</script>

<?php
include __DIR__ . '/footer.php';
