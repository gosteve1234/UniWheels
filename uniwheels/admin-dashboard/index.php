<?php
/**
 * UniWheels Executive Management Dashboard
 * Left-Sidebar Architecture with Fleet CRUD, Image Uploads, Employees, and Rules & Roles
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Gate: Require logged-in session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Current logged in user details
$current_user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'] ?? 'admin',
    'full_name' => $_SESSION['full_name'] ?? 'Super Administrator',
    'role' => $_SESSION['role'] ?? 'admin',
    'email' => $_SESSION['email'] ?? 'admin@uniwheels.com'
];

// Database connection
$suppress_db_die = true;
require_once __DIR__ . '/../connection.php';

// Target folder for transparent car cutouts
$cars_img_dir = __DIR__ . '/../assets/images/cars';
if (!is_dir($cars_img_dir)) {
    @mkdir($cars_img_dir, 0777, true);
}

// Helper: Get list of existing car images
if (!function_exists('getAvailableCarImages')) {
    function getAvailableCarImages($dir) {
        $images = [];
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $f) {
                if (preg_match('/\.(png|jpg|jpeg|webp|avif)$/i', $f)) {
                    $images[] = $f;
                }
            }
        }
        sort($images);
        return $images;
    }
}

$available_images = getAvailableCarImages($cars_img_dir);

// Helper: Handle direct image upload
if (!function_exists('handleCarImageUpload')) {
    function handleCarImageUpload($file_input, $cars_img_dir, $fallback_image = 'car-1.png') {
        if (isset($_FILES[$file_input]) && $_FILES[$file_input]['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES[$file_input]['tmp_name'];
            $orig_name = $_FILES[$file_input]['name'];
            $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
            $allowed = ['png', 'jpg', 'jpeg', 'webp', 'avif'];
            
            if (in_array($ext, $allowed)) {
                // Clean filename and make unique
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

// Handle POST actions
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$current_tab = $_GET['tab'] ?? 'dashboard';

if ($pdo && $request_method === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================= CAR CRUD ACTIONS =================
    if ($action === 'create_car') {
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? 'UniWheels');
        $price = floatval($_POST['price'] ?? 0);
        $stock_id = trim($_POST['stock_id'] ?? '');
        if (empty($stock_id) && $name) {
            $stock_id = 'UW-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 4)) . rand(10, 99);
        }
        $mileage = trim($_POST['mileage'] ?? 'Delivery Miles');
        $transmission = trim($_POST['transmission'] ?? 'Dual-Clutch DCT');
        $body_type = trim($_POST['body_type'] ?? 'Coupe');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasoline');
        $seating = trim($_POST['seating'] ?? '2 Seats');
        $wheel_name = trim($_POST['wheel_name'] ?? '');
        $status = trim($_POST['status'] ?? 'AVAILABLE');
        if (!in_array($status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            $status = 'AVAILABLE';
        }
        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $front_fitment = trim($_POST['front_fitment'] ?? '');
        $rear_fitment = trim($_POST['rear_fitment'] ?? '');
        $finish = trim($_POST['finish'] ?? '');
        $alloy_type = trim($_POST['alloy_type'] ?? '');
        $weight = trim($_POST['weight'] ?? '');
        $sold_at = ($status === 'SOLD') ? date('Y-m-d H:i:s') : null;
        
        // Handle image selection or upload
        $selected_existing = trim($_POST['image_file'] ?? 'car-1.png');
        $image_file = handleCarImageUpload('car_upload_file', $cars_img_dir, $selected_existing);

        if ($name && $wheel_name) {
            $stmt = $pdo->prepare("INSERT INTO cars (name, brand, price, stock_id, mileage, transmission, body_type, fuel_type, seating, wheel_name, status, is_recommended, is_featured, front_fitment, rear_fitment, finish, alloy_type, weight, image_file, sold_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $brand, $price, $stock_id, $mileage, $transmission, $body_type, $fuel_type, $seating, $wheel_name, $status, $is_recommended, $is_featured, $front_fitment, $rear_fitment, $finish, $alloy_type, $weight, $image_file, $sold_at]);
            $_SESSION['flash'] = "Vehicle '{$name}' ({$brand}) created successfully with image '{$image_file}'!";
            header("Location: index.php?tab=" . ($status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }
    }

    if ($action === 'update_car') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $brand = trim($_POST['brand'] ?? 'UniWheels');
        $price = floatval($_POST['price'] ?? 0);
        $stock_id = trim($_POST['stock_id'] ?? '');
        $mileage = trim($_POST['mileage'] ?? 'Delivery Miles');
        $transmission = trim($_POST['transmission'] ?? 'Dual-Clutch DCT');
        $body_type = trim($_POST['body_type'] ?? 'Coupe');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasoline');
        $seating = trim($_POST['seating'] ?? '2 Seats');
        $wheel_name = trim($_POST['wheel_name'] ?? '');
        $status = trim($_POST['status'] ?? 'AVAILABLE');
        if (!in_array($status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            $status = 'AVAILABLE';
        }
        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $front_fitment = trim($_POST['front_fitment'] ?? '');
        $rear_fitment = trim($_POST['rear_fitment'] ?? '');
        $finish = trim($_POST['finish'] ?? '');
        $alloy_type = trim($_POST['alloy_type'] ?? '');
        $weight = trim($_POST['weight'] ?? '');
        
        // Retain existing image if no new file is uploaded
        $current_image = trim($_POST['existing_image_file'] ?? 'car-1.png');
        $selected_image = trim($_POST['image_file'] ?? $current_image);
        $image_file = handleCarImageUpload('car_upload_file', $cars_img_dir, $selected_image);

        if ($id && $name) {
            if ($status === 'SOLD') {
                $stmt = $pdo->prepare("UPDATE cars SET name=?, brand=?, price=?, stock_id=?, mileage=?, transmission=?, body_type=?, fuel_type=?, seating=?, wheel_name=?, status=?, is_recommended=?, is_featured=?, front_fitment=?, rear_fitment=?, finish=?, alloy_type=?, weight=?, image_file=?, sold_at=IFNULL(sold_at, NOW()) WHERE id=?");
            } else {
                $stmt = $pdo->prepare("UPDATE cars SET name=?, brand=?, price=?, stock_id=?, mileage=?, transmission=?, body_type=?, fuel_type=?, seating=?, wheel_name=?, status=?, is_recommended=?, is_featured=?, front_fitment=?, rear_fitment=?, finish=?, alloy_type=?, weight=?, image_file=?, sold_at=NULL WHERE id=?");
            }
            $stmt->execute([$name, $brand, $price, $stock_id, $mileage, $transmission, $body_type, $fuel_type, $seating, $wheel_name, $status, $is_recommended, $is_featured, $front_fitment, $rear_fitment, $finish, $alloy_type, $weight, $image_file, $id]);
            $_SESSION['flash'] = "Vehicle #{$id} ({$name}) updated successfully!";
            header("Location: index.php?tab=" . ($status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }
    }

    if ($action === 'delete_car') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id=?");
            $stmt->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} deleted from fleet catalog.";
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }

    if ($action === 'toggle_recommended') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE cars SET is_recommended = 1 - is_recommended WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = "Recommended status updated for vehicle #{$id}.";
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }

    if ($action === 'toggle_featured') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE cars SET is_featured = 1 - is_featured WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = "Featured highlight status updated for vehicle #{$id}.";
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }

    if ($action === 'set_car_status') {
        $id = intval($_POST['id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? 'AVAILABLE');
        if ($id && in_array($new_status, ['AVAILABLE', 'RESERVED', 'SOLD'])) {
            if ($new_status === 'SOLD') {
                $pdo->prepare("UPDATE cars SET status = 'SOLD', sold_at = IFNULL(sold_at, NOW()) WHERE id = ?")->execute([$id]);
                $_SESSION['flash'] = "Vehicle #{$id} marked as SOLD and transferred to Sold Cars archive.";
            } else {
                $pdo->prepare("UPDATE cars SET status = ?, sold_at = NULL WHERE id = ?")->execute([$new_status, $id]);
                $_SESSION['flash'] = "Vehicle #{$id} status set to {$new_status}.";
            }
            header("Location: index.php?tab=" . urlencode($current_tab));
            exit;
        }
    }

    if ($action === 'retract_sold_car') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', sold_at = NULL WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} successfully retracted to the Active Fleet (status set to AVAILABLE).";
            header("Location: index.php?tab=cars");
            exit;
        }
    }

    // ================= EMPLOYEE ACTIONS =================
    if ($action === 'create_employee') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $name = trim($_POST['full_name'] ?? $_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'employee');

        if ($username && $password && $name) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            try {
                $stmt = $pdo->prepare("INSERT INTO employees (username, password, name, email, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$username, $hashed, $name, $email, $role]);
                $_SESSION['flash'] = "Staff account '{$username}' ({$name}) registered successfully!";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Failed to register staff: " . $e->getMessage();
            }
            header("Location: index.php?tab=employees");
            exit;
        }
    }

    if ($action === 'delete_employee') {
        $user_id_to_del = intval($_POST['user_id'] ?? 0);
        if ($user_id_to_del && $user_id_to_del !== intval($_SESSION['user_id'])) {
            $pdo->prepare("DELETE FROM employees WHERE id = ?")->execute([$user_id_to_del]);
            $_SESSION['flash'] = "Staff member #{$user_id_to_del} removed.";
        } else {
            $_SESSION['flash_error'] = "You cannot delete your own active administrator account!";
        }
        header("Location: index.php?tab=employees");
        exit;
    }



}

// Read flash notices
$flash = $_SESSION['flash'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_error']);

// Refresh images list after potential upload
$available_images = getAvailableCarImages($cars_img_dir);

// Fetch fleet cars (Active Fleet vs Sold Archive)
$active_cars = [];
$sold_cars = [];
$stats = ['total' => 0, 'available' => 0, 'reserved' => 0, 'sold' => 0, 'recommended' => 0, 'featured' => 0];
$all_existing_brands = [];

if ($pdo) {
    // Active Fleet: Excludes SOLD
    $stmt_active = $pdo->query("SELECT * FROM cars WHERE status != 'SOLD' ORDER BY id ASC");
    $active_cars = $stmt_active->fetchAll();

    // Sold Cars Archive: Only SOLD, sorted by sold_at DESC, id DESC
    $stmt_sold = $pdo->query("SELECT * FROM cars WHERE status = 'SOLD' ORDER BY sold_at DESC, id DESC");
    $sold_cars = $stmt_sold->fetchAll();

    $stats['total'] = count($active_cars);
    $stats['sold'] = count($sold_cars);

    foreach ($active_cars as $c) {
        $st = strtoupper($c['status']);
        if ($st === 'AVAILABLE') $stats['available']++;
        if ($st === 'RESERVED') $stats['reserved']++;
        if (!empty($c['is_recommended'])) $stats['recommended']++;
        if (!empty($c['is_featured'])) $stats['featured']++;
    }

    // Keep $cars mapped to active fleet for backward compatibility
    $cars = $active_cars;

    try {
        $stmt_brands = $pdo->query("SELECT DISTINCT brand FROM cars WHERE brand IS NOT NULL AND brand != '' ORDER BY brand ASC");
        $all_existing_brands = $stmt_brands->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {}
}
if (empty($all_existing_brands)) {
    $all_existing_brands = ['Ferrari', 'Apollo', 'Nissan', 'Porsche', 'Lamborghini', 'Chevrolet', 'BMW', 'McLaren'];
}

// Fetch employees for staff directory
$users_list = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT id, username, name, email, role, status, created_at FROM employees ORDER BY id ASC");
    $users_list = $stmt->fetchAll();
}
$employee_count = count($users_list);

// Fetch roles from roles table (retained for employee assignment options)
$roles_list = [];
if ($pdo) {
    try {
        $roles_list = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
    } catch (Exception $e) {}
}

// Active Tab validation (Showroom dealership: inquiries and customer tabs removed)
$allowed_tabs = ['dashboard', 'cars', 'sold', 'employees'];
if (!in_array($current_tab, $allowed_tabs)) {
    $current_tab = 'dashboard';
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniWheels Admin Suite — Executive Dashboard</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --bg-black: #040406;
      --bg-sidebar: #07070b;
      --bg-panel: #0d0d14;
      --bg-card: rgba(16, 16, 24, 0.7);
      --bg-card-hover: rgba(24, 24, 36, 0.9);
      --uni-white: #ffffff;
      --wheels-crimson: #c4152e;
      --wheels-dark-red: #8b0014;
      --wheels-neon: #ff2b47;
      --text-main: #f5f5f7;
      --text-muted: #6e6e84;
      --text-sub: #a2a2b8;
      --border-subtle: rgba(255, 255, 255, 0.08);
      --border-red: rgba(196, 21, 46, 0.35);
      --border-red-active: rgba(255, 43, 71, 0.8);
      --font-heading: 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
      --sidebar-width: 270px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: var(--font-body);
      background-color: var(--bg-black);
      color: var(--text-main);
      display: flex;
      min-height: 100vh;
      background-image: 
        radial-gradient(circle at 15% 10%, rgba(139, 0, 20, 0.18) 0%, transparent 45%),
        radial-gradient(circle at 85% 90%, rgba(196, 21, 46, 0.12) 0%, transparent 40%);
      background-attachment: fixed;
    }

    a { color: inherit; text-decoration: none; }

    /* ================= SIDEBAR ARCHITECTURE ================= */
    .admin-sidebar {
      width: var(--sidebar-width);
      min-width: var(--sidebar-width);
      background: var(--bg-sidebar);
      border-right: 1px solid var(--border-subtle);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 1000;
      backdrop-filter: blur(20px);
    }

    .sidebar-brand {
      padding: 1.75rem 1.5rem;
      display: flex;
      align-items: center;
      gap: 12px;
      border-bottom: 1px solid var(--border-subtle);
    }

    .brand-logo-img {
      height: 42px;
      width: auto;
      filter: drop-shadow(0 0 12px rgba(196, 21, 46, 0.6));
    }

    .brand-title {
      font-family: var(--font-heading);
      font-size: 1.25rem;
      font-weight: 800;
      letter-spacing: -0.01em;
      line-height: 1.1;
    }

    .brand-sub {
      font-size: 0.62rem;
      font-weight: 700;
      color: var(--wheels-neon);
      letter-spacing: 0.18em;
      text-transform: uppercase;
      font-family: var(--font-mono);
    }

    .sidebar-nav {
      flex: 1;
      padding: 1.5rem 1rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
    }

    .nav-section-title {
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--text-muted);
      padding: 0.75rem 0.75rem 0.35rem;
      font-family: var(--font-mono);
    }

    .nav-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 14px;
      border-radius: 10px;
      color: var(--text-sub);
      font-size: 0.88rem;
      font-weight: 500;
      transition: all 0.2s ease;
      position: relative;
    }

    .nav-item:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.04);
    }

    .nav-item.active {
      color: #fff;
      background: linear-gradient(90deg, rgba(196, 21, 46, 0.25) 0%, rgba(196, 21, 46, 0.08) 100%);
      border: 1px solid var(--border-red);
      font-weight: 600;
    }

    .nav-item.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 20%;
      bottom: 20%;
      width: 3px;
      background: var(--wheels-neon);
      border-radius: 0 4px 4px 0;
      box-shadow: 0 0 10px var(--wheels-neon);
    }

    .nav-icon {
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--text-muted);
      transition: color 0.2s ease;
      flex-shrink: 0;
    }

    .nav-item.active .nav-icon,
    .nav-item:hover .nav-icon {
      color: var(--wheels-neon);
    }

    .nav-badge {
      margin-left: auto;
      font-family: var(--font-mono);
      font-size: 0.68rem;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.06);
      color: var(--text-sub);
    }

    .nav-item.active .nav-badge {
      background: var(--wheels-crimson);
      color: #fff;
    }

    /* Sidebar Footer Profile Card */
    .sidebar-footer {
      padding: 1.25rem 1rem;
      border-top: 1px solid var(--border-subtle);
      background: rgba(4, 4, 6, 0.5);
    }

    .user-profile-card {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 8px 10px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border-subtle);
      transition: border-color 0.2s ease;
    }

    .user-profile-card:hover {
      border-color: var(--border-red);
    }

    /* Default Empty Person Silhouette Avatar */
    .user-avatar-default {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(18, 18, 26, 0.9);
      border: 1px solid var(--border-red);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      box-shadow: 0 0 10px rgba(196, 21, 46, 0.3);
    }

    .user-avatar-default svg {
      width: 22px;
      height: 22px;
      color: var(--text-sub);
    }

    .user-info {
      flex: 1;
      min-width: 0;
    }

    .user-name {
      font-size: 0.85rem;
      font-weight: 700;
      color: #fff;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .user-role-badge {
      display: inline-block;
      font-family: var(--font-mono);
      font-size: 0.6rem;
      font-weight: 700;
      color: var(--wheels-neon);
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .btn-logout {
      color: var(--text-muted);
      padding: 6px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }

    .btn-logout:hover {
      color: var(--wheels-neon);
      background: rgba(196, 21, 46, 0.15);
    }

    /* ================= MAIN CONTENT AREA ================= */
    .admin-main {
      margin-left: var(--sidebar-width);
      flex: 1;
      padding: 2.25rem 2.75rem 5rem;
      min-width: 0;
    }

    .top-action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .page-title-box h1 {
      font-family: var(--font-heading);
      font-size: 1.85rem;
      font-weight: 800;
      letter-spacing: -0.02em;
    }

    .page-title-box p {
      color: var(--text-sub);
      font-size: 0.88rem;
      margin-top: 3px;
    }

    .top-actions {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      border-radius: 10px;
      font-family: var(--font-heading);
      font-size: 0.85rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.25s ease;
      border: none;
      outline: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--wheels-crimson) 0%, var(--wheels-dark-red) 100%);
      color: #fff;
      border: 1px solid var(--wheels-neon);
      box-shadow: 0 4px 18px rgba(196, 21, 46, 0.45);
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, var(--wheels-neon) 0%, var(--wheels-crimson) 100%);
      box-shadow: 0 6px 24px rgba(196, 21, 46, 0.7);
      transform: translateY(-1px);
    }

    .btn-outline {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-subtle);
      color: var(--text-main);
    }

    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: rgba(255, 255, 255, 0.2);
    }

    /* Flash Message */
    .flash-alert {
      background: rgba(196, 21, 46, 0.12);
      border: 1px solid var(--border-red);
      color: #fff;
      padding: 12px 18px;
      border-radius: 12px;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    .flash-alert.error {
      background: rgba(255, 43, 71, 0.2);
      border-color: rgba(255, 43, 71, 0.6);
      color: #ff8595;
    }

    /* ================= STATS WIDGETS ================= */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.25rem;
      margin-bottom: 2.25rem;
    }

    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 16px;
      padding: 1.5rem;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .stat-card:hover {
      border-color: var(--border-red);
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    .stat-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--wheels-crimson), transparent);
    }

    .stat-label {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .stat-value {
      font-family: var(--font-heading);
      font-size: 2.2rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }

    .stat-meta {
      font-size: 0.75rem;
      color: var(--text-sub);
      margin-top: 8px;
    }

    /* ================= TABLE & LIST CARDS ================= */
    .card-panel {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.6);
      margin-bottom: 2.5rem;
    }

    .card-panel-header {
      padding: 1.25rem 1.75rem;
      border-bottom: 1px solid var(--border-subtle);
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: rgba(10, 10, 16, 0.5);
    }

    .card-panel-title {
      font-family: var(--font-heading);
      font-size: 1.15rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .data-table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 0.88rem;
    }

    .data-table th {
      background: rgba(6, 6, 10, 0.7);
      padding: 14px 18px;
      font-family: var(--font-heading);
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--text-muted);
      letter-spacing: 0.1em;
      text-transform: uppercase;
      border-bottom: 1px solid var(--border-subtle);
      white-space: nowrap;
    }

    .data-table td {
      padding: 16px 18px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.04);
      vertical-align: middle;
    }

    .data-table tr:hover td {
      background: rgba(255, 255, 255, 0.02);
    }

    /* Vehicle preview in table */
    .car-cell-flex {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .car-thumb-box {
      width: 68px;
      height: 44px;
      border-radius: 8px;
      background: rgba(0, 0, 0, 0.6);
      border: 1px solid var(--border-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .car-thumb-img {
      max-width: 90%;
      max-height: 90%;
      object-fit: contain;
      filter: drop-shadow(0 2px 6px rgba(0,0,0,0.8));
    }

    .car-name-title {
      font-weight: 700;
      color: #fff;
      font-size: 0.92rem;
    }

    .car-wheel-subtitle {
      font-size: 0.75rem;
      color: var(--text-sub);
      font-family: var(--font-mono);
    }

    /* Status Pills */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 4px 10px;
      border-radius: 6px;
      font-family: var(--font-mono);
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.05em;
      cursor: pointer;
      transition: all 0.2s ease;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-subtle);
      color: var(--text-sub);
    }

    .status-badge.available {
      background: rgba(46, 213, 115, 0.12);
      border-color: rgba(46, 213, 115, 0.45);
      color: #2ed573;
    }

    .status-badge.reserved {
      background: rgba(255, 171, 0, 0.12);
      border-color: rgba(255, 171, 0, 0.45);
      color: #ffab00;
    }

    .status-badge.sold {
      background: rgba(255, 71, 87, 0.12);
      border-color: rgba(255, 71, 87, 0.45);
      color: #ff4757;
    }

    .admin-status-select {
      background: #0f1017;
      border: 1px solid var(--border-subtle);
      border-radius: 6px;
      padding: 6px 10px;
      font-family: var(--font-mono);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.03em;
      cursor: pointer;
      outline: none;
      transition: all 0.2s ease;
    }
    .admin-status-select.status-available {
      color: #2ed573;
      border-color: rgba(46, 213, 115, 0.4);
      background: rgba(46, 213, 115, 0.08);
    }
    .admin-status-select.status-reserved {
      color: #ffab00;
      border-color: rgba(255, 171, 0, 0.4);
      background: rgba(255, 171, 0, 0.08);
    }
    .admin-status-select.status-sold {
      color: #ff4757;
      border-color: rgba(255, 71, 87, 0.4);
      background: rgba(255, 71, 87, 0.08);
    }
    .admin-status-select option {
      background: #0d0d14;
      color: #fff;
    }

    .fleet-subtabs {
      display: flex;
      gap: 10px;
      margin-bottom: 1.25rem;
    }
    .fleet-subtab-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      border-radius: 8px;
      font-family: var(--font-heading);
      font-size: 0.85rem;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s ease;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border-subtle);
      color: var(--text-sub);
    }
    .fleet-subtab-btn:hover {
      background: rgba(255, 255, 255, 0.06);
      color: #fff;
    }
    .fleet-subtab-btn.active {
      background: rgba(196, 21, 46, 0.15);
      border-color: var(--wheels-crimson);
      color: #fff;
      box-shadow: 0 0 15px rgba(196, 21, 46, 0.2);
    }



    .toggle-chip {
      background: transparent;
      border: 1px solid var(--border-subtle);
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 0.72rem;
      font-weight: 600;
      cursor: pointer;
      color: var(--text-muted);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .toggle-chip.active-rec {
      border-color: rgba(255, 193, 7, 0.6);
      background: rgba(255, 193, 7, 0.15);
      color: #ffc107;
    }

    .toggle-chip.active-feat {
      border-color: var(--border-red-active);
      background: rgba(196, 21, 46, 0.2);
      color: var(--wheels-neon);
    }

    .table-actions {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .btn-table-action {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid var(--border-subtle);
      color: var(--text-sub);
      padding: 6px 10px;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-table-action:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
    }

    .btn-table-action.delete:hover {
      background: rgba(196, 21, 46, 0.2);
      border-color: var(--border-red);
      color: #ff556b;
    }

    /* ================= MODAL DIALOGS ================= */
    .modal-overlay {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.85);
      backdrop-filter: blur(10px);
      z-index: 2000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .modal-overlay.active {
      display: flex;
    }

    .modal-dialog {
      background: #0b0b12;
      border: 1px solid var(--border-red);
      border-radius: 20px;
      width: 100%;
      max-width: 720px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.9), 0 0 35px rgba(196, 21, 46, 0.35);
      position: relative;
    }

    .modal-header {
      padding: 1.5rem 1.75rem;
      border-bottom: 1px solid var(--border-subtle);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-title {
      font-family: var(--font-heading);
      font-size: 1.35rem;
      font-weight: 800;
    }

    .modal-close {
      background: none;
      border: none;
      color: var(--text-muted);
      font-size: 1.5rem;
      cursor: pointer;
      padding: 4px;
      line-height: 1;
      transition: color 0.2s ease;
    }

    .modal-close:hover {
      color: #fff;
    }

    .modal-body {
      padding: 1.75rem;
    }

    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.25rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      font-family: var(--font-heading);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--text-sub);
      margin-bottom: 6px;
    }

    .form-input, .form-select {
      width: 100%;
      background: rgba(18, 18, 28, 0.9);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 11px 14px;
      color: #fff;
      font-family: var(--font-body);
      font-size: 0.88rem;
      transition: all 0.25s ease;
    }

    .form-input:focus, .form-select:focus {
      outline: none;
      border-color: var(--wheels-neon);
      background: rgba(24, 24, 38, 0.95);
      box-shadow: 0 0 15px rgba(196, 21, 46, 0.35);
    }

    /* File Upload Box */
    .upload-dropzone {
      border: 2px dashed rgba(196, 21, 46, 0.4);
      border-radius: 12px;
      padding: 1.25rem;
      text-align: center;
      background: rgba(196, 21, 46, 0.03);
      cursor: pointer;
      transition: all 0.2s ease;
      position: relative;
    }

    .upload-dropzone:hover {
      border-color: var(--wheels-neon);
      background: rgba(196, 21, 46, 0.08);
    }

    .upload-dropzone input[type="file"] {
      position: absolute;
      top: 0; left: 0; width: 100%; height: 100%;
      opacity: 0;
      cursor: pointer;
    }

    .upload-preview-container {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-top: 10px;
      padding: 8px 12px;
      background: rgba(0, 0, 0, 0.4);
      border-radius: 8px;
    }

    .upload-preview-img {
      height: 48px;
      width: auto;
      object-fit: contain;
    }

    .checkbox-group {
      display: flex;
      gap: 1.5rem;
      margin-top: 0.5rem;
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.85rem;
      color: var(--text-main);
      cursor: pointer;
    }

    .modal-footer {
      padding: 1.25rem 1.75rem;
      border-top: 1px solid var(--border-subtle);
      display: flex;
      justify-content: flex-end;
      gap: 0.75rem;
      background: rgba(6, 6, 10, 0.4);
    }

    /* ================= EMPLOYEES EMPTY STATE & TAB ================= */
    .empty-state-box {
      text-align: center;
      padding: 4rem 2rem;
      background: rgba(12, 12, 18, 0.5);
      border: 1px dashed var(--border-subtle);
      border-radius: 18px;
      margin: 1.5rem 0;
    }

    .empty-state-icon {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid var(--border-red);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
      box-shadow: 0 0 20px rgba(196, 21, 46, 0.25);
    }

    .empty-state-icon svg {
      width: 36px;
      height: 36px;
      color: var(--text-sub);
    }

    .empty-state-title {
      font-family: var(--font-heading);
      font-size: 1.3rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 0.5rem;
    }

    .empty-state-desc {
      color: var(--text-muted);
      font-size: 0.88rem;
      max-width: 480px;
      margin: 0 auto 1.5rem;
      line-height: 1.5;
    }

    /* ================= RULES & ROLES TAB ================= */
    .roles-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2.5rem;
    }

    .role-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 18px;
      padding: 1.75rem;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .role-card:hover {
      border-color: var(--border-red);
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.7);
    }

    .role-card.highlight {
      border-color: var(--border-red-active);
      background: linear-gradient(180deg, rgba(24, 16, 22, 0.9) 0%, rgba(12, 12, 18, 0.8) 100%);
    }

    .role-badge-pill {
      display: inline-block;
      font-family: var(--font-mono);
      font-size: 0.65rem;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 4px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      margin-bottom: 1rem;
    }

    .role-badge-pill.admin {
      background: rgba(196, 21, 46, 0.2);
      border: 1px solid var(--border-red);
      color: var(--wheels-neon);
    }

    .role-badge-pill.manager {
      background: rgba(255, 171, 0, 0.15);
      border: 1px solid rgba(255, 171, 0, 0.4);
      color: #ffab00;
    }

    .role-badge-pill.employee {
      background: rgba(46, 213, 115, 0.15);
      border: 1px solid rgba(46, 213, 115, 0.4);
      color: #2ed573;
    }

    .role-badge-pill.visitor {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid var(--border-subtle);
      color: var(--text-sub);
    }

    .role-badge-pill.bespoke, .role-badge-pill.custom {
      background: rgba(168, 85, 247, 0.15);
      border: 1px solid rgba(168, 85, 247, 0.4);
      color: #c084fc;
    }

    textarea.form-input {
      resize: vertical;
      line-height: 1.5;
    }

    .role-card-title {
      font-family: var(--font-heading);
      font-size: 1.25rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
    }

    .role-card-desc {
      font-size: 0.85rem;
      color: var(--text-sub);
      line-height: 1.5;
      margin-bottom: 1.25rem;
    }

    .role-capabilities-list {
      list-style: none;
      font-size: 0.8rem;
      color: var(--text-main);
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .role-capabilities-list li {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .rule-check {
      color: #2ed573;
      font-weight: 700;
    }

    .rule-cross {
      color: #ff4757;
      font-weight: 700;
    }

    /* Rules of Engagement Cards */
    .guidelines-box {
      background: rgba(14, 14, 22, 0.6);
      border: 1px solid var(--border-subtle);
      border-radius: 16px;
      padding: 1.75rem;
      margin-top: 2rem;
    }

    .guideline-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      margin-bottom: 1.25rem;
    }

    .guideline-item:last-child {
      margin-bottom: 0;
    }

    .guideline-num {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(196, 21, 46, 0.2);
      border: 1px solid var(--border-red);
      color: var(--wheels-neon);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: var(--font-mono);
      font-size: 0.75rem;
      font-weight: 700;
      flex-shrink: 0;
    }

    .guideline-text h4 {
      font-family: var(--font-heading);
      font-size: 0.95rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 2px;
    }

    .guideline-text p {
      font-size: 0.82rem;
      color: var(--text-sub);
      line-height: 1.45;
    }

    /* Quick Live Site Button in top */
    .live-preview-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-subtle);
      font-size: 0.75rem;
      font-family: var(--font-mono);
      color: var(--text-sub);
      transition: all 0.2s ease;
    }

    .live-preview-pill:hover {
      background: rgba(196, 21, 46, 0.15);
      border-color: var(--border-red);
      color: #fff;
    }

    .pulse-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #2ed573;
      box-shadow: 0 0 8px #2ed573;
    }

    @media (max-width: 1024px) {
      .admin-sidebar {
        width: 220px;
        min-width: 220px;
      }
      .admin-main {
        margin-left: 220px;
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>

  <!-- ================= LEFT SIDEBAR ================= -->
  <aside class="admin-sidebar">
    <div class="sidebar-brand">
      <img src="../assets/images/logo.png" alt="UniWheels Logo" class="brand-logo-img">
      <div>
        <div class="brand-title">
          <span style="color:#fff;">UNI</span><span style="color:var(--wheels-crimson);">WHEELS</span>
        </div>
        <div class="brand-sub">EXECUTIVE SUITE</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-title">Navigation</div>
      
      <a href="index.php?tab=dashboard" class="nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
          </svg>
        </span>
        <span>Dashboard</span>
      </a>

      <a href="index.php?tab=cars" class="nav-item <?php echo $current_tab === 'cars' ? 'active' : ''; ?>">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.8 2 11v5c0 .6.4 1 1 1h2"></path>
            <circle cx="7" cy="17" r="2"></circle>
            <circle cx="17" cy="17" r="2"></circle>
          </svg>
        </span>
        <span>Cars Fleet</span>
        <span class="nav-badge"><?php echo count($active_cars); ?></span>
      </a>

      <a href="index.php?tab=sold" class="nav-item <?php echo $current_tab === 'sold' ? 'active' : ''; ?>">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </span>
        <span>Sold Cars</span>
        <span class="nav-badge" style="<?php echo count($sold_cars) > 0 ? 'background:rgba(255,71,87,0.2); color:#ff6b81;' : ''; ?>"><?php echo count($sold_cars); ?></span>
      </a>

      <a href="index.php?tab=employees" class="nav-item <?php echo $current_tab === 'employees' ? 'active' : ''; ?>">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
          </svg>
        </span>
        <span>Employees</span>
        <span class="nav-badge"><?php echo $employee_count; ?></span>
      </a>

      <div class="nav-section-title" style="margin-top:1rem;">External</div>

      <a href="../preview.html" target="_blank" class="nav-item">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="2" y1="12" x2="22" y2="12"></line>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
          </svg>
        </span>
        <span>Storefront Preview</span>
        <span style="font-size:0.75rem; color:var(--text-muted);">↗</span>
      </a>

      <a href="../" target="_blank" class="nav-item">
        <span class="nav-icon">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
        </span>
        <span>WordPress Site</span>
        <span style="font-size:0.75rem; color:var(--text-muted);">↗</span>
      </a>
    </nav>

    <!-- Sidebar Footer with Default Silhouette Person Avatar -->
    <div class="sidebar-footer">
      <div class="user-profile-card">
        <!-- Default Empty Person Silhouette Icon -->
        <div class="user-avatar-default" title="Default Profile Avatar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($current_user['full_name']); ?></div>
          <div class="user-role-badge"><?php echo htmlspecialchars(strtoupper($current_user['role'])); ?></div>
        </div>

        <a href="logout.php" class="btn-logout" title="Sign Out">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </a>
      </div>
    </div>
  </aside>

  <!-- ================= MAIN CONTENT ================= -->
  <main class="admin-main">

    <!-- Flash notifications -->
    <?php if ($flash): ?>
      <div class="flash-alert">
        <div style="display:flex; align-items:center; gap:10px;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#2ed573" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
          <span><?php echo htmlspecialchars($flash); ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer;">✕</button>
      </div>
    <?php endif; ?>

    <?php if ($flash_error): ?>
      <div class="flash-alert error">
        <div style="display:flex; align-items:center; gap:10px;">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#ff4757" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <span><?php echo htmlspecialchars($flash_error); ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" style="background:none; border:none; color:#fff; cursor:pointer;">✕</button>
      </div>
    <?php endif; ?>

    <!-- ================= TAB 1: DASHBOARD ================= -->
    <?php if ($current_tab === 'dashboard'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>System Overview</h1>
          <p>Real-time telemetry and inventory metrics for UniWheels Executive Fleet.</p>
        </div>
        <div class="top-actions">
          <div class="live-preview-pill">
            <span class="pulse-dot"></span>
            <span>MySQL Connected: <code>uniwheels</code></span>
          </div>
          <button class="btn btn-primary" onclick="openCreateModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Vehicle
          </button>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">
            <span>Total Fleet Builds</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.8 2 11v5c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
          </div>
          <div class="stat-value"><?php echo $stats['total']; ?></div>
          <div class="stat-meta">Active entries in <code>cars</code> table</div>
        </div>

        <div class="stat-card">
          <div class="stat-label">
            <span>Available In Stock</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#2ed573" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="stat-value" style="color:#2ed573;"><?php echo $stats['available']; ?></div>
          <div class="stat-meta">Immediate customer shipment ready</div>
        </div>

        <div class="stat-card">
          <div class="stat-label">
            <span>Recommended Sets</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#ffab00" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
          </div>
          <div class="stat-value" style="color:#ffab00;"><?php echo $stats['recommended']; ?></div>
          <div class="stat-meta">Highlighted in front showroom</div>
        </div>

        <div class="stat-card">
          <div class="stat-label">
            <span>Staff Members</span>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="var(--wheels-neon)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
          </div>
          <div class="stat-value" style="color:var(--wheels-neon);"><?php echo $employee_count; ?></div>
          <div class="stat-meta">Managed via <code>employees</code> table</div>
        </div>
      </div>

      <!-- Quick Fleet Snapshot Table -->
      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--wheels-crimson)" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
            <span>Recent Fleet Snapshot</span>
          </div>
          <a href="index.php?tab=cars" class="btn btn-outline" style="padding:6px 12px; font-size:0.75rem;">View Full Inventory & Uploads →</a>
        </div>
        
        <div class="data-table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Vehicle & Setup</th>
                <th>Status</th>
                <th>Fitment (F / R)</th>
                <th>Finish / Alloy</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($cars, 0, 5) as $c): ?>
                <tr>
                  <td>
                    <div class="car-cell-flex">
                      <div class="car-thumb-box">
                        <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file']); ?>" alt="" class="car-thumb-img" onerror="this.src='../assets/images/cars/car-1.png'">
                      </div>
                      <div>
                        <div class="car-name-title"><?php echo htmlspecialchars($c['name']); ?></div>
                        <div class="car-wheel-subtitle"><?php echo htmlspecialchars($c['wheel_name']); ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $c['status'])); ?>">
                      <?php echo htmlspecialchars($c['status']); ?>
                    </span>
                  </td>
                  <td style="font-family:var(--font-mono); font-size:0.78rem;">
                    <div>F: <?php echo htmlspecialchars($c['front_fitment']); ?></div>
                    <div>R: <?php echo htmlspecialchars($c['rear_fitment']); ?></div>
                  </td>
                  <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($c['finish']); ?></div>
                    <div style="color:var(--text-sub); font-size:0.75rem;"><?php echo htmlspecialchars($c['alloy_type']); ?></div>
                  </td>
                  <td>
                    <button class="btn-table-action" onclick='openEditModal(<?php echo json_encode($c); ?>)'>Edit</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- ================= TAB 2: CARS FLEET CRUD ================= -->
    <?php if ($current_tab === 'cars'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Vehicle Fleet Management</h1>
          <p>Manage active dealership inventory. Vehicles marked as AVAILABLE or RESERVED are listed here.</p>
        </div>
        <div class="top-actions">
          <button class="btn btn-primary" onclick="openCreateModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add New Vehicle
          </button>
        </div>
      </div>

      <!-- Fleet Subtabs: Active vs Sold -->
      <div class="fleet-subtabs">
        <a href="index.php?tab=cars" class="fleet-subtab-btn active">
          <span>Active Fleet</span>
          <span style="font-family:var(--font-mono); font-size:0.75rem; background:rgba(255,255,255,0.12); padding:2px 8px; border-radius:10px;"><?php echo count($active_cars); ?></span>
        </a>
        <a href="index.php?tab=sold" class="fleet-subtab-btn">
          <span>Sold Cars Archive</span>
          <span style="font-family:var(--font-mono); font-size:0.75rem; background:rgba(255,255,255,0.12); padding:2px 8px; border-radius:10px;"><?php echo count($sold_cars); ?></span>
        </a>
      </div>

      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--wheels-crimson)" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.8 2 11v5c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
            <span>Active Inventory (<?php echo count($active_cars); ?> Vehicles)</span>
          </div>
          <div style="font-size:0.75rem; color:var(--text-sub);">
            Use dropdown to switch status. Setting a car to <strong>SOLD</strong> moves it to the Sold Archive.
          </div>
        </div>

        <!-- Search Bar -->
        <div style="padding:1.25rem 1.25rem 0 1.25rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:1.25rem; flex-wrap:wrap;">
            <div style="position:relative; flex:1; max-width:420px;">
              <input type="text" id="activeFleetSearch" onkeyup="filterAdminFleetTable('activeFleetTable', this.value, 'activeMatchCount')" placeholder="Search by Stock ID, Marque, Model, Wheel, Fitment..." style="width:100%; padding:10px 14px 10px 38px; background:rgba(255,255,255,0.04); border:1px solid var(--border-subtle); border-radius:8px; color:#fff; font-size:0.85rem; font-family:var(--font-body); outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--wheels-crimson)'" onblur="this.style.borderColor='var(--border-subtle)'">
              <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none; opacity:0.4;" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <div style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
              Showing <span id="activeMatchCount" style="color:var(--wheels-neon); font-weight:700;"><?php echo count($active_cars); ?></span> of <?php echo count($active_cars); ?> active vehicles
            </div>
          </div>
        </div>

        <div class="data-table-wrapper">
          <table class="data-table" id="activeFleetTable">
            <thead>
              <tr>
                <th style="width:75px;">Stock ID</th>
                <th>Vehicle & Marque</th>
                <th>Price & Terms</th>
                <th>Status</th>
                <th>Specifications (Mi, Trans, Fuel)</th>
                <th>Wheels & Fitment</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($active_cars)): ?>
                <tr class="no-results-row">
                  <td colspan="7" style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
                    <div style="font-size:2rem; margin-bottom:8px; opacity:0.5;">🚗</div>
                    <div style="font-size:1rem; font-weight:600; color:#fff; margin-bottom:4px;">No Active Vehicles Found</div>
                    <div style="font-size:0.82rem;">Click "Add New Vehicle" above to add your first supercar to the active fleet.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($active_cars as $c): ?>
                  <tr>
                    <td style="font-family:var(--font-mono); font-weight:700; color:var(--wheels-neon); font-size:0.75rem;">
                      #<?php echo htmlspecialchars($c['stock_id'] ?: 'UW-' . $c['id']); ?>
                    </td>
                    <td>
                      <div class="car-cell-flex">
                        <div class="car-thumb-box">
                          <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file']); ?>" alt="" class="car-thumb-img" onerror="this.src='../assets/images/cars/car-1.png'">
                        </div>
                        <div>
                          <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                            <span style="font-size:0.68rem; font-weight:800; padding:2px 6px; border-radius:4px; background:rgba(196,21,46,0.2); color:var(--wheels-neon); text-transform:uppercase; font-family:var(--font-heading);"><?php echo htmlspecialchars($c['brand'] ?: 'UniWheels'); ?></span>
                            <span style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);"><?php echo htmlspecialchars($c['body_type'] ?: 'Coupe'); ?></span>
                          </div>
                          <div class="car-name-title"><?php echo htmlspecialchars($c['name']); ?></div>
                          <div class="car-wheel-subtitle"><?php echo htmlspecialchars($c['wheel_name']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($c['price'] > 0): ?>
                        <div style="font-family:var(--font-heading); font-weight:800; font-size:1rem; color:#fff;">
                          ₱<?php echo number_format($c['price'], 0); ?>
                        </div>
                        <div style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);">
                          Est. ₱<?php echo number_format(($c['price'] * 0.8 * (0.059/12) * pow(1 + 0.059/12, 60)) / (pow(1 + 0.059/12, 60) - 1), 0); ?>/mo
                        </div>
                      <?php else: ?>
                        <div style="font-family:var(--font-heading); font-weight:800; font-size:0.88rem; color:#ffab00; letter-spacing:0.04em;">
                          NEGOTIABLE
                        </div>
                        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono); text-transform:uppercase;">
                          Open for Offer
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form method="POST" action="index.php?tab=cars" style="margin:0;">
                        <input type="hidden" name="action" value="set_car_status">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <select name="new_status" onchange="this.form.submit()" class="admin-status-select status-<?php echo strtolower($c['status']); ?>" title="Select status (Setting to SOLD archives this vehicle)">
                          <option value="AVAILABLE" <?php echo $c['status'] === 'AVAILABLE' ? 'selected' : ''; ?>>● AVAILABLE</option>
                          <option value="RESERVED" <?php echo $c['status'] === 'RESERVED' ? 'selected' : ''; ?>>● RESERVED</option>
                          <option value="SOLD" <?php echo $c['status'] === 'SOLD' ? 'selected' : ''; ?>>● SOLD</option>
                        </select>
                      </form>
                    </td>
                    <td style="font-family:var(--font-mono); font-size:0.75rem;">
                      <div><span style="color:var(--text-muted);">Mi:</span> <?php echo htmlspecialchars($c['mileage'] ?: 'Delivery Miles'); ?></div>
                      <div><span style="color:var(--text-muted);">Trans:</span> <?php echo htmlspecialchars($c['transmission'] ?: 'Dual-Clutch'); ?></div>
                      <div><span style="color:var(--text-muted);">Fuel:</span> <?php echo htmlspecialchars($c['fuel_type'] ?: 'Gasoline'); ?></div>
                    </td>
                    <td style="font-family:var(--font-mono); font-size:0.72rem;">
                      <div><span style="color:var(--text-muted);">F:</span> <?php echo htmlspecialchars($c['front_fitment']); ?></div>
                      <div><span style="color:var(--text-muted);">R:</span> <?php echo htmlspecialchars($c['rear_fitment']); ?></div>
                      <div style="color:var(--text-sub);"><?php echo htmlspecialchars($c['finish']); ?></div>
                    </td>
                    <td style="text-align:right;">
                      <div class="table-actions" style="justify-content:flex-end;">
                        <button class="btn-table-action" onclick='openEditModal(<?php echo json_encode($c); ?>)'>Edit</button>
                        <form method="POST" action="index.php?tab=cars" onsubmit="return confirm('Are you sure you want to permanently delete <?php echo addslashes($c['name']); ?>?');" style="display:inline;">
                          <input type="hidden" name="action" value="delete_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-table-action delete">Delete</button>
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

    <!-- ================= TAB: SOLD CARS ARCHIVE ================= -->
    <?php if ($current_tab === 'sold'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Sold Vehicles Archive</h1>
          <p>Archived vehicles that have been marked as SOLD. These vehicles are hidden from the public showroom and can be retracted back to the active fleet at any time.</p>
        </div>
      </div>

      <!-- Fleet Subtabs: Active vs Sold -->
      <div class="fleet-subtabs">
        <a href="index.php?tab=cars" class="fleet-subtab-btn">
          <span>Active Fleet</span>
          <span style="font-family:var(--font-mono); font-size:0.75rem; background:rgba(255,255,255,0.12); padding:2px 8px; border-radius:10px;"><?php echo count($active_cars); ?></span>
        </a>
        <a href="index.php?tab=sold" class="fleet-subtab-btn active">
          <span>Sold Cars Archive</span>
          <span style="font-family:var(--font-mono); font-size:0.75rem; background:rgba(255,255,255,0.12); padding:2px 8px; border-radius:10px;"><?php echo count($sold_cars); ?></span>
        </a>
      </div>

      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--wheels-crimson)" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Sold Vehicles (<?php echo count($sold_cars); ?> Archived)</span>
          </div>
          <div style="font-size:0.75rem; color:var(--text-sub);">
            Sorted by transfer date (most recently sold first). Use "Retract to Fleet" to restore refunded or cancelled vehicles back to AVAILABLE.
          </div>
        </div>

        <!-- Search Bar -->
        <div style="padding:1.25rem 1.25rem 0 1.25rem;">
          <div style="display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:1.25rem; flex-wrap:wrap;">
            <div style="position:relative; flex:1; max-width:420px;">
              <input type="text" id="soldFleetSearch" onkeyup="filterAdminFleetTable('soldFleetTable', this.value, 'soldMatchCount')" placeholder="Search sold vehicles by Stock ID, Marque, Model..." style="width:100%; padding:10px 14px 10px 38px; background:rgba(255,255,255,0.04); border:1px solid var(--border-subtle); border-radius:8px; color:#fff; font-size:0.85rem; font-family:var(--font-body); outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--wheels-crimson)'" onblur="this.style.borderColor='var(--border-subtle)'">
              <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none; opacity:0.4;" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>
            <div style="font-size:0.78rem; color:var(--text-muted); font-family:var(--font-mono);">
              Showing <span id="soldMatchCount" style="color:var(--wheels-neon); font-weight:700;"><?php echo count($sold_cars); ?></span> of <?php echo count($sold_cars); ?> sold vehicles
            </div>
          </div>
        </div>

        <div class="data-table-wrapper">
          <table class="data-table" id="soldFleetTable">
            <thead>
              <tr>
                <th style="width:75px;">Stock ID</th>
                <th>Vehicle & Marque</th>
                <th>Sold / Listed Price</th>
                <th>Status</th>
                <th>Date Transferred / Sold</th>
                <th>Specifications</th>
                <th>Wheels & Fitment</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($sold_cars)): ?>
                <tr class="no-results-row">
                  <td colspan="8" style="text-align:center; padding:3rem 1rem; color:var(--text-muted);">
                    <div style="font-size:2rem; margin-bottom:8px; opacity:0.5;">🏷️</div>
                    <div style="font-size:1rem; font-weight:600; color:#fff; margin-bottom:4px;">No Sold Vehicles Recorded</div>
                    <div style="font-size:0.82rem;">When vehicles in the active fleet are set to "SOLD", they will automatically be archived here.</div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($sold_cars as $c): ?>
                  <tr>
                    <td style="font-family:var(--font-mono); font-weight:700; color:var(--wheels-neon); font-size:0.75rem;">
                      #<?php echo htmlspecialchars($c['stock_id'] ?: 'UW-' . $c['id']); ?>
                    </td>
                    <td>
                      <div class="car-cell-flex">
                        <div class="car-thumb-box">
                          <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file']); ?>" alt="" class="car-thumb-img" onerror="this.src='../assets/images/cars/car-1.png'">
                        </div>
                        <div>
                          <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                            <span style="font-size:0.68rem; font-weight:800; padding:2px 6px; border-radius:4px; background:rgba(196,21,46,0.2); color:var(--wheels-neon); text-transform:uppercase; font-family:var(--font-heading);"><?php echo htmlspecialchars($c['brand'] ?: 'UniWheels'); ?></span>
                            <span style="font-size:0.7rem; color:var(--text-muted); font-family:var(--font-mono);"><?php echo htmlspecialchars($c['body_type'] ?: 'Coupe'); ?></span>
                          </div>
                          <div class="car-name-title"><?php echo htmlspecialchars($c['name']); ?></div>
                          <div class="car-wheel-subtitle"><?php echo htmlspecialchars($c['wheel_name']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <?php if ($c['price'] > 0): ?>
                        <div style="font-family:var(--font-heading); font-weight:800; font-size:1rem; color:#fff;">
                          ₱<?php echo number_format($c['price'], 0); ?>
                        </div>
                      <?php else: ?>
                        <div style="font-family:var(--font-heading); font-weight:800; font-size:0.88rem; color:#ffab00; letter-spacing:0.04em;">
                          NEGOTIABLE
                        </div>
                        <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono); text-transform:uppercase;">
                          Open for Offer
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="status-badge sold">
                        ● SOLD
                      </span>
                    </td>
                    <td style="font-family:var(--font-mono); font-size:0.78rem; color:#fff;">
                      <div style="display:flex; align-items:center; gap:6px;">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="var(--wheels-neon)" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span><?php echo !empty($c['sold_at']) ? date('M d, Y h:i A', strtotime($c['sold_at'])) : 'Archived'; ?></span>
                      </div>
                    </td>
                    <td style="font-family:var(--font-mono); font-size:0.75rem;">
                      <div><span style="color:var(--text-muted);">Mi:</span> <?php echo htmlspecialchars($c['mileage'] ?: 'Delivery Miles'); ?></div>
                      <div><span style="color:var(--text-muted);">Trans:</span> <?php echo htmlspecialchars($c['transmission'] ?: 'Dual-Clutch'); ?></div>
                      <div><span style="color:var(--text-muted);">Fuel:</span> <?php echo htmlspecialchars($c['fuel_type'] ?: 'Gasoline'); ?></div>
                    </td>
                    <td style="font-family:var(--font-mono); font-size:0.72rem;">
                      <div><span style="color:var(--text-muted);">F:</span> <?php echo htmlspecialchars($c['front_fitment']); ?></div>
                      <div><span style="color:var(--text-muted);">R:</span> <?php echo htmlspecialchars($c['rear_fitment']); ?></div>
                      <div style="color:var(--text-sub);"><?php echo htmlspecialchars($c['finish']); ?></div>
                    </td>
                    <td style="text-align:right;">
                      <div class="table-actions" style="justify-content:flex-end;">
                        <!-- Retract to Fleet Button -->
                        <form method="POST" action="index.php?tab=sold" onsubmit="return confirm('Restore <?php echo addslashes($c['name']); ?> back to the Active Fleet?\n\nVehicle status will become AVAILABLE and it will immediately reappear in the public showroom catalog.');" style="display:inline;">
                          <input type="hidden" name="action" value="retract_sold_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-table-action" style="padding:6px 12px; font-size:0.75rem; background:rgba(46,213,115,0.12); color:#2ed573; border-color:rgba(46,213,115,0.4); display:inline-flex; align-items:center; gap:5px;" title="Retract to Fleet (re-activates vehicle as AVAILABLE)">
                            <span style="font-size:0.9rem;">↺</span> Retract to Fleet
                          </button>
                        </form>
                        <button class="btn-table-action" onclick='openEditModal(<?php echo json_encode($c); ?>)'>Edit</button>
                        <form method="POST" action="index.php?tab=sold" onsubmit="return confirm('Are you sure you want to permanently delete <?php echo addslashes($c['name']); ?>?');" style="display:inline;">
                          <input type="hidden" name="action" value="delete_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-table-action delete">Delete</button>
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

    <!-- ================= TAB 3: EMPLOYEES ================= -->
    <?php if ($current_tab === 'employees'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Staff & Employee Directory</h1>
          <p>Access privileges and user management for UniWheels showroom personnel.</p>
        </div>
        <div class="top-actions">
          <button class="btn btn-primary" onclick="openEmployeeModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Employee
          </button>
        </div>
      </div>

      <!-- Current Users Table -->
      <div class="card-panel">
        <div class="card-panel-header">
          <div class="card-panel-title">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="var(--wheels-crimson)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            <span>Registered Staff Accounts (<code>employees</code> table)</span>
          </div>
          <div style="font-size:0.75rem; color:var(--text-sub);">
            Default silhouettes are used across all profiles
          </div>
        </div>

        <div class="data-table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Profile & Name</th>
                <th>Username</th>
                <th>Email Address</th>
                <th>Permission Role</th>
                <th>Account Status</th>
                <th>Created At</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users_list as $u): ?>
                <tr>
                  <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                      <!-- Default Empty Person Silhouette -->
                      <div class="user-avatar-default" style="width:36px; height:36px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:20px; height:20px;">
                          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                          <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                      </div>
                      <div>
                        <div style="font-weight:700; color:#fff;"><?php echo htmlspecialchars($u['name'] ?? $u['full_name'] ?? 'Staff Member'); ?></div>
                        <?php if ($u['id'] == $_SESSION['user_id']): ?>
                          <span style="font-size:0.65rem; color:var(--wheels-neon); font-family:var(--font-mono);">(Current Session)</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td><code style="color:var(--text-main);"><?php echo htmlspecialchars($u['username']); ?></code></td>
                  <td style="color:var(--text-sub);"><?php echo htmlspecialchars($u['email']); ?></td>
                  <td>
                    <span class="role-badge-pill <?php echo strtolower($u['role']); ?>">
                      <?php echo htmlspecialchars(strtoupper($u['role'])); ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-badge available">● <?php echo htmlspecialchars(strtoupper($u['status'])); ?></span>
                  </td>
                  <td style="font-family:var(--font-mono); font-size:0.75rem; color:var(--text-muted);">
                    <?php echo htmlspecialchars($u['created_at']); ?>
                  </td>
                  <td style="text-align:right;">
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                      <form method="POST" action="index.php?tab=employees" onsubmit="return confirm('Remove user <?php echo addslashes($u['username']); ?>?');" style="display:inline;">
                        <input type="hidden" name="action" value="delete_employee">
                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" class="btn-table-action delete">Revoke Access</button>
                      </form>
                    <?php else: ?>
                      <span style="font-size:0.75rem; color:var(--text-muted);">Primary Admin</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State Notice for Subordinate Employees -->
      <?php if (count($users_list) <= 1): ?>
        <div class="empty-state-box">
          <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <h3 class="empty-state-title">Subordinate Employee Roster Empty</h3>
          <p class="empty-state-desc">
            No secondary employees or managers are registered yet. As Super Administrator, you are managing all inventory, fitment specifications, and asset uploads. Click below to add staff members when your dealership expands.
          </p>
          <button class="btn btn-primary" onclick="openEmployeeModal()">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Create First Staff Member
          </button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </main>

  <!-- ================= MODAL: ADD VEHICLE ================= -->
  <div class="modal-overlay" id="createCarModal">
    <div class="modal-dialog">
      <div class="modal-header">
        <h2 class="modal-title">Add New Vehicle to Fleet</h2>
        <button class="modal-close" onclick="closeCreateModal()">&times;</button>
      </div>
      
      <form method="POST" action="index.php?tab=cars" enctype="multipart/form-data">
        <input type="hidden" name="action" value="create_car">
        <div class="modal-body">
          
          <!-- Brand & Model Info -->
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label" for="add_brand">Brand / Marque *</label>
              <input type="text" name="brand" id="add_brand" list="brandDatalist" class="form-input" placeholder="e.g. Ferrari, Pagani, Rimac..." required>
              <datalist id="brandDatalist">
                <?php foreach ($all_existing_brands as $b): ?>
                  <option value="<?php echo htmlspecialchars($b); ?>"></option>
                <?php endforeach; ?>
              </datalist>
            </div>
            <div class="form-group">
              <label class="form-label" for="add_price">Price (₱ PHP) <span style="font-weight:normal; color:var(--text-muted); font-size:0.75rem;">(0 for Negotiable / Open for Offer)</span></label>
              <input type="number" step="1000" name="price" id="add_price" class="form-input" placeholder="e.g. 0 or 450000" value="0">
            </div>
            <div class="form-group">
              <label class="form-label" for="add_stock_id">Stock Reference ID</label>
              <input type="text" name="stock_id" id="add_stock_id" class="form-input" placeholder="e.g. UW-AM09">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="add_name">Vehicle Model & Trim *</label>
              <input type="text" name="name" id="add_name" class="form-input" placeholder="e.g. Aston Martin DBS Superleggera" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="add_wheel_name">Wheel Series / Model *</label>
              <input type="text" name="wheel_name" id="add_wheel_name" class="form-input" placeholder="e.g. UW-AM1 Ultra-Concave Forged" required>
            </div>
          </div>

          <!-- Specifications Row -->
          <div class="form-grid-4">
            <div class="form-group">
              <label class="form-label" for="add_mileage">Mileage</label>
              <input type="text" name="mileage" id="add_mileage" class="form-input" placeholder="e.g. 1,200 mi" value="Delivery Miles">
            </div>
            <div class="form-group">
              <label class="form-label" for="add_transmission">Transmission</label>
              <select name="transmission" id="add_transmission" class="form-select">
                <option value="Dual-Clutch DCT">Dual-Clutch DCT</option>
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="Sequential">Sequential</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="add_body_type">Body Type</label>
              <select name="body_type" id="add_body_type" class="form-select">
                <option value="Coupe">Coupe</option>
                <option value="Hypercar">Hypercar</option>
                <option value="Track Special">Track Special</option>
                <option value="Convertible / Targa">Convertible / Targa</option>
                <option value="Sedan">Sedan</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="add_seating">Seating</label>
              <input type="text" name="seating" id="add_seating" class="form-input" placeholder="e.g. 2 Seats" value="2 Seats">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="add_fuel_type">Engine / Fuel Powertrain</label>
              <input type="text" name="fuel_type" id="add_fuel_type" class="form-input" placeholder="e.g. 4.0L Twin-Turbo V8 Hybrid" value="Twin-Turbo V8 Gasoline">
            </div>
            <div class="form-group">
              <label class="form-label" for="add_status">Inventory Status *</label>
              <select name="status" id="add_status" class="form-select">
                <option value="AVAILABLE">AVAILABLE (Active in public catalog)</option>
                <option value="RESERVED">RESERVED (Pending deal, public actions disabled)</option>
                <option value="SOLD">SOLD (Archived to Sold Cars list)</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="add_finish">Wheel Finish</label>
              <input type="text" name="finish" id="add_finish" class="form-input" placeholder="e.g. Gloss Brushed Crimson" value="Satin Stealth Black">
            </div>
            <div class="form-group">
              <label class="form-label" for="add_weight">Approx Weight</label>
              <input type="text" name="weight" id="add_weight" class="form-input" placeholder="e.g. 9.4 kg (20.7 lbs)" value="9.8 kg (21.6 lbs)">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="add_front_fitment">Front Fitment (Width x Diam, ET)</label>
              <input type="text" name="front_fitment" id="add_front_fitment" class="form-input" placeholder="20x9.5 ET22" value="20x9.5 ET20">
            </div>
            <div class="form-group">
              <label class="form-label" for="add_rear_fitment">Rear Fitment (Width x Diam, ET)</label>
              <input type="text" name="rear_fitment" id="add_rear_fitment" class="form-input" placeholder="21x12.5 ET35" value="21x12.0 ET32">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="add_alloy_type">Alloy Construction</label>
            <input type="text" name="alloy_type" id="add_alloy_type" class="form-input" placeholder="e.g. 6061-T6 Aerospace Aluminum" value="6061-T6 Aerospace Forged">
          </div>

          <!-- Direct Image Upload & Image Selector -->
          <div class="form-group">
            <label class="form-label">Vehicle Image (Direct Upload or Select Asset)</label>
            
            <div class="upload-dropzone">
              <input type="file" name="car_upload_file" id="add_car_upload_file" accept="image/png,image/jpeg,image/webp" onchange="previewUploadImage(this, 'add_preview')">
              <div style="font-size:1.5rem; margin-bottom:4px;">📁</div>
              <div style="font-weight:700; font-size:0.85rem; color:#fff;">Click or drag to upload a transparent car image</div>
              <div style="font-size:0.75rem; color:var(--text-sub); margin-top:3px;">
                Files will be automatically uploaded into <code>assets/images/cars/</code>
              </div>
            </div>

            <!-- Fallback / Select Existing -->
            <div style="margin-top:10px;">
              <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Or pick existing image in folder:</span>
              <select name="image_file" id="add_image_file" class="form-select" style="margin-top:4px;" onchange="updateSelectPreview(this.value, 'add_preview')">
                <?php foreach ($available_images as $img): ?>
                  <option value="<?php echo htmlspecialchars($img); ?>"><?php echo htmlspecialchars($img); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="upload-preview-container" id="add_preview_container">
              <span style="font-size:0.75rem; color:var(--text-muted);">Preview:</span>
              <img id="add_preview" src="../assets/images/cars/<?php echo $available_images[0] ?? 'car-1.png'; ?>" class="upload-preview-img" alt="Preview">
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeCreateModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Vehicle to Fleet</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL: EDIT VEHICLE ================= -->
  <div class="modal-overlay" id="editCarModal">
    <div class="modal-dialog">
      <div class="modal-header">
        <h2 class="modal-title">Edit Vehicle Specification</h2>
        <button class="modal-close" onclick="closeEditModal()">&times;</button>
      </div>
      
      <form method="POST" action="index.php?tab=cars" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_car">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="existing_image_file" id="edit_existing_image_file">
        
        <div class="modal-body">
          <!-- Brand & Model Info -->
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label" for="edit_brand">Brand / Marque *</label>
              <input type="text" name="brand" id="edit_brand" list="brandDatalist" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_price">Price (₱ PHP) <span style="font-weight:normal; color:var(--text-muted); font-size:0.75rem;">(0 for Negotiable / Open for Offer)</span></label>
              <input type="number" step="1000" name="price" id="edit_price" class="form-input" placeholder="e.g. 0 or 450000">
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_stock_id">Stock Reference ID</label>
              <input type="text" name="stock_id" id="edit_stock_id" class="form-input">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="edit_name">Vehicle Model & Trim *</label>
              <input type="text" name="name" id="edit_name" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_wheel_name">Wheel Series / Model *</label>
              <input type="text" name="wheel_name" id="edit_wheel_name" class="form-input" required>
            </div>
          </div>

          <!-- Specifications Row -->
          <div class="form-grid-4">
            <div class="form-group">
              <label class="form-label" for="edit_mileage">Mileage</label>
              <input type="text" name="mileage" id="edit_mileage" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_transmission">Transmission</label>
              <select name="transmission" id="edit_transmission" class="form-select">
                <option value="Dual-Clutch DCT">Dual-Clutch DCT</option>
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="Sequential">Sequential</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_body_type">Body Type</label>
              <select name="body_type" id="edit_body_type" class="form-select">
                <option value="Coupe">Coupe</option>
                <option value="Hypercar">Hypercar</option>
                <option value="Track Special">Track Special</option>
                <option value="Convertible / Targa">Convertible / Targa</option>
                <option value="Sedan">Sedan</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_seating">Seating</label>
              <input type="text" name="seating" id="edit_seating" class="form-input">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="edit_fuel_type">Engine / Fuel Powertrain</label>
              <input type="text" name="fuel_type" id="edit_fuel_type" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_status">Inventory Status *</label>
              <select name="status" id="edit_status" class="form-select">
                <option value="AVAILABLE">AVAILABLE (Active in public catalog)</option>
                <option value="RESERVED">RESERVED (Pending deal, public actions disabled)</option>
                <option value="SOLD">SOLD (Archived to Sold Cars list)</option>
              </select>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="edit_finish">Wheel Finish</label>
              <input type="text" name="finish" id="edit_finish" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_weight">Approx Weight</label>
              <input type="text" name="weight" id="edit_weight" class="form-input">
            </div>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="edit_front_fitment">Front Fitment</label>
              <input type="text" name="front_fitment" id="edit_front_fitment" class="form-input">
            </div>
            <div class="form-group">
              <label class="form-label" for="edit_rear_fitment">Rear Fitment</label>
              <input type="text" name="rear_fitment" id="edit_rear_fitment" class="form-input">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="edit_alloy_type">Alloy Construction</label>
            <input type="text" name="alloy_type" id="edit_alloy_type" class="form-input">
          </div>

          <!-- Image Update -->
          <div class="form-group">
            <label class="form-label">Replace Image (Direct Upload or Select)</label>
            
            <div class="upload-dropzone">
              <input type="file" name="car_upload_file" id="edit_car_upload_file" accept="image/png,image/jpeg,image/webp" onchange="previewUploadImage(this, 'edit_preview')">
              <div style="font-size:1.5rem; margin-bottom:4px;">📁</div>
              <div style="font-weight:700; font-size:0.85rem; color:#fff;">Upload a new image to replace current asset</div>
              <div style="font-size:0.75rem; color:var(--text-sub);">Will be saved into <code>assets/images/cars/</code></div>
            </div>

            <div style="margin-top:10px;">
              <span style="font-size:0.72rem; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Or select an existing asset:</span>
              <select name="image_file" id="edit_image_file" class="form-select" style="margin-top:4px;" onchange="updateSelectPreview(this.value, 'edit_preview')">
                <?php foreach ($available_images as $img): ?>
                  <option value="<?php echo htmlspecialchars($img); ?>"><?php echo htmlspecialchars($img); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="upload-preview-container">
              <span style="font-size:0.75rem; color:var(--text-muted);">Current Asset Preview:</span>
              <img id="edit_preview" src="" class="upload-preview-img" alt="Preview">
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= MODAL: ADD EMPLOYEE ================= -->
  <div class="modal-overlay" id="createEmployeeModal">
    <div class="modal-dialog" style="max-width:520px;">
      <div class="modal-header">
        <h2 class="modal-title">Register Staff Member</h2>
        <button class="modal-close" onclick="closeEmployeeModal()">&times;</button>
      </div>

      <form method="POST" action="index.php?tab=employees">
        <input type="hidden" name="action" value="create_employee">
        <div class="modal-body">
          
          <div class="form-group">
            <label class="form-label" for="emp_fullname">Full Name</label>
            <input type="text" name="full_name" id="emp_fullname" class="form-input" placeholder="e.g. Marcus Vance" required>
          </div>

          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label" for="emp_username">Username</label>
              <input type="text" name="username" id="emp_username" class="form-input" placeholder="e.g. mvance" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="emp_password">Initial Password</label>
              <input type="password" name="password" id="emp_password" class="form-input" placeholder="••••••••" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="emp_email">Email Address</label>
            <input type="email" name="email" id="emp_email" class="form-input" placeholder="e.g. mvance@uniwheels.com">
          </div>

          <div class="form-group">
            <label class="form-label" for="emp_role">Assigned Role</label>
            <select name="role" id="emp_role" class="form-select">
              <?php foreach ($roles_list as $rl): 
                if ($rl['role_key'] === 'visitor') continue;
              ?>
                <option value="<?php echo htmlspecialchars($rl['role_key']); ?>" <?php echo ($rl['role_key'] === 'employee') ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($rl['name']); ?> (<?php echo htmlspecialchars($rl['role_key']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <p style="font-size:0.75rem; color:var(--text-muted); line-height:1.4;">
            Note: All staff members will automatically be assigned the default neutral silhouette avatar. Passwords are securely hashed with standard bcrypt prior to saving.
          </p>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeEmployeeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Account</button>
        </div>
      </form>
    </div>
  </div>





  <script>
    // Modal Helpers
    function openCreateModal() {
      document.getElementById('createCarModal').classList.add('active');
    }
    function closeCreateModal() {
      document.getElementById('createCarModal').classList.remove('active');
    }

    function openEditModal(car) {
      document.getElementById('edit_id').value = car.id;
      document.getElementById('edit_name').value = car.name || '';
      document.getElementById('edit_brand').value = car.brand || '';
      document.getElementById('edit_price').value = car.price || 0;
      document.getElementById('edit_stock_id').value = car.stock_id || '';
      document.getElementById('edit_mileage').value = car.mileage || 'Delivery Miles';
      document.getElementById('edit_transmission').value = car.transmission || 'Dual-Clutch DCT';
      document.getElementById('edit_body_type').value = car.body_type || 'Coupe';
      document.getElementById('edit_fuel_type').value = car.fuel_type || '';
      document.getElementById('edit_seating').value = car.seating || '2 Seats';
      document.getElementById('edit_wheel_name').value = car.wheel_name || '';
      document.getElementById('edit_status').value = car.status || 'AVAILABLE';
      document.getElementById('edit_finish').value = car.finish || '';
      document.getElementById('edit_front_fitment').value = car.front_fitment || '';
      document.getElementById('edit_rear_fitment').value = car.rear_fitment || '';
      document.getElementById('edit_alloy_type').value = car.alloy_type || '';
      document.getElementById('edit_weight').value = car.weight || '';
      document.getElementById('edit_existing_image_file').value = car.image_file;
      document.getElementById('edit_image_file').value = car.image_file;
      document.getElementById('edit_preview').src = '../assets/images/cars/' + car.image_file;
      
      document.getElementById('editCarModal').classList.add('active');
    }
    function closeEditModal() {
      document.getElementById('editCarModal').classList.remove('active');
    }

    function openEmployeeModal() {
      document.getElementById('createEmployeeModal').classList.add('active');
    }
    function closeEmployeeModal() {
      document.getElementById('createEmployeeModal').classList.remove('active');
    }

    // Live Instant Search for Active Fleet & Sold Archive
    function filterAdminFleetTable(tableId, query, countId) {
      var table = document.getElementById(tableId);
      if (!table) return;
      var rows = table.querySelectorAll('tbody tr');
      var q = (query || '').toLowerCase().trim();
      var matchCount = 0;
      
      rows.forEach(function(row) {
        if (row.classList.contains('no-results-row')) return;
        var text = row.innerText.toLowerCase();
        if (!q || text.indexOf(q) !== -1) {
          row.style.display = '';
          matchCount++;
        } else {
          row.style.display = 'none';
        }
      });

      if (countId) {
        var countEl = document.getElementById(countId);
        if (countEl) countEl.textContent = matchCount;
      }
    }

    // Dynamic Image Previews
    function previewUploadImage(input, previewId) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    function updateSelectPreview(val, previewId) {
      document.getElementById(previewId).src = '../assets/images/cars/' + val;
    }

    // Close modal on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
        closeEmployeeModal();
      }
    });
  </script>
</body>
</html>
