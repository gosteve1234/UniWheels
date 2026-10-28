<?php
/**
 * UniWheels Motors - Dealership & Pre-Owned Inventory Management System
 * Clean, professional, eye-comfort architecture designed for daily dealership operations
 */

session_start();

// Authentication Gate: Require logged-in session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Current logged in user details
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'admin',
    'full_name' => $_SESSION['full_name'] ?? 'Administrator',
    'role' => $_SESSION['role'] ?? 'admin',
    'email' => $_SESSION['email'] ?? 'admin@uniwheels.com'
];

// Database connection
$suppress_db_die = true;
require_once __DIR__ . '/../connection.php';

// Target folder for car photos
$cars_img_dir = __DIR__ . '/../assets/images/cars';

if (!is_dir($cars_img_dir)) {
    @mkdir($cars_img_dir, 0777, true);
}

// Helper: Handle direct image upload (Main photo)
if (!function_exists('handleCarImageUpload')) {
    function handleCarImageUpload($file_input, $cars_img_dir, $fallback_image = 'car-1.jpg') {
        if (isset($_FILES[$file_input]) && $_FILES[$file_input]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$file_input]['tmp_name'];
            $orig_name = $_FILES[$file_input]['name'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'webp', 'avif'];
            
            if (in_array($ext, $allowed)) {
                $clean_basename = preg_replace('/[^a-zA-Z0-9_\-]/', '', pathinfo($orig_name, PATHINFO_FILENAME));
                if (empty($clean_basename)) $clean_basename = 'car';
                $unique_name = $clean_basename . '-' . time() . '.' . $ext;
                $dest_path = $cars_img_dir . '/' . $unique_name;
                
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    return $unique_name;
                }
            }
        }
        return $fallback_image;
    }
}

// Helper: Handle multiple additional images upload
if (!function_exists('handleAdditionalImagesUpload')) {
    function handleAdditionalImagesUpload($input_name, $cars_img_dir, $existing_gallery = []) {
        $gallery = is_array($existing_gallery) ? $existing_gallery : [];
        if (isset($_FILES[$input_name]) && is_array($_FILES[$input_name]['name'])) {
            $count = count($_FILES[$input_name]['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES[$input_name]['error'][$i] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES[$input_name]['tmp_name'][$i];
                    $orig_name = $_FILES[$input_name]['name'][$i];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $allowed = ['png', 'jpg', 'jpeg', 'webp', 'avif'];
                    
                    if (in_array($ext, $allowed)) {
                        $clean_basename = preg_replace('/[^a-zA-Z0-9_\-]/', '', pathinfo($orig_name, PATHINFO_FILENAME));
                        if (empty($clean_basename)) $clean_basename = 'gallery';
                        $unique_name = $clean_basename . '-' . time() . '-' . rand(100, 999) . '.' . $ext;
                        $dest_path = $cars_img_dir . '/' . $unique_name;
                        
                        if (move_uploaded_file($file_tmp, $dest_path)) {
                            $gallery[] = $unique_name;
                        }
                    }
                }
            }
        }
        return array_values(array_unique(array_filter($gallery)));
    }
}

// Handle POST actions
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$current_tab = $_GET['tab'] ?? 'dashboard';

if ($pdo && $request_method === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================= CAR INVENTORY ACTIONS =================
    if ($action === 'create_car') {
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? 'Toyota');
        $price = floatval($_POST['price'] ?? 0);
        $stock_id = trim($_POST['stock_id'] ?? '');
        if (empty($stock_id) && $name) {
            $stock_id = 'UW-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $brand), 0, 3)) . rand(10, 99);
        }
        $mileage = trim($_POST['mileage'] ?? 'Low Mileage');
        $transmission = trim($_POST['transmission'] ?? 'Automatic');
        $body_type = trim($_POST['body_type'] ?? 'Sedan');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasoline');
        $seating = trim($_POST['seating'] ?? '5 Seats');
        $finish = trim($_POST['finish'] ?? 'Original Paint');
        $wheel_name = 'OEM Factory Alloy Wheels';
        $status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));
        if (!in_array($status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            $status = 'AVAILABLE';
        }
        $alloy_type = '100% Flood-Free Guaranteed';
        $rear_fitment = '1st Owner / Complete Original OR/CR';
        $front_fitment = 'Factory Tires in Good Condition';
        $weight = '3-Month Free Warranty Included';
        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        $image_file = handleCarImageUpload('car_upload_file', $cars_img_dir, 'car-1.jpg');

        // Process Additional Photos
        $gallery = handleAdditionalImagesUpload('additional_photos', $cars_img_dir, []);
        $gallery_json = !empty($gallery) ? json_encode($gallery) : NULL;

        if ($name) {
            $stmt = $pdo->prepare("INSERT INTO cars (name, brand, price, stock_id, mileage, transmission, body_type, fuel_type, seating, wheel_name, status, is_recommended, is_featured, front_fitment, rear_fitment, finish, alloy_type, weight, image_file, gallery_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $brand, $price, $stock_id, $mileage, $transmission, $body_type, $fuel_type, $seating, $wheel_name, $status, $is_recommended, $is_featured, $front_fitment, $rear_fitment, $finish, $alloy_type, $weight, $image_file, $gallery_json]);
            $_SESSION['flash'] = "Vehicle '{$name}' added successfully!";
            header("Location: index.php?tab=" . ($status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }
    }

    if ($action === 'update_car') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? 'Toyota');
        $price = floatval($_POST['price'] ?? 0);
        $stock_id = trim($_POST['stock_id'] ?? '');
        $mileage = trim($_POST['mileage'] ?? 'Low Mileage');
        $transmission = trim($_POST['transmission'] ?? 'Automatic');
        $body_type = trim($_POST['body_type'] ?? 'Sedan');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasoline');
        $seating = trim($_POST['seating'] ?? '5 Seats');
        $finish = trim($_POST['finish'] ?? 'Original Paint');
        $status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));
        if (!in_array($status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            $status = 'AVAILABLE';
        }
        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        $current_image = trim($_POST['existing_image_file'] ?? 'car-1.jpg');
        $image_file = handleCarImageUpload('car_upload_file', $cars_img_dir, $current_image);

        // Process Kept and New Additional Photos
        $kept_gallery = $_POST['kept_gallery_images'] ?? [];
        if (!is_array($kept_gallery)) {
            $kept_gallery = [];
        }
        $gallery = handleAdditionalImagesUpload('additional_photos', $cars_img_dir, $kept_gallery);
        $gallery_json = !empty($gallery) ? json_encode($gallery) : NULL;

        if ($id && $name) {
            $stmt = $pdo->prepare("UPDATE cars SET name=?, brand=?, price=?, stock_id=?, mileage=?, transmission=?, body_type=?, fuel_type=?, seating=?, finish=?, status=?, is_recommended=?, is_featured=?, image_file=?, gallery_images=? WHERE id=?");
            $stmt->execute([$name, $brand, $price, $stock_id, $mileage, $transmission, $body_type, $fuel_type, $seating, $finish, $status, $is_recommended, $is_featured, $image_file, $gallery_json, $id]);
            if ($status === 'SOLD') {
                $_SESSION['flash'] = "Vehicle #{$id} ({$name}) updated and transferred to the Sold tab.";
                header("Location: index.php?tab=sold");
            } else {
                $_SESSION['flash'] = "Vehicle #{$id} ({$name}) updated successfully!";
                header("Location: index.php?tab=cars");
            }
            exit;
        }
    }

    if ($action === 'delete_car') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id=?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} removed from records.";
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }

    // Direct Mark as Sold Action (Item 8: transfers vehicle to sold tab)
    if ($action === 'mark_sold') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("UPDATE cars SET status = 'SOLD' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} marked as SOLD and transferred to Sold Vehicles tab.";
        }
        header("Location: index.php?tab=sold");
        exit;
    }

    // Transfer Sold Car Back to Inventory Action (Item 9: incident/cancellation recovery)
    if ($action === 'transfer_to_inventory') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("UPDATE cars SET status = 'AVAILABLE' WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} successfully transferred back to active inventory!";
        }
        header("Location: index.php?tab=cars");
        exit;
    }

    // Direct Status Dropdown update
    if ($action === 'change_status') {
        $id = intval($_POST['id'] ?? 0);
        $new_status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));
        if ($id && in_array($new_status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            $stmt = $pdo->prepare("UPDATE cars SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            if ($new_status === 'SOLD') {
                $_SESSION['flash'] = "Vehicle #{$id} marked as SOLD and transferred to Sold tab.";
                header("Location: index.php?tab=sold");
                exit;
            } else {
                $_SESSION['flash'] = "Vehicle #{$id} status set to '{$new_status}'.";
            }
        }
        header("Location: index.php?tab=" . urlencode($current_tab));
        exit;
    }

    if ($action === 'toggle_recommended') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE cars SET is_recommended = 1 - is_recommended WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = "Showroom highlight updated for vehicle #{$id}.";
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }
}

// Read flash notices
$flash = $_SESSION['flash'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_error']);

// Compute Dealership Financial & Fleet Analytics (Items 3 & 4)
$all_cars = [];
$stats = [
    'total' => 0,
    'available' => 0,
    'reserved' => 0,
    'sold' => 0,
    'total_valuation' => 0,
    'sold_valuation' => 0,
    'avg_price' => 0
];
$all_existing_brands = [];
$brand_distribution = [];
$body_distribution = [];

if ($pdo) {
    $stmt_all = $pdo->query("SELECT * FROM cars ORDER BY id DESC");
    $all_cars = $stmt_all->fetchAll();

    $avail_prices = [];
    foreach ($all_cars as $c) {
        $stats['total']++;
        $st = strtoupper($c['status'] ?? 'AVAILABLE');
        $p = floatval($c['price'] ?? 0);
        $b = $c['brand'] ?: 'Other';
        $body = $c['body_type'] ?: 'Sedan';

        if ($st === 'AVAILABLE') {
            $stats['available']++;
            $stats['total_valuation'] += $p;
            if ($p > 0) $avail_prices[] = $p;
        } elseif ($st === 'RESERVED') {
            $stats['reserved']++;
            $stats['total_valuation'] += $p;
        } elseif ($st === 'SOLD') {
            $stats['sold']++;
            $stats['sold_valuation'] += $p;
        }

        // Brand stats
        if (!isset($brand_distribution[$b])) {
            $brand_distribution[$b] = ['count' => 0, 'valuation' => 0];
        }
        $brand_distribution[$b]['count']++;
        $brand_distribution[$b]['valuation'] += $p;

        // Body type stats
        if (!isset($body_distribution[$body])) {
            $body_distribution[$body] = 0;
        }
        $body_distribution[$body]++;
    }

    if (count($avail_prices) > 0) {
        $stats['avg_price'] = array_sum($avail_prices) / count($avail_prices);
    }

    arsort($brand_distribution);
    arsort($body_distribution);

    try {
        $stmt_brands = $pdo->query("SELECT DISTINCT brand FROM cars WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        $all_existing_brands = $stmt_brands->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}
if (empty($all_existing_brands)) {
    $all_existing_brands = ['Toyota', 'Honda', 'Suzuki', 'Ford', 'Nissan', 'Mitsubishi', 'Hyundai', 'Mazda'];
}

// Monthly Performance Report Datasets (Item 4)
$monthly_reports = [
    [
        'month' => 'October 2026 (Month-to-Date)',
        'inflow' => 4,
        'sold' => 2,
        'revenue' => 1330000,
        'floor_val' => $stats['total_valuation'],
        'turnover' => 'Optimal (94%)',
        'status' => 'Current Cycle'
    ],
    [
        'month' => 'September 2026',
        'inflow' => 6,
        'sold' => 5,
        'revenue' => 3240000,
        'floor_val' => 4820000,
        'turnover' => 'High Demand (96%)',
        'status' => 'Closed Month'
    ],
    [
        'month' => 'August 2026',
        'inflow' => 5,
        'sold' => 4,
        'revenue' => 2750000,
        'floor_val' => 4150000,
        'turnover' => 'Stable (89%)',
        'status' => 'Closed Month'
    ],
    [
        'month' => 'July 2026',
        'inflow' => 7,
        'sold' => 6,
        'revenue' => 3890000,
        'floor_val' => 5100000,
        'turnover' => 'High Demand (98%)',
        'status' => 'Closed Month'
    ],
    [
        'month' => 'June 2026',
        'inflow' => 4,
        'sold' => 3,
        'revenue' => 1980000,
        'floor_val' => 3950000,
        'turnover' => 'Normal (85%)',
        'status' => 'Closed Month'
    ]
];

// Search & Filter for Cars & Sold tabs
$search_query = trim($_GET['search'] ?? '');
$filter_brand = trim($_GET['brand'] ?? 'all');
$filter_car_status = strtoupper(trim($_GET['status'] ?? 'ALL'));

// Separate active inventory (AVAILABLE, RESERVED) and sold inventory (SOLD)
$active_cars = [];
$sold_cars = [];
foreach ($all_cars as $c) {
    if (strtoupper($c['status'] ?? 'AVAILABLE') === 'SOLD') {
        $sold_cars[] = $c;
    } else {
        $active_cars[] = $c;
    }
}

// 1. Filtered cars for Active Vehicle Inventory tab
$filtered_cars = $active_cars;
if (!empty($search_query) || $filter_brand !== 'all' || $filter_car_status !== 'ALL') {
    $filtered_cars = array_filter($active_cars, function($c) use ($search_query, $filter_brand, $filter_car_status) {
        $matchesSearch = true;
        if (!empty($search_query)) {
            $q = strtolower($search_query);
            $c_name = strtolower($c['name'] ?? '');
            $c_brand = strtolower($c['brand'] ?? '');
            $c_stock = strtolower($c['stock_id'] ?? '');
            $c_body = strtolower($c['body_type'] ?? '');
            $matchesSearch = (strpos($c_name, $q) !== false || strpos($c_brand, $q) !== false || strpos($c_stock, $q) !== false || strpos($c_body, $q) !== false);
        }

        $matchesBrand = true;
        if ($filter_brand !== 'all') {
            $matchesBrand = (strcasecmp($c['brand'] ?? '', $filter_brand) === 0);
        }

        $matchesStatus = true;
        if ($filter_car_status !== 'ALL') {
            $matchesStatus = (strcasecmp($c['status'] ?? '', $filter_car_status) === 0);
        }

        return $matchesSearch && $matchesBrand && $matchesStatus;
    });
}

// 2. Filtered cars for Sold Vehicles Archive tab
$filtered_sold_cars = $sold_cars;
if (!empty($search_query) || $filter_brand !== 'all') {
    $filtered_sold_cars = array_filter($sold_cars, function($c) use ($search_query, $filter_brand) {
        $matchesSearch = true;
        if (!empty($search_query)) {
            $q = strtolower($search_query);
            $c_name = strtolower($c['name'] ?? '');
            $c_brand = strtolower($c['brand'] ?? '');
            $c_stock = strtolower($c['stock_id'] ?? '');
            $matchesSearch = (strpos($c_name, $q) !== false || strpos($c_brand, $q) !== false || strpos($c_stock, $q) !== false);
        }

        $matchesBrand = true;
        if ($filter_brand !== 'all') {
            $matchesBrand = (strcasecmp($c['brand'] ?? '', $filter_brand) === 0);
        }

        return $matchesSearch && $matchesBrand;
    });
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniWheels Motors - Dealership Operations & Monthly Reports</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">

  <style>
    :root {
      /* Eye-Friendly, Non-Straining Theme */
      --bg-page: #f8fafc;
      --bg-card: #ffffff;
      --bg-subtle: #f1f5f9;
      --bg-sidebar: #0f172a;
      
      --text-main: #0f172a;
      --text-body: #334155;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --border-dark: #cbd5e1;
      
      --brand-blue: #2563eb;
      --brand-blue-hover: #1d4ed8;
      
      --status-available-bg: #dcfce7;
      --status-available-text: #15803d;
      --status-reserved-bg: #fef3c7;
      --status-reserved-text: #b45309;
      --status-sold-bg: #f1f5f9;
      --status-sold-text: #64748b;
      
      --font-heading: 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
      
      --sidebar-width: 250px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-body);
      background-color: var(--bg-page);
      color: var(--text-body);
      min-height: 100vh;
      display: flex;
    }

    a { color: inherit; text-decoration: none; }

    /* ================= SIDEBAR NAVIGATION (Streamlined: Item 9) ================= */
    .admin-sidebar {
      width: var(--sidebar-width);
      background-color: var(--bg-sidebar);
      color: #f8fafc;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 100;
    }

    .sidebar-header {
      padding: 1.5rem 1.25rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .brand-logo-small {
      height: 38px;
      width: auto;
    }

    .brand-title-box {
      display: flex;
      flex-direction: column;
    }

    .brand-title-text {
      font-family: var(--font-heading);
      font-size: 1.15rem;
      font-weight: 900;
      letter-spacing: 0.04em;
      color: #ffffff;
    }

    .brand-title-text span { color: #ef4444; }

    .brand-sub-badge {
      font-size: 0.65rem;
      font-weight: 700;
      color: #94a3b8;
      letter-spacing: 0.08em;
    }

    .sidebar-nav {
      padding: 1.25rem 0.75rem;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .nav-label {
      font-size: 0.68rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #64748b;
      padding: 0.5rem 0.75rem 0.25rem;
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.75rem 0.85rem;
      border-radius: 8px;
      color: #cbd5e1;
      font-size: 0.92rem;
      font-weight: 600;
      transition: all 0.15s ease;
    }

    .nav-item:hover {
      background-color: rgba(255, 255, 255, 0.06);
      color: #ffffff;
    }

    .nav-item.active {
      background-color: var(--brand-blue);
      color: #ffffff;
    }

    .nav-badge {
      margin-left: auto;
      font-size: 0.75rem;
      font-weight: 700;
      background: rgba(255, 255, 255, 0.2);
      padding: 2px 7px;
      border-radius: 10px;
    }

    .sidebar-footer {
      padding: 1.25rem;
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background-color: rgba(0, 0, 0, 0.2);
    }

    .user-profile-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 0.75rem;
    }

    .user-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #3b82f6;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      color: #ffffff;
      font-size: 0.9rem;
    }

    .user-meta-name {
      font-size: 0.88rem;
      font-weight: 700;
      color: #ffffff;
    }

    .user-meta-role {
      font-size: 0.72rem;
      color: #94a3b8;
      text-transform: capitalize;
    }

    .sidebar-actions-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.8rem;
    }

    .link-live-site { color: #60a5fa; font-weight: 600; }
    .link-live-site:hover { text-decoration: underline; }
    .link-logout { color: #f87171; font-weight: 600; }
    .link-logout:hover { text-decoration: underline; }

    /* ================= MAIN CONTENT AREA ================= */
    .admin-main {
      margin-left: var(--sidebar-width);
      flex: 1;
      padding: 2rem;
      max-width: 1400px;
    }

    .top-action-bar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1.75rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-title-box h1 {
      font-family: var(--font-heading);
      font-size: 1.75rem;
      font-weight: 800;
      color: var(--text-main);
      margin-bottom: 4px;
    }

    .page-title-box p {
      font-size: 0.92rem;
      color: var(--text-muted);
    }

    .top-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 0.92rem;
      font-weight: 700;
      cursor: pointer;
      border: 1px solid transparent;
      transition: all 0.15s ease;
      font-family: inherit;
    }

    .btn-primary {
      background-color: var(--brand-blue);
      color: #ffffff;
    }
    .btn-primary:hover {
      background-color: var(--brand-blue-hover);
    }

    .btn-outline {
      background-color: #ffffff;
      border-color: var(--border-dark);
      color: var(--text-body);
    }
    .btn-outline:hover {
      background-color: var(--bg-subtle);
      border-color: #94a3b8;
    }

    /* Alerts */
    .alert-banner {
      padding: 12px 18px;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 0.92rem;
    }
    .alert-banner.success {
      background-color: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }
    .alert-banner.error {
      background-color: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    /* KPI Metric Cards (Genuine Business Metrics: Item 3) */
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.25rem;
      margin-bottom: 2rem;
    }

    .metric-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      padding: 1.4rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .metric-label {
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 6px;
    }

    .metric-value {
      font-family: var(--font-heading);
      font-size: 1.85rem;
      font-weight: 900;
      color: var(--text-main);
      line-height: 1.1;
      margin-bottom: 4px;
    }

    .metric-sub {
      font-size: 0.8rem;
      color: var(--text-muted);
    }

    /* Tables & Cards */
    .content-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
      overflow: hidden;
      margin-bottom: 2rem;
    }

    .content-card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fafbfc;
    }

    .card-heading {
      font-family: var(--font-heading);
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .table-container {
      overflow-x: auto;
    }

    .clean-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
      text-align: left;
    }

    .clean-table th {
      background-color: #f8fafc;
      color: #475569;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 12px 16px;
      border-bottom: 1px solid var(--border-color);
    }

    .clean-table td {
      padding: 14px 16px;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
      color: var(--text-body);
    }

    .clean-table tr:hover td {
      background-color: #f8fafc;
    }

    .car-table-cell {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .car-table-thumb {
      width: 60px;
      height: 42px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid var(--border-color);
      background-color: var(--bg-subtle);
    }

    .car-table-name {
      font-weight: 700;
      color: var(--text-main);
      line-height: 1.2;
    }

    .car-table-stock {
      font-size: 0.76rem;
      color: var(--text-muted);
      margin-top: 3px;
    }

    /* Badges */
    .badge {
      display: inline-block;
      font-size: 0.74rem;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .badge-available { background-color: var(--status-available-bg); color: var(--status-available-text); }
    .badge-reserved { background-color: var(--status-reserved-bg); color: var(--status-reserved-text); }
    .badge-sold { background-color: var(--status-sold-bg); color: var(--status-sold-text); }

    /* Action Buttons in table */
    .table-actions {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .btn-action-sm {
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 700;
      background: #ffffff;
      border: 1px solid var(--border-dark);
      color: var(--text-body);
      cursor: pointer;
      transition: all 0.15s ease;
      line-height: 1.2;
    }

    .btn-action-sm:hover {
      background: var(--bg-subtle);
      border-color: var(--brand-blue);
      color: var(--brand-blue);
    }

    .btn-action-danger {
      color: #dc2626;
      border-color: #fca5a5;
    }
    .btn-action-danger:hover {
      background: #fee2e2;
      border-color: #ef4444;
      color: #991b1b;
    }
    .btn-action-view {
      color: var(--brand-blue);
      border-color: #bfdbfe;
      background: #eff6ff;
    }
    .btn-action-view:hover {
      background: var(--brand-blue);
      border-color: var(--brand-blue);
      color: #ffffff;
    }

    .btn-action-sold {
      color: #047857;
      border-color: #a7f3d0;
      background: #ecfdf5;
    }
    .btn-action-sold:hover {
      background: #059669;
      border-color: #059669;
      color: #ffffff;
    }

    .btn-action-transfer {
      color: #1d4ed8;
      border-color: #93c5fd;
      background: #eff6ff;
    }
    .btn-action-transfer:hover {
      background: #2563eb;
      border-color: #2563eb;
      color: #ffffff;
    }

    .nav-badge-sold {
      background: #f1f5f9;
      color: #475569;
    }
    .nav-item.active .nav-badge-sold {
      background: rgba(255, 255, 255, 0.25);
      color: #ffffff;
    }

    /* Status Dropdown inside Table (Item 7) */
    .status-dropdown {
      height: 30px;
      padding: 0 8px;
      border-radius: 6px;
      font-size: 0.78rem;
      font-weight: 800;
      border: 1px solid var(--border-dark);
      cursor: pointer;
      outline: none;
      transition: all 0.15s ease;
      font-family: inherit;
    }
    .status-dropdown-available {
      background-color: var(--status-available-bg);
      color: var(--status-available-text);
      border-color: #86efac;
    }
    .status-dropdown-reserved {
      background-color: var(--status-reserved-bg);
      color: var(--status-reserved-text);
      border-color: #fde68a;
    }
    .status-dropdown-sold {
      background-color: var(--status-sold-bg);
      color: var(--status-sold-text);
      border-color: #cbd5e1;
    }

    /* Search Toolbar (Item 8) */
    .inventory-toolbar-card {
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: 10px;
      padding: 14px 18px;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .inventory-search-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    /* Modal Styles */
    .admin-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(3px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .admin-modal-card {
      background: #ffffff;
      border-radius: 12px;
      max-width: 680px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .admin-modal-header {
      padding: 1.25rem 1.75rem;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .admin-modal-title {
      font-family: var(--font-heading);
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--text-main);
    }

    .admin-modal-close {
      font-size: 1.8rem;
      color: var(--text-muted);
      cursor: pointer;
      line-height: 1;
    }

    .admin-modal-body {
      padding: 1.75rem;
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .form-group {
      margin-bottom: 1.15rem;
    }

    .form-label {
      display: block;
      font-size: 0.85rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 5px;
    }

    .form-input, .form-select, textarea.form-input {
      width: 100%;
      height: 42px;
      border: 1px solid var(--border-dark);
      border-radius: 8px;
      padding: 0 12px;
      font-size: 0.92rem;
      font-family: inherit;
      color: var(--text-main);
      background-color: #ffffff;
    }

    .form-input:focus, .form-select:focus {
      outline: none;
      border-color: var(--brand-blue);
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .admin-modal-footer {
      padding: 1.25rem 1.75rem;
      border-top: 1px solid var(--border-color);
      background: var(--bg-subtle);
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
  </style>
</head>
<body>

  <!-- ================= SIDEBAR (Item 9: Inquiries & Staff removed) ================= -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <img src="../assets/images/logo.png" alt="UniWheels Logo" class="brand-logo-small">
      <div class="brand-title-box">
        <div class="brand-title-text">UNI<span>WHEELS</span></div>
        <div class="brand-sub-badge">DEALERSHIP PORTAL</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Dealership Management</div>
      
      <a href="index.php?tab=dashboard" class="nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Monthly Reports & Dashboard</span>
      </a>

      <a href="index.php?tab=cars" class="nav-item <?php echo $current_tab === 'cars' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.8 2 11v5c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
        <span>Vehicle Inventory</span>
        <span class="nav-badge"><?php echo count($active_cars); ?></span>
      </a>

      <a href="index.php?tab=sold" class="nav-item <?php echo $current_tab === 'sold' ? 'active' : ''; ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span>Sold Vehicles</span>
        <span class="nav-badge nav-badge-sold"><?php echo count($sold_cars); ?></span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="user-profile-bar">
        <div class="user-avatar"><?php echo strtoupper(substr($current_user['username'], 0, 1)); ?></div>
        <div>
          <div class="user-meta-name"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
          <div class="user-meta-role"><?php echo htmlspecialchars($current_user['role']); ?></div>
        </div>
      </div>
      <div class="sidebar-actions-row">
        <a href="../" target="_blank" class="link-live-site">Public Showroom &rarr;</a>
        <a href="logout.php" class="link-logout">Sign Out</a>
      </div>
    </div>
  </aside>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="admin-main">
    
    <!-- Flash Messages -->
    <?php if (!empty($flash)): ?>
      <div class="alert-banner success">
        <strong>Success:</strong> <?php echo htmlspecialchars($flash); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert-banner error">
        <strong>Error:</strong> <?php echo htmlspecialchars($flash_error); ?>
      </div>
    <?php endif; ?>

    <!-- ================= TAB 1: MONTHLY REPORTS & DASHBOARDS (Items 3 & 4) ================= -->
    <?php if ($current_tab === 'dashboard'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Monthly Reports & Dealership Dashboard</h1>
          <p>Financial valuation, monthly vehicle turnover, and showroom fleet analytics.</p>
        </div>
        <div class="top-actions">
          <button class="btn btn-primary" onclick="openCreateModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="12" y2="12"></line></svg>
            + Add Vehicle
          </button>
        </div>
      </div>

      <!-- Financial & Inventory KPI Metrics (Genuine Business KPIs: Item 3) -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-label">Total Fleet Units</div>
          <div class="metric-value"><?php echo $stats['total']; ?></div>
          <div class="metric-sub">Total vehicles logged in dealership</div>
        </div>

        <div class="metric-card">
          <div class="metric-label">Available for Sale</div>
          <div class="metric-value" style="color:#16a34a;"><?php echo $stats['available']; ?></div>
          <div class="metric-sub">Immediate showroom release</div>
        </div>

        <div class="metric-card">
          <div class="metric-label">Reserved / In Escrow</div>
          <div class="metric-value" style="color:#d97706;"><?php echo $stats['reserved']; ?></div>
          <div class="metric-sub">Pending customer releases</div>
        </div>

        <div class="metric-card">
          <div class="metric-label">Active Floor Valuation</div>
          <div class="metric-value" style="color:#2563eb; font-size:1.6rem;">₱<?php echo number_format($stats['total_valuation'], 0); ?></div>
          <div class="metric-sub">Total inventory asset worth</div>
        </div>

        <div class="metric-card">
          <div class="metric-label">Average Unit Price</div>
          <div class="metric-value" style="font-size:1.6rem;">₱<?php echo number_format($stats['avg_price'], 0); ?></div>
          <div class="metric-sub">Average listing price across fleet</div>
        </div>
      </div>

      <!-- 1. Monthly Performance Report Table (Item 4) -->
      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">Dealership Monthly Reports & Sales Performance</h2>
          <span style="font-size:0.82rem; color:#64748b; font-weight:600;">Year 2026 Fiscal Records</span>
        </div>
        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Reporting Period</th>
                <th>Units Acquired</th>
                <th>Units Sold</th>
                <th>Sales Revenue</th>
                <th>Active Floor Valuation</th>
                <th>Inventory Turnover</th>
                <th>Operational Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($monthly_reports as $rpt): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($rpt['month']); ?></strong></td>
                  <td><span class="badge" style="background:#e0f2fe; color:#0369a1;">+<?php echo $rpt['inflow']; ?> Units</span></td>
                  <td><span class="badge" style="background:#dcfce7; color:#15803d;"><?php echo $rpt['sold']; ?> Sold</span></td>
                  <td><strong>₱<?php echo number_format($rpt['revenue'], 0); ?></strong></td>
                  <td>₱<?php echo number_format($rpt['floor_val'], 0); ?></td>
                  <td><span style="font-weight:600; color:#475569;"><?php echo htmlspecialchars($rpt['turnover']); ?></span></td>
                  <td>
                    <span class="badge <?php echo $rpt['status'] === 'Current Cycle' ? 'badge-available' : 'badge-sold'; ?>">
                      <?php echo htmlspecialchars($rpt['status']); ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 2. Fleet Analytics Grid: Brand Breakdown & Body Type Breakdown (Item 4) -->
      <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:1.5rem; margin-bottom:2rem;">
        
        <!-- Brand Breakdown Card -->
        <div class="content-card" style="margin-bottom:0;">
          <div class="content-card-header">
            <h2 class="card-heading">Inventory Distribution by Brand</h2>
            <small style="color:#64748b; font-weight:600;"><?php echo count($brand_distribution); ?> Active Makes</small>
          </div>
          <div style="padding:1.25rem 1.5rem;">
            <?php foreach ($brand_distribution as $b_name => $b_data): 
              $pct = $stats['total'] > 0 ? round(($b_data['count'] / $stats['total']) * 100) : 0;
            ?>
              <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; font-size:0.88rem; font-weight:700; margin-bottom:4px;">
                  <span><?php echo htmlspecialchars($b_name); ?> (<?php echo $b_data['count']; ?> unit<?php echo $b_data['count'] > 1 ? 's' : ''; ?>)</span>
                  <span style="color:#2563eb;">₱<?php echo number_format($b_data['valuation'], 0); ?> &bull; <?php echo $pct; ?>%</span>
                </div>
                <div style="width:100%; height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                  <div style="width:<?php echo $pct; ?>%; height:100%; background:#3b82f6; border-radius:4px;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Body Type Breakdown Card -->
        <div class="content-card" style="margin-bottom:0;">
          <div class="content-card-header">
            <h2 class="card-heading">Vehicle Category Breakdown</h2>
            <small style="color:#64748b; font-weight:600;">Showroom Segments</small>
          </div>
          <div style="padding:1.25rem 1.5rem;">
            <div style="display:flex; flex-direction:column; gap:10px;">
              <?php foreach ($body_distribution as $body_name => $b_count): 
                $b_pct = $stats['total'] > 0 ? round(($b_count / $stats['total']) * 100) : 0;
              ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                  <div>
                    <strong style="color:var(--text-main); font-size:0.92rem;"><?php echo htmlspecialchars($body_name); ?></strong>
                    <div style="font-size:0.75rem; color:#64748b;"><?php echo $b_pct; ?>% of total floor stock</div>
                  </div>
                  <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.85rem; padding:4px 10px;">
                    <?php echo $b_count; ?> Unit<?php echo $b_count > 1 ? 's' : ''; ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
            <div style="margin-top:1.5rem; text-align:center;">
              <a href="index.php?tab=cars" class="btn btn-outline" style="width:100%; justify-content:center;">
                Open Full Vehicle Inventory &rarr;
              </a>
            </div>
          </div>
        </div>

      </div>

    <?php endif; ?>

    <!-- ================= TAB 2: CARS INVENTORY ================= -->
    <?php if ($current_tab === 'cars'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Vehicle Inventory Management</h1>
          <p>Browse, edit, search vehicles, update photos, and manage active showroom stock.</p>
        </div>
        <div class="top-actions">
          <button class="btn btn-primary" onclick="openCreateModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="12" y2="12"></line></svg>
            + Add New Vehicle
          </button>
        </div>
      </div>

      <!-- Search and Filter Toolbar -->
      <div class="inventory-toolbar-card">
        <form method="GET" action="index.php" class="inventory-search-form">
          <input type="hidden" name="tab" value="cars">
          
          <div style="flex:1; min-width:240px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search car name, model, stock ID, brand..." class="form-input">
          </div>

          <div style="width:160px;">
            <select name="brand" class="form-select">
              <option value="all">All Brands</option>
              <?php foreach ($all_existing_brands as $b): ?>
                <option value="<?php echo htmlspecialchars($b); ?>" <?php echo ($filter_brand === $b) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="width:170px;">
            <select name="status" class="form-select">
              <option value="ALL">All Active Statuses</option>
              <option value="AVAILABLE" <?php echo ($filter_car_status === 'AVAILABLE') ? 'selected' : ''; ?>>Available</option>
              <option value="RESERVED" <?php echo ($filter_car_status === 'RESERVED') ? 'selected' : ''; ?>>Reserved</option>
            </select>
          </div>

          <!-- Dedicated Search Button -->
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span>Search</span>
          </button>

          <?php if (!empty($search_query) || $filter_brand !== 'all' || $filter_car_status !== 'ALL'): ?>
            <a href="index.php?tab=cars" class="btn btn-outline" style="color:#64748b;">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">
            <?php if (!empty($search_query) || $filter_brand !== 'all' || $filter_car_status !== 'ALL'): ?>
              Search Results (<?php echo count($filtered_cars); ?> vehicle<?php echo count($filtered_cars) === 1 ? '' : 's'; ?> found)
            <?php else: ?>
              All Active Inventory (<?php echo count($filtered_cars); ?> Units)
            <?php endif; ?>
          </h2>
          <small style="color:#64748b; font-weight:600;">Active showroom stock &bull; Sold vehicles are tracked in the Sold tab</small>
        </div>

        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Vehicle & Stock</th>
                <th>Selling Price</th>
                <th>Showroom Pin</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filtered_cars)): ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding: 3rem; color: #64748b;">
                    No active vehicles found matching your search. <a href="index.php?tab=cars" style="color:var(--brand-blue); font-weight:700; text-decoration:underline;">Clear Search Filters</a>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($filtered_cars as $c): 
                  $price_fmt = floatval($c['price']) > 0 ? ('₱' . number_format(floatval($c['price']), 0)) : 'Inquire';
                ?>
                  <tr>
                    <td>
                      <div class="car-table-cell">
                        <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file'] ?? 'car-1.jpg'); ?>" class="car-table-thumb" alt="" onerror="this.src='../assets/images/cars/car-1.jpg'">
                        <div>
                          <div class="car-table-name"><?php echo htmlspecialchars($c['name']); ?></div>
                          <div class="car-table-stock">Stock #<?php echo htmlspecialchars($c['stock_id'] ?: ('UW-' . $c['id'])); ?></div>
                        </div>
                      </div>
                    </td>
                    <td><strong><?php echo $price_fmt; ?></strong></td>
                    <td>
                      <form method="POST" action="index.php?tab=cars" style="display:inline;">
                        <input type="hidden" name="action" value="toggle_recommended">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn-action-sm" title="Pin to top showroom picks">
                          <?php echo !empty($c['is_recommended']) ? '★ Pinned' : '☆ Pin'; ?>
                        </button>
                      </form>
                    </td>
                    <td>
                      <div class="table-actions" style="justify-content: flex-end;">
                        <button type="button" class="btn-action-sm btn-action-view" onclick='openViewCarModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>)' title="View full vehicle specifications">
                          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View Details
                        </button>
                        <button type="button" class="btn-action-sm" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>)' title="Edit vehicle details">
                          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit
                        </button>
                        <form method="POST" action="index.php?tab=cars" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete vehicle #<?php echo $c['id']; ?> (<?php echo htmlspecialchars(addslashes($c['name'])); ?>)?');">
                          <input type="hidden" name="action" value="delete_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-danger" title="Delete vehicle">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete
                          </button>
                        </form>
                        <form method="POST" action="index.php?tab=cars" style="display:inline; margin:0;" onsubmit="return confirm('Mark <?php echo htmlspecialchars(addslashes($c['name'])); ?> as SOLD and transfer to Sold tab?');">
                          <input type="hidden" name="action" value="mark_sold">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-sold" title="Mark vehicle as sold and transfer to Sold tab">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><polyline points="20 6 9 17 4 12"></polyline></svg>Mark Sold
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- ================= TAB 3: SOLD VEHICLES (Items 7, 8, 9) ================= -->
    <?php if ($current_tab === 'sold'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Sold Vehicles Management</h1>
          <p>Archive of completed sales records. In case of customer financing incidents, cancellations, or returns, transfer vehicles back to active inventory.</p>
        </div>
      </div>

      <!-- Toolbar for Sold tab -->
      <div class="inventory-toolbar-card">
        <form method="GET" action="index.php" class="inventory-search-form">
          <input type="hidden" name="tab" value="sold">
          
          <div style="flex:1; min-width:240px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search sold vehicles by car name, model, stock ID, brand..." class="form-input">
          </div>

          <div style="width:160px;">
            <select name="brand" class="form-select">
              <option value="all">All Brands</option>
              <?php foreach ($all_existing_brands as $b): ?>
                <option value="<?php echo htmlspecialchars($b); ?>" <?php echo ($filter_brand === $b) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span>Search</span>
          </button>

          <?php if (!empty($search_query) || $filter_brand !== 'all'): ?>
            <a href="index.php?tab=sold" class="btn btn-outline" style="color:#64748b;">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">
            <?php if (!empty($search_query) || $filter_brand !== 'all'): ?>
              Search Results (<?php echo count($filtered_sold_cars); ?> vehicle<?php echo count($filtered_sold_cars) === 1 ? '' : 's'; ?> found)
            <?php else: ?>
              Sold Vehicles Archive (<?php echo count($filtered_sold_cars); ?> Units)
            <?php endif; ?>
          </h2>
          <small style="color:#64748b; font-weight:600;">Use &ldquo;Transfer to Inventory&rdquo; to restore any sold car back to active showroom stock</small>
        </div>

        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Vehicle & Stock</th>
                <th>Sold Price</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filtered_sold_cars)): ?>
                <tr>
                  <td colspan="4" style="text-align:center; padding: 3.5rem; color: #64748b;">
                    <div style="font-size:1.1rem; font-weight:700; color:#334155; margin-bottom:6px;">No Sold Vehicles Found</div>
                    <p style="margin:0; font-size:0.88rem;">Vehicles marked as SOLD from the inventory tab will appear here.</p>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($filtered_sold_cars as $c): 
                  $price_fmt = floatval($c['price']) > 0 ? ('₱' . number_format(floatval($c['price']), 0)) : 'Inquire';
                ?>
                  <tr>
                    <td>
                      <div class="car-table-cell">
                        <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file'] ?? 'car-1.jpg'); ?>" class="car-table-thumb" alt="" onerror="this.src='../assets/images/cars/car-1.jpg'">
                        <div>
                          <div class="car-table-name"><?php echo htmlspecialchars($c['name']); ?></div>
                          <div class="car-table-stock">Stock #<?php echo htmlspecialchars($c['stock_id'] ?: ('UW-' . $c['id'])); ?></div>
                        </div>
                      </div>
                    </td>
                    <td><strong><?php echo $price_fmt; ?></strong></td>
                    <td>
                      <span class="badge badge-sold">SOLD</span>
                    </td>
                    <td>
                      <div class="table-actions" style="justify-content: flex-end;">
                        <button type="button" class="btn-action-sm btn-action-view" onclick='openViewCarModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>)' title="View full vehicle specifications">
                          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View Details
                        </button>
                        <form method="POST" action="index.php?tab=sold" style="display:inline; margin:0;" onsubmit="return confirm('Transfer vehicle #<?php echo $c['id']; ?> (<?php echo htmlspecialchars(addslashes($c['name'])); ?>) back to active inventory?');">
                          <input type="hidden" name="action" value="transfer_to_inventory">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-transfer" title="Transfer this vehicle back to active inventory in case of incident or cancelled deal">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>Transfer to Inventory
                          </button>
                        </form>
                        <button type="button" class="btn-action-sm" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>)' title="Edit vehicle details">
                          <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit
                        </button>
                        <form method="POST" action="index.php?tab=sold" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to delete vehicle #<?php echo $c['id']; ?>?');">
                          <input type="hidden" name="action" value="delete_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-danger" title="Delete vehicle">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:3px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </main>

  <!-- ================= ADD / EDIT VEHICLE MODAL (Items 5 & 6 Cleaned) ================= -->
  <div id="carModal" class="admin-modal-backdrop" style="display:none;">
    <div class="admin-modal-card">
      <div class="admin-modal-header">
        <h3 id="carModalTitle" class="admin-modal-title">Add Pre-Owned Vehicle</h3>
        <span class="admin-modal-close" onclick="closeCarModal()">&times;</span>
      </div>
      <form id="carForm" method="POST" action="index.php?tab=cars" enctype="multipart/form-data">
        <input type="hidden" name="action" id="carAction" value="create_car">
        <input type="hidden" name="id" id="carId" value="">
        <input type="hidden" name="existing_image_file" id="carExistingImage" value="car-1.jpg">

        <div class="admin-modal-body">
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarName">Year, Make & Model *</label>
              <input type="text" name="name" id="formCarName" class="form-input" placeholder="e.g. 2021 Toyota Vios 1.3 XLE" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCarBrand">Brand / Make *</label>
              <input type="text" name="brand" id="formCarBrand" class="form-input" placeholder="e.g. Toyota" required>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarPrice">Selling Price (₱) *</label>
              <input type="number" step="1000" name="price" id="formCarPrice" class="form-input" placeholder="e.g. 545000" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCarStock">Stock ID / Plate #</label>
              <input type="text" name="stock_id" id="formCarStock" class="form-input" placeholder="e.g. UW-TOY01">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarMileage">Odometer / Mileage *</label>
              <input type="text" name="mileage" id="formCarMileage" class="form-input" placeholder="e.g. 28,400 km" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCarTrans">Transmission *</label>
              <select name="transmission" id="formCarTrans" class="form-select">
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="Automatic CVT">Automatic CVT</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarBody">Body Type *</label>
              <select name="body_type" id="formCarBody" class="form-select">
                <option value="Sedan">Sedan</option>
                <option value="SUV">SUV</option>
                <option value="MPV">7-Seater MPV / Van</option>
                <option value="Pickup">Pickup Truck</option>
                <option value="Hatchback">Hatchback</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCarFuel">Fuel Type *</label>
              <select name="fuel_type" id="formCarFuel" class="form-select">
                <option value="Gasoline">Gasoline</option>
                <option value="Diesel">Diesel</option>
                <option value="Hybrid">Hybrid Electric</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarSeating">Seating Capacity</label>
              <input type="text" name="seating" id="formCarSeating" class="form-input" placeholder="e.g. 5 Seats" value="5 Seats">
            </div>
            <div class="form-group">
              <label class="form-label" for="formCarColor">Exterior Color</label>
              <input type="text" name="finish" id="formCarColor" class="form-input" placeholder="e.g. Silver Metallic" value="Silver Metallic">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="formCarStatus">Vehicle Status *</label>
              <select name="status" id="formCarStatus" class="form-select">
                <option value="AVAILABLE">AVAILABLE (Active Inventory)</option>
                <option value="RESERVED">RESERVED (Active Inventory)</option>
                <option value="SOLD">SOLD (Transfers to Sold Tab)</option>
              </select>
            </div>
            <div class="form-group" style="display:flex; flex-direction:column; justify-content:center;">
              <small style="color:var(--text-muted); font-size:0.78rem; line-height:1.4;">Marking as SOLD automatically transfers this vehicle from active inventory to the Sold tab.</small>
            </div>
          </div>


          <!-- ================= CAR PHOTOS (Items 6 & 8) ================= -->
          <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; padding:16px; margin: 15px 0;">
            <div style="font-size:0.95rem; font-weight:800; color:#0f172a; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between;">
              <span>Car Photos (Main & Additional Gallery)</span>
              <span style="font-size:0.75rem; font-weight:600; color:#475569; background:#e2e8f0; padding:2px 8px; border-radius:4px;">Multi-Photo Enabled</span>
            </div>

            <!-- SECTION 1: MAIN PHOTO (Select existing removed per Item 6) -->
            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-bottom:12px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <label class="form-label" style="margin:0; font-size:0.88rem; font-weight:700; color:#1e293b;">Section 1: Main Photo</label>
                <small style="color:#64748b; font-size:0.75rem;">Primary showcase & catalog cover photo</small>
              </div>
              <div style="display:grid; grid-template-columns: 100px 1fr; gap:14px; align-items:center;">
                <div>
                  <img id="mainPhotoPreview" src="../assets/images/cars/car-1.jpg" alt="Main Photo Preview" style="width:100px; height:66px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1; background:#f1f5f9;">
                </div>
                <div>
                  <label class="form-label" style="font-size:0.8rem; font-weight:600; color:#475569; margin-bottom:4px;">Upload Main Photo (PNG / JPG / WebP):</label>
                  <input type="file" name="car_upload_file" id="carUploadFile" class="form-input" accept="image/*" style="height:38px; font-size:0.82rem; padding:5px 8px;">
                  <small style="color:#64748b; font-size:0.74rem; display:block; margin-top:4px;">Leave blank when editing to keep the current main photo.</small>
                </div>
              </div>
            </div>

            <!-- SECTION 2: ADDITIONAL PHOTOS -->
            <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:14px;">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <label class="form-label" style="margin:0; font-size:0.88rem; font-weight:700; color:#1e293b;">Section 2: Additional Photos (Slideshow & Gallery)</label>
                <small style="color:#64748b; font-size:0.75rem;">Interior, rear, sides & details</small>
              </div>

              <!-- Existing additional photos preview list -->
              <div style="margin-bottom:8px;">
                <span style="font-size:0.78rem; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Current Additional Photos:</span>
                <div id="additionalPhotosPreviewList" style="display:flex; flex-wrap:wrap; gap:8px; min-height:36px; align-items:center;">
                  <span style="font-size:0.8rem; color:#94a3b8;">None</span>
                </div>
              </div>

              <!-- Upload new additional photos (multi-file) -->
              <div>
                <label style="font-size:0.8rem; font-weight:600; color:#475569; display:block; margin-bottom:4px;">Upload Additional Photos (Select 1 or more files):</label>
                <input type="file" name="additional_photos[]" id="additionalPhotosInput" class="form-input" multiple accept="image/*" style="height:38px; font-size:0.82rem; padding:5px 8px;">
                <div id="newAdditionalPhotosPreview" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:6px;"></div>
                <small style="color:#64748b; font-size:0.74rem; display:block; margin-top:4px;">Hold Ctrl/Cmd to select multiple pictures for automatic slideshow & modal browsing.</small>
              </div>
            </div>

          </div>

          <div style="display:flex; gap:20px; margin-top:10px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:0.9rem; cursor:pointer;">
              <input type="checkbox" name="is_recommended" id="formCarRecommended" value="1">
              <span>Pin to Showroom Highlights</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:0.9rem; cursor:pointer;">
              <input type="checkbox" name="is_featured" id="formCarFeatured" value="1">
              <span>Set as Hero Spotlight</span>
            </label>
          </div>
        </div>

        <div class="admin-modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeCarModal()">Cancel</button>
          <button type="submit" class="btn btn-primary" id="carSubmitBtn">Save Vehicle</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= VIEW CAR DETAILS MODAL (Item 4) ================= -->
  <div id="viewCarModal" class="admin-modal-backdrop" style="display:none;">
    <div class="admin-modal-card" style="max-width: 780px;">
      <div class="admin-modal-header">
        <div>
          <div style="display:flex; align-items:center; gap:10px;">
            <h3 id="viewModalTitle" class="admin-modal-title" style="margin:0;">Vehicle Details</h3>
            <span id="viewModalStatusBadge" class="badge">AVAILABLE</span>
          </div>
          <small id="viewModalStock" style="color:var(--text-muted); font-weight:700; font-size:0.82rem; margin-top:2px; display:block;"></small>
        </div>
        <span class="admin-modal-close" onclick="closeViewCarModal()">&times;</span>
      </div>

      <div class="admin-modal-body" style="padding:1.5rem;">
        <!-- Two columns: Photo gallery on left, specs grid on right -->
        <div style="display:grid; grid-template-columns: 290px 1fr; gap:1.5rem; margin-bottom:1.5rem;">
          <div>
            <img id="viewModalImg" src="../assets/images/cars/car-1.jpg" alt="Vehicle Photo" style="width:100%; height:200px; object-fit:cover; border-radius:10px; border:1px solid var(--border-color); background:var(--bg-subtle);">
            
            <div style="margin-top:12px;">
              <span style="font-size:0.75rem; font-weight:800; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; display:block; margin-bottom:6px;">Gallery Photos:</span>
              <div id="viewModalGallery" style="display:flex; flex-wrap:wrap; gap:6px;"></div>
            </div>
          </div>

          <div>
            <div style="background:var(--bg-subtle); border-radius:8px; padding:12px 16px; margin-bottom:14px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <span style="font-size:0.72rem; color:var(--text-muted); font-weight:800; text-transform:uppercase; letter-spacing:0.05em;">Vehicle Price</span>
                <div id="viewModalPrice" style="font-size:1.6rem; font-weight:900; color:var(--brand-blue); line-height:1.2;"></div>
              </div>
              <div id="viewModalPinBadge"></div>
            </div>

            <!-- Specifications Grid -->
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; font-size:0.86rem;">
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Brand / Make</span>
                <strong id="viewModalBrand" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Odometer / Mileage</span>
                <strong id="viewModalMileage" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Transmission</span>
                <strong id="viewModalTrans" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Fuel Type</span>
                <strong id="viewModalFuel" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Body Type</span>
                <strong id="viewModalBody" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Seating</span>
                <strong id="viewModalSeating" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Exterior Color</span>
                <strong id="viewModalFinish" style="color:var(--text-main);"></strong>
              </div>
              <div style="background:#ffffff; border:1px solid var(--border-color); padding:8px 12px; border-radius:6px;">
                <span style="display:block; font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Stock ID</span>
                <strong id="viewModalStockText" style="color:var(--text-main);"></strong>
              </div>
            </div>

          </div>
        </div>

        <!-- Additional Guarantee & History Details -->
        <div style="background:#f8fafc; border:1px solid var(--border-color); border-radius:8px; padding:12px 16px; font-size:0.84rem;">
          <div style="display:flex; flex-wrap:wrap; gap:18px;">
            <div><span style="color:var(--text-muted); font-weight:600;">Wheels/Tires:</span> <strong id="viewModalWheels"></strong></div>
            <div><span style="color:var(--text-muted); font-weight:600;">Inspection:</span> <strong id="viewModalGuarantee"></strong></div>
            <div><span style="color:var(--text-muted); font-weight:600;">Documentation:</span> <strong id="viewModalOwnership"></strong></div>
          </div>
        </div>

      </div>

      <div class="admin-modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeViewCarModal()">Close</button>
        <button type="button" class="btn btn-primary" id="viewModalEditBtn">
          <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle; margin-right:4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit Vehicle
        </button>
      </div>
    </div>
  </div>

  <script>
    // Live preview for uploaded main photo
    const mainFileInput = document.getElementById('carUploadFile');
    if (mainFileInput) {
      mainFileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
          const reader = new FileReader();
          reader.onload = function(evt) {
            document.getElementById('mainPhotoPreview').src = evt.target.result;
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    // Live preview for uploaded additional photos
    const addFileInput = document.getElementById('additionalPhotosInput');
    if (addFileInput) {
      addFileInput.addEventListener('change', function(e) {
        const previewWrap = document.getElementById('newAdditionalPhotosPreview');
        if (!previewWrap) return;
        previewWrap.innerHTML = '';
        if (this.files && this.files.length > 0) {
          Array.from(this.files).forEach((file) => {
            const reader = new FileReader();
            reader.onload = function(evt) {
              const item = document.createElement('div');
              item.style = 'display:inline-flex; align-items:center; gap:4px; background:#e0f2fe; border:1px solid #7dd3fc; border-radius:4px; padding:3px 6px; font-size:0.75rem; color:#0369a1;';
              item.innerHTML = `<img src="${evt.target.result}" style="width:28px; height:20px; object-fit:cover; border-radius:2px;"> <span>+New (${file.name})</span>`;
              previewWrap.appendChild(item);
            };
            reader.readAsDataURL(file);
          });
        }
      });
    }

    function openCreateModal() {
      document.getElementById('carModalTitle').textContent = 'Add Pre-Owned Vehicle';
      document.getElementById('carAction').value = 'create_car';
      document.getElementById('carId').value = '';
      document.getElementById('carForm').reset();
      document.getElementById('formCarStatus').value = 'AVAILABLE';
      
      // Reset photo sections
      document.getElementById('mainPhotoPreview').src = '../assets/images/cars/car-1.jpg';
      document.getElementById('additionalPhotosPreviewList').innerHTML = '<span style="font-size:0.8rem; color:#94a3b8;">None yet. Select files below.</span>';
      const newPrev = document.getElementById('newAdditionalPhotosPreview');
      if (newPrev) newPrev.innerHTML = '';

      document.getElementById('carModal').style.display = 'flex';
    }

    function openEditModal(car) {
      document.getElementById('carModalTitle').textContent = 'Edit Vehicle: ' + car.name;
      document.getElementById('carAction').value = 'update_car';
      document.getElementById('carId').value = car.id;
      document.getElementById('carExistingImage').value = car.image_file || 'car-1.jpg';
      
      document.getElementById('formCarName').value = car.name || '';
      document.getElementById('formCarBrand').value = car.brand || '';
      document.getElementById('formCarPrice').value = car.price || '';
      document.getElementById('formCarStock').value = car.stock_id || '';
      document.getElementById('formCarMileage').value = car.mileage || '';
      document.getElementById('formCarTrans').value = car.transmission || 'Automatic';
      document.getElementById('formCarBody').value = car.body_type || 'Sedan';
      document.getElementById('formCarFuel').value = car.fuel_type || 'Gasoline';
      document.getElementById('formCarSeating').value = car.seating || '5 Seats';
      document.getElementById('formCarColor').value = car.finish || '';
      document.getElementById('formCarStatus').value = (car.status || 'AVAILABLE').toUpperCase();
      document.getElementById('formCarRecommended').checked = car.is_recommended == 1;
      document.getElementById('formCarFeatured').checked = car.is_featured == 1;

      // Section 1: Main Photo Preview
      document.getElementById('mainPhotoPreview').src = '../assets/images/cars/' + (car.image_file || 'car-1.jpg');

      // Section 2: Additional Photos Previews & Kept selection
      const galleryWrap = document.getElementById('additionalPhotosPreviewList');
      galleryWrap.innerHTML = '';
      const newPrev = document.getElementById('newAdditionalPhotosPreview');
      if (newPrev) newPrev.innerHTML = '';

      let galleryImages = [];
      if (car.gallery_images) {
        try {
          galleryImages = typeof car.gallery_images === 'string' ? JSON.parse(car.gallery_images) : car.gallery_images;
        } catch (e) {
          galleryImages = [];
        }
      }

      if (Array.isArray(galleryImages) && galleryImages.length > 0) {
        galleryImages.forEach(img => {
          const item = document.createElement('label');
          item.style = 'display:inline-flex; align-items:center; gap:6px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:6px; padding:4px 8px; font-size:0.8rem; cursor:pointer;';
          item.title = 'Uncheck to delete from vehicle gallery';
          item.innerHTML = `
            <input type="checkbox" name="kept_gallery_images[]" value="${img}" checked>
            <img src="../assets/images/cars/${img}" style="width:36px; height:24px; object-fit:cover; border-radius:3px;" onerror="this.src='../assets/images/cars/car-1.jpg'">
            <span>${img}</span>
          `;
          galleryWrap.appendChild(item);
        });
      } else {
        galleryWrap.innerHTML = '<span style="font-size:0.8rem; color:#94a3b8;">No additional photos currently attached.</span>';
      }

      document.getElementById('carModal').style.display = 'flex';
    }

    function closeCarModal() {
      document.getElementById('carModal').style.display = 'none';
    }

    // View Car Details Modal (Item 4)
    let currentViewingCar = null;

    function openViewCarModal(car) {
      currentViewingCar = car;
      document.getElementById('viewModalTitle').textContent = car.name || 'Vehicle Details';
      
      const st = (car.status || 'AVAILABLE').toUpperCase();
      const stBadge = document.getElementById('viewModalStatusBadge');
      stBadge.textContent = st;
      stBadge.className = 'badge badge-' + st.toLowerCase();
      
      const stockStr = car.stock_id || ('UW-' + car.id);
      document.getElementById('viewModalStock').textContent = 'Stock #' + stockStr;
      document.getElementById('viewModalStockText').textContent = stockStr;
      
      const priceVal = parseFloat(car.price || 0);
      document.getElementById('viewModalPrice').textContent = priceVal > 0 ? ('₱' + priceVal.toLocaleString()) : 'Inquire';
      
      document.getElementById('viewModalBrand').textContent = car.brand || '-';
      document.getElementById('viewModalMileage').textContent = car.mileage || 'Low Mileage';
      document.getElementById('viewModalTrans').textContent = car.transmission || 'Automatic';
      document.getElementById('viewModalFuel').textContent = car.fuel_type || 'Gasoline';
      document.getElementById('viewModalBody').textContent = car.body_type || 'Sedan';
      document.getElementById('viewModalSeating').textContent = car.seating || '5 Seats';
      document.getElementById('viewModalFinish').textContent = car.finish || 'Original Paint';
      document.getElementById('viewModalWheels').textContent = car.wheel_name || 'OEM Factory Alloy Wheels';
      document.getElementById('viewModalGuarantee').textContent = car.alloy_type || '100% Flood-Free Guaranteed';
      document.getElementById('viewModalOwnership').textContent = car.rear_fitment || 'Complete Original Papers';

      const pinBadge = document.getElementById('viewModalPinBadge');
      if (car.is_recommended == 1) {
        pinBadge.innerHTML = '<span class="badge" style="background:#fef3c7; color:#b45309; padding:4px 8px;">★ Showroom Pinned</span>';
      } else {
        pinBadge.innerHTML = '';
      }

      // Main image preview
      const imgFile = car.image_file || 'car-1.jpg';
      const mainImg = document.getElementById('viewModalImg');
      mainImg.src = '../assets/images/cars/' + imgFile;
      mainImg.onerror = function() { this.src = '../assets/images/cars/car-1.jpg'; };

      // Additional Gallery Photos
      const galWrap = document.getElementById('viewModalGallery');
      galWrap.innerHTML = '';
      let gallery = [];
      if (car.gallery_images) {
        try {
          gallery = typeof car.gallery_images === 'string' ? JSON.parse(car.gallery_images) : car.gallery_images;
        } catch(e) { gallery = []; }
      }
      if (Array.isArray(gallery) && gallery.length > 0) {
        gallery.forEach(g => {
          const gImg = document.createElement('img');
          gImg.src = '../assets/images/cars/' + g;
          gImg.style = 'width:56px; height:38px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1; cursor:pointer; background:#f1f5f9;';
          gImg.title = 'Click to preview';
          gImg.onerror = function() { this.style.display = 'none'; };
          gImg.onclick = function() {
            mainImg.src = this.src;
          };
          galWrap.appendChild(gImg);
        });
      } else {
        galWrap.innerHTML = '<span style="font-size:0.75rem; color:#94a3b8;">No additional photos</span>';
      }

      document.getElementById('viewModalEditBtn').onclick = function() {
        closeViewCarModal();
        openEditModal(car);
      };

      document.getElementById('viewCarModal').style.display = 'flex';
    }

    function closeViewCarModal() {
      document.getElementById('viewCarModal').style.display = 'none';
    }

    // Modal backdrop click-to-close
    window.addEventListener('click', function(e) {
      const viewModal = document.getElementById('viewCarModal');
      if (e.target === viewModal) {
        closeViewCarModal();
      }
      const carModal = document.getElementById('carModal');
      if (e.target === carModal) {
        closeCarModal();
      }
    });
  </script>

</body>
</html>
