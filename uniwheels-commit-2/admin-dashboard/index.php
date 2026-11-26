<?php
/**
 * UniWheels Motors - Dealership Operations, Reservation Hold Manager & Editable CMS (ECMS)
 * Allows staff to manage cars, prices, condition/damage reports, categorized photos,
 * real-time availability (Available, Reserved, Under Maintenance, Sold), active holds,
 * Admin-only Agent/Owner records, and website content/policies without editing code.
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user = [
    'id'        => $_SESSION['user_id'],
    'username'  => $_SESSION['username'] ?? 'admin',
    'full_name' => $_SESSION['full_name'] ?? 'Administrator',
    'role'      => $_SESSION['role'] ?? 'admin',
    'email'     => $_SESSION['email'] ?? 'admin@uniwheels.com'
];

$suppress_db_die = true;
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/uniwheels-core.php';

if ($pdo) {
    uniwheels_ensure_schema($pdo);
    uniwheels_release_expired_holds($pdo);
}

$ecms_settings = $pdo ? uniwheels_get_settings($pdo) : uniwheels_default_settings();
$ref_year = intval($ecms_settings['reference_year'] ?? 2026);

// Target folder for car photos
$cars_img_dir = __DIR__ . '/../assets/images/cars';

if (!is_dir($cars_img_dir)) @mkdir($cars_img_dir, 0777, true);

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
                $unique_name = $clean_basename . '-' . time() . '-' . rand(10, 99) . '.' . $ext;
                $dest_path = $cars_img_dir . '/' . $unique_name;
                if (move_uploaded_file($file_tmp, $dest_path)) {
                    return $unique_name;
                }
            }
        }
        return $fallback_image;
    }
}

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

$valid_statuses = ['AVAILABLE', 'RESERVED', 'UNDER MAINTENANCE', 'SOLD'];
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$current_tab = $_GET['tab'] ?? 'dashboard';

if ($pdo && $request_method === 'POST') {
    $action = $_POST['action'] ?? '';

    // ================= 1. CREATE / UPDATE VEHICLE (WITH CONDITION & DAMAGE DISCLOSURE) =================
    if ($action === 'create_car' || $action === 'update_car') {
        $is_update = ($action === 'update_car');
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $year_model = intval($_POST['year_model'] ?? 2024);
        $brand = trim($_POST['brand'] ?? 'Toyota');
        $price = floatval($_POST['price'] ?? 0);
        $price_note = trim($_POST['price_note'] ?? 'Negotiable');
        $deposit_amount = floatval($_POST['deposit_amount'] ?? ($ecms_settings['default_deposit'] ?? 10000));
        $stock_id = trim($_POST['stock_id'] ?? '');
        if (empty($stock_id) && $name) {
            $stock_id = 'UW-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $brand), 0, 3)) . rand(10, 99);
        }
        $plate_number = trim($_POST['plate_number'] ?? '');
        $agent_owner = trim($_POST['agent_owner'] ?? 'Boss');
        $mileage = trim($_POST['mileage'] ?? 'Low Mileage');
        $transmission = trim($_POST['transmission'] ?? 'Automatic');
        $body_type = trim($_POST['body_type'] ?? 'Sedan');
        $fuel_type = trim($_POST['fuel_type'] ?? 'Gasoline');
        $seating = trim($_POST['seating'] ?? '5 Seats');
        $finish = trim($_POST['finish'] ?? 'Original Paint');
        $ownership_history = trim($_POST['ownership_history'] ?? '1 previous owner');
        $damage_disclosure = trim($_POST['damage_disclosure'] ?? '');
        $vehicle_policies = trim($_POST['vehicle_policies'] ?? '');

        $status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));
        if (!in_array($status, $valid_statuses, true)) {
            $status = 'AVAILABLE';
        }

        $reserved_until = null;
        if ($status === 'RESERVED') {
            $hold_days_admin = max(1, intval($_POST['admin_hold_days'] ?? 2));
            $existing_until = trim($_POST['existing_reserved_until'] ?? '');
            if (!empty($existing_until) && strtotime($existing_until) > time() && empty($_POST['reset_hold_timer'])) {
                $reserved_until = date('Y-m-d H:i:s', strtotime($existing_until));
            } else {
                $reserved_until = date('Y-m-d H:i:s', time() + ($hold_days_admin * 86400));
            }
        }

        $is_recommended = isset($_POST['is_recommended']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $fallback_img = $is_update ? trim($_POST['existing_image_file'] ?? 'car-1.jpg') : 'car-1.jpg';
        $image_file = handleCarImageUpload('car_upload_file', $cars_img_dir, $fallback_img);

        $kept_gallery = ($is_update && isset($_POST['kept_gallery_images']) && is_array($_POST['kept_gallery_images']))
            ? $_POST['kept_gallery_images']
            : [];
        $gallery = handleAdditionalImagesUpload('additional_photos', $cars_img_dir, $kept_gallery);

        // Damage Close-Up Photo uploads
        $kept_dmg_files = ($is_update && isset($_POST['kept_damage_images']) && is_array($_POST['kept_damage_images']))
            ? $_POST['kept_damage_images']
            : [];
        $dmg_files = handleAdditionalImagesUpload('damage_photos_upload', $cars_img_dir, $kept_dmg_files);
        $dmg_json_arr = [];
        foreach ($dmg_files as $df) {
            $dmg_json_arr[] = ['file' => $df, 'caption' => 'Documented condition close-up photo'];
            if (!in_array($df, $gallery, true)) {
                $gallery[] = $df;
            }
        }
        $gallery_json = !empty($gallery) ? json_encode(array_values($gallery)) : null;
        $damage_photos_json = !empty($dmg_json_arr) ? json_encode($dmg_json_arr) : null;

        if (!$is_update && $name) {
            $stmt = $pdo->prepare("INSERT INTO `cars` (
                `name`, `year_model`, `brand`, `price`, `price_note`, `deposit_amount`,
                `stock_id`, `plate_number`, `agent_owner`, `mileage`, `transmission`,
                `body_type`, `fuel_type`, `seating`, `finish`, `ownership_history`,
                `damage_disclosure`, `damage_photos`, `vehicle_policies`,
                `wheel_name`, `status`, `reserved_until`, `is_recommended`, `is_featured`,
                `front_fitment`, `rear_fitment`, `alloy_type`, `weight`, `image_file`, `gallery_images`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'OEM Factory Wheels', ?, ?, ?, ?, ?, ?, '100% Flood-Free & Mechanic Inspected', ?, ?, ?)");
            $stmt->execute([
                $name, $year_model, $brand, $price, $price_note, $deposit_amount,
                $stock_id, $plate_number, $agent_owner, $mileage, $transmission,
                $body_type, $fuel_type, $seating, $finish, $ownership_history,
                $damage_disclosure, $damage_photos_json, $vehicle_policies ?: null,
                $status, $reserved_until, $is_recommended, $is_featured,
                "Plate: {$plate_number}", $ownership_history, $price_note, $image_file, $gallery_json
            ]);
            $_SESSION['flash'] = "Vehicle '{$name}' created successfully!";
            header("Location: index.php?tab=" . ($status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }

        if ($is_update && $id && $name) {
            // Clear photo_metadata if photos changed so auto-categorizer refreshes cleanly
            $stmt = $pdo->prepare("UPDATE `cars` SET
                `name`=?, `year_model`=?, `brand`=?, `price`=?, `price_note`=?, `deposit_amount`=?,
                `stock_id`=?, `plate_number`=?, `agent_owner`=?, `mileage`=?, `transmission`=?,
                `body_type`=?, `fuel_type`=?, `seating`=?, `finish`=?, `ownership_history`=?,
                `damage_disclosure`=?, `damage_photos`=?, `vehicle_policies`=?, `rear_fitment`=?,
                `status`=?, `reserved_until`=?, `is_recommended`=?, `is_featured`=?,
                `image_file`=?, `gallery_images`=?, `photo_metadata`=NULL
                WHERE `id`=?");
            $stmt->execute([
                $name, $year_model, $brand, $price, $price_note, $deposit_amount,
                $stock_id, $plate_number, $agent_owner, $mileage, $transmission,
                $body_type, $fuel_type, $seating, $finish, $ownership_history,
                $damage_disclosure, $damage_photos_json, $vehicle_policies ?: null, $ownership_history,
                $status, $reserved_until, $is_recommended, $is_featured,
                $image_file, $gallery_json, $id
            ]);

            if ($status !== 'RESERVED') {
                $pdo->prepare("UPDATE `cars` SET `active_reservation_ref` = NULL WHERE `id` = ?")->execute([$id]);
            }

            $_SESSION['flash'] = "Vehicle #{$id} ({$name}) updated successfully!";
            header("Location: index.php?tab=" . ($status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }
    }

    // ================= 2. QUICK VEHICLE STATUS / DELETE / PIN ACTIONS =================
    if ($action === 'delete_car') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("DELETE FROM `cars` WHERE `id` = ?")->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} deleted from inventory.";
        }
        header("Location: index.php?tab=" . urlencode($current_tab));
        exit;
    }

    if ($action === 'change_status') {
        $id = intval($_POST['id'] ?? 0);
        $new_status = strtoupper(trim($_POST['status'] ?? 'AVAILABLE'));
        if ($id && in_array($new_status, $valid_statuses, true)) {
            $res_until = ($new_status === 'RESERVED') ? date('Y-m-d H:i:s', time() + 2 * 86400) : null;
            $pdo->prepare("UPDATE `cars` SET `status` = ?, `reserved_until` = ? WHERE `id` = ?")->execute([$new_status, $res_until, $id]);
            if ($new_status !== 'RESERVED') {
                $pdo->prepare("UPDATE `reservations` SET `status` = ? WHERE `car_id` = ? AND `status` = 'ACTIVE'")
                    ->execute([$new_status === 'SOLD' ? 'COMPLETED' : 'CANCELLED', $id]);
            }
            $_SESSION['flash'] = "Vehicle #{$id} status updated to {$new_status}.";
            header("Location: index.php?tab=" . ($new_status === 'SOLD' ? 'sold' : 'cars'));
            exit;
        }
    }

    if ($action === 'mark_sold') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE `cars` SET `status` = 'SOLD', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ?")->execute([$id]);
            $pdo->prepare("UPDATE `reservations` SET `status` = 'COMPLETED' WHERE `car_id` = ? AND `status` = 'ACTIVE'")->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} marked as SOLD and archived.";
        }
        header("Location: index.php?tab=sold");
        exit;
    }

    if ($action === 'transfer_to_inventory') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE `cars` SET `status` = 'AVAILABLE', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ?")->execute([$id]);
            $_SESSION['flash'] = "Vehicle #{$id} restored to AVAILABLE showroom stock.";
        }
        header("Location: index.php?tab=cars");
        exit;
    }

    if ($action === 'toggle_recommended') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $pdo->prepare("UPDATE `cars` SET `is_recommended` = 1 - `is_recommended` WHERE `id` = ?")->execute([$id]);
            $_SESSION['flash'] = "Showroom pin toggled for vehicle #{$id}.";
        }
        header("Location: index.php?tab=" . urlencode($current_tab));
        exit;
    }

    // ================= 3. RESERVATION HOLD MANAGEMENT ACTIONS =================
    if ($action === 'complete_reservation') {
        $res_id = intval($_POST['res_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE `id` = ?");
        $stmt->execute([$res_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            $pdo->prepare("UPDATE `reservations` SET `status` = 'COMPLETED' WHERE `id` = ?")->execute([$res_id]);
            $pdo->prepare("UPDATE `cars` SET `status` = 'SOLD', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ?")->execute([$res['car_id']]);
            $_SESSION['flash'] = "Reservation {$res['booking_ref']} finalized as SOLD!";
        }
        header("Location: index.php?tab=reservations");
        exit;
    }

    if ($action === 'extend_reservation') {
        $res_id = intval($_POST['res_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE `id` = ?");
        $stmt->execute([$res_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res && strtoupper($res['status']) === 'ACTIVE') {
            $new_exp = date('Y-m-d H:i:s', strtotime($res['expires_at']) + 86400);
            $new_days = intval($res['hold_days']) + 1;
            $pdo->prepare("UPDATE `reservations` SET `expires_at` = ?, `hold_days` = ? WHERE `id` = ?")->execute([$new_exp, $new_days, $res_id]);
            $pdo->prepare("UPDATE `cars` SET `reserved_until` = ? WHERE `id` = ?")->execute([$new_exp, $res['car_id']]);
            $_SESSION['flash'] = "Extended hold {$res['booking_ref']} by +24 hours (new deadline: " . date('M j, Y g:i A', strtotime($new_exp)) . ").";
        }
        header("Location: index.php?tab=reservations");
        exit;
    }

    if ($action === 'cancel_reservation_admin') {
        $res_id = intval($_POST['res_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE `id` = ?");
        $stmt->execute([$res_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            $pdo->prepare("UPDATE `reservations` SET `status` = 'CANCELLED' WHERE `id` = ?")->execute([$res_id]);
            $pdo->prepare("UPDATE `cars` SET `status` = 'AVAILABLE', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ? AND `status` = 'RESERVED'")->execute([$res['car_id']]);
            $_SESSION['flash'] = "Reservation {$res['booking_ref']} released — vehicle is now AVAILABLE.";
        }
        header("Location: index.php?tab=reservations");
        exit;
    }

    // ================= 4. SAVE ECMS WEBSITE CONTENT & POLICIES (Add-on E) =================
    if ($action === 'save_ecms_settings') {
        $allowed_keys = array_keys(uniwheels_default_settings());
        $upsert = $pdo->prepare("INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`)");
        foreach ($allowed_keys as $k) {
            if (isset($_POST['ecms_' . $k])) {
                $upsert->execute([$k, trim((string)$_POST['ecms_' . $k])]);
            }
        }
        $_SESSION['flash'] = "Website ECMS content, hold rules, and dealership policies saved live!";
        header("Location: index.php?tab=ecms");
        exit;
    }
}

$flash = $_SESSION['flash'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash'], $_SESSION['flash_error']);

// Fetch Fleet, Reservations & Analytics
$all_cars = [];
$all_reservations = [];
$active_holds_count = 0;
$stats = [
    'total'           => 0,
    'available'       => 0,
    'reserved'        => 0,
    'maintenance'     => 0,
    'sold'            => 0,
    'total_valuation' => 0,
    'avg_price'       => 0
];
$all_existing_brands = [];
$all_existing_agents = [];
$brand_distribution = [];
$body_distribution = [];

if ($pdo) {
    $all_cars = $pdo->query("SELECT * FROM `cars` ORDER BY `id` ASC")->fetchAll(PDO::FETCH_ASSOC);
    $all_reservations = $pdo->query("
        SELECT r.*, c.name AS car_name, c.stock_id, c.image_file, c.agent_owner
        FROM `reservations` r
        LEFT JOIN `cars` c ON c.id = r.car_id
        ORDER BY FIELD(r.status, 'ACTIVE', 'COMPLETED', 'EXPIRED', 'CANCELLED'), r.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_reservations as $r) {
        if (strtoupper($r['status']) === 'ACTIVE') $active_holds_count++;
    }

    $avail_prices = [];
    foreach ($all_cars as $c) {
        $stats['total']++;
        $st = strtoupper($c['status'] ?? 'AVAILABLE');
        $p = floatval($c['price'] ?? 0);
        $b = $c['brand'] ?: 'Other';
        $body = $c['body_type'] ?: 'Sedan';
        $ag = trim($c['agent_owner'] ?? '');
        if ($ag !== '' && !in_array($ag, $all_existing_agents, true)) {
            $all_existing_agents[] = $ag;
        }

        if ($st === 'AVAILABLE') {
            $stats['available']++;
            $stats['total_valuation'] += $p;
            if ($p > 0) $avail_prices[] = $p;
        } elseif ($st === 'RESERVED') {
            $stats['reserved']++;
            $stats['total_valuation'] += $p;
        } elseif ($st === 'UNDER MAINTENANCE') {
            $stats['maintenance']++;
            $stats['total_valuation'] += $p;
        } elseif ($st === 'SOLD') {
            $stats['sold']++;
        }

        if (!isset($brand_distribution[$b])) $brand_distribution[$b] = ['count' => 0, 'valuation' => 0];
        $brand_distribution[$b]['count']++;
        $brand_distribution[$b]['valuation'] += $p;

        if (!isset($body_distribution[$body])) $body_distribution[$body] = 0;
        $body_distribution[$body]++;
    }

    if (count($avail_prices) > 0) {
        $stats['avg_price'] = array_sum($avail_prices) / count($avail_prices);
    }

    uasort($brand_distribution, fn($a, $b) => $b['count'] <=> $a['count']);
    arsort($body_distribution);
    sort($all_existing_agents);

    $all_existing_brands = $pdo->query("SELECT DISTINCT `brand` FROM `cars` WHERE `brand` IS NOT NULL AND `brand` != '' ORDER BY `brand` ASC")->fetchAll(PDO::FETCH_COLUMN);
}

$search_query = trim($_GET['search'] ?? '');
$filter_brand = trim($_GET['brand'] ?? 'all');
$filter_car_status = strtoupper(trim($_GET['status'] ?? 'ALL'));
$filter_agent = trim($_GET['agent'] ?? 'all');

$active_cars = [];
$sold_cars = [];
foreach ($all_cars as $c) {
    if (strtoupper($c['status'] ?? 'AVAILABLE') === 'SOLD') {
        $sold_cars[] = $c;
    } else {
        $active_cars[] = $c;
    }
}

$filtered_cars = array_filter($active_cars, function($c) use ($search_query, $filter_brand, $filter_car_status, $filter_agent) {
    if ($search_query !== '') {
        $q = strtolower($search_query);
        $haystack = strtolower(($c['name'] ?? '') . ' ' . ($c['brand'] ?? '') . ' ' . ($c['stock_id'] ?? '') . ' ' . ($c['plate_number'] ?? '') . ' ' . ($c['agent_owner'] ?? '') . ' ' . ($c['body_type'] ?? ''));
        if (strpos($haystack, $q) === false) return false;
    }
    if ($filter_brand !== 'all' && strcasecmp($c['brand'] ?? '', $filter_brand) !== 0) return false;
    if ($filter_car_status !== 'ALL' && strcasecmp($c['status'] ?? '', $filter_car_status) !== 0) return false;
    if ($filter_agent !== 'all' && strcasecmp($c['agent_owner'] ?? '', $filter_agent) !== 0) return false;
    return true;
});

$filtered_sold_cars = array_filter($sold_cars, function($c) use ($search_query, $filter_brand) {
    if ($search_query !== '') {
        $q = strtolower($search_query);
        $haystack = strtolower(($c['name'] ?? '') . ' ' . ($c['brand'] ?? '') . ' ' . ($c['stock_id'] ?? '') . ' ' . ($c['plate_number'] ?? ''));
        if (strpos($haystack, $q) === false) return false;
    }
    if ($filter_brand !== 'all' && strcasecmp($c['brand'] ?? '', $filter_brand) !== 0) return false;
    return true;
});
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniWheels ECMS Admin — Inventory, Reservations &amp; Policies</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
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
      --font-heading: 'Outfit', sans-serif;
      --font-body: 'Inter', sans-serif;
      --sidebar-width: 260px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-body); background: var(--bg-page); color: var(--text-body); min-height: 100vh; display: flex; }
    a { color: inherit; text-decoration: none; }

    /* Sidebar */
    .admin-sidebar { width: var(--sidebar-width); background: var(--bg-sidebar); color: #f8fafc; display: flex; flex-direction: column; position: fixed; inset: 0 auto 0 0; z-index: 100; }
    .sidebar-header { padding: 1.4rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 12px; }
    .brand-logo-small { height: 38px; width: auto; }
    .brand-title-text { font-family: var(--font-heading); font-size: 1.15rem; font-weight: 900; color: #fff; }
    .brand-title-text span { color: #ef4444; }
    .brand-sub-badge { font-size: 0.65rem; font-weight: 700; color: #94a3b8; letter-spacing: 0.08em; }
    .sidebar-nav { padding: 1.15rem 0.75rem; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .nav-label { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; padding: 0.6rem 0.75rem 0.25rem; }
    .nav-item { display: flex; align-items: center; gap: 10px; padding: 0.72rem 0.85rem; border-radius: 8px; color: #cbd5e1; font-size: 0.9rem; font-weight: 600; transition: 0.15s; }
    .nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
    .nav-item.active { background: var(--brand-blue); color: #fff; }
    .nav-badge { margin-left: auto; font-size: 0.72rem; font-weight: 800; background: rgba(255,255,255,0.18); padding: 2px 7px; border-radius: 10px; }
    .nav-badge-warn { background: #f59e0b; color: #fff; }
    .sidebar-footer { padding: 1.15rem; border-top: 1px solid rgba(255,255,255,0.08); background: rgba(0,0,0,0.2); }
    .user-profile-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 0.75rem; }
    .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #fff; }
    .user-meta-name { font-size: 0.88rem; font-weight: 700; color: #fff; }
    .user-meta-role { font-size: 0.72rem; color: #94a3b8; text-transform: capitalize; }
    .sidebar-actions-row { display: flex; justify-content: space-between; font-size: 0.8rem; }
    .link-live-site { color: #60a5fa; font-weight: 600; }
    .link-logout { color: #f87171; font-weight: 600; }

    /* Main Content */
    .admin-main { margin-left: var(--sidebar-width); flex: 1; padding: 1.85rem 2.25rem; max-width: 1460px; }
    .top-action-bar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title-box h1 { font-family: var(--font-heading); font-size: 1.7rem; font-weight: 800; color: var(--text-main); margin-bottom: 3px; }
    .page-title-box p { font-size: 0.9rem; color: var(--text-muted); }

    .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: 700; cursor: pointer; border: 1px solid transparent; font-family: inherit; transition: 0.15s; }
    .btn-primary { background: var(--brand-blue); color: #fff; }
    .btn-primary:hover { background: var(--brand-blue-hover); }
    .btn-success { background: #16a34a; color: #fff; }
    .btn-success:hover { background: #15803d; }
    .btn-outline { background: #fff; border-color: var(--border-dark); color: var(--text-body); }
    .btn-outline:hover { background: var(--bg-subtle); }

    .alert-banner { padding: 12px 16px; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.9rem; }
    .alert-banner.success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-banner.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
    .metric-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; }
    .metric-label { font-size: 0.74rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 5px; }
    .metric-value { font-family: var(--font-heading); font-size: 1.75rem; font-weight: 900; color: var(--text-main); line-height: 1.1; margin-bottom: 4px; }
    .metric-sub { font-size: 0.78rem; color: var(--text-muted); }

    .content-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; margin-bottom: 1.75rem; }
    .content-card-header { padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; background: #fafbfc; flex-wrap: wrap; gap: 8px; }
    .card-heading { font-family: var(--font-heading); font-size: 1.1rem; font-weight: 800; color: var(--text-main); }
    .table-container { overflow-x: auto; }
    .clean-table { width: 100%; border-collapse: collapse; font-size: 0.87rem; text-align: left; }
    .clean-table th { background: #f8fafc; color: #475569; font-size: 0.74rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; padding: 11px 14px; border-bottom: 1px solid var(--border-color); }
    .clean-table td { padding: 12px 14px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .clean-table tr:hover td { background: #f8fafc; }

    .car-table-cell { display: flex; align-items: center; gap: 11px; }
    .car-table-thumb { width: 62px; height: 42px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-subtle); }
    .car-table-name { font-weight: 700; color: var(--text-main); }
    .car-table-stock { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }

    .badge { display: inline-block; font-size: 0.71rem; font-weight: 800; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; }
    .badge-available { background: #dcfce7; color: #15803d; }
    .badge-reserved { background: #fef3c7; color: #b45309; }
    .badge-under-maintenance { background: #f3e8ff; color: #6b21a8; }
    .badge-sold { background: #f1f5f9; color: #64748b; }
    .badge-agent { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; text-transform: none; }

    .status-dropdown { height: 30px; padding: 0 8px; border-radius: 6px; font-size: 0.76rem; font-weight: 800; border: 1px solid var(--border-dark); cursor: pointer; font-family: inherit; }
    .table-actions { display: inline-flex; align-items: center; gap: 5px; flex-wrap: wrap; }
    .btn-action-sm { padding: 5px 9px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; background: #fff; border: 1px solid var(--border-dark); color: var(--text-body); cursor: pointer; }
    .btn-action-sm:hover { border-color: var(--brand-blue); color: var(--brand-blue); }
    .btn-action-view { background: #eff6ff; border-color: #bfdbfe; color: var(--brand-blue); }
    .btn-action-sold { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }
    .btn-action-danger { border-color: #fca5a5; color: #dc2626; }

    .inventory-toolbar-card { background: #fff; border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 16px; margin-bottom: 1.25rem; }
    .inventory-search-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .form-input, .form-select, textarea.form-textarea { width: 100%; border: 1px solid var(--border-dark); border-radius: 8px; padding: 8px 12px; font-size: 0.9rem; font-family: inherit; color: var(--text-main); background: #fff; }
    .form-input, .form-select { height: 40px; padding: 0 11px; }
    textarea.form-textarea { min-height: 78px; resize: vertical; line-height: 1.45; }
    .form-label { display: block; font-size: 0.82rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px; }
    .form-group { margin-bottom: 1rem; }
    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }

    /* Modals */
    .admin-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.62); backdrop-filter: blur(3px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1.25rem; }
    .admin-modal-card { background: #fff; border-radius: 12px; max-width: 820px; width: 100%; max-height: 92vh; overflow-y: auto; box-shadow: 0 20px 30px rgba(0,0,0,0.18); }
    .admin-modal-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: #fff; z-index: 5; }
    .admin-modal-title { font-family: var(--font-heading); font-size: 1.2rem; font-weight: 800; color: var(--text-main); }
    .admin-modal-close { font-size: 1.7rem; color: var(--text-muted); cursor: pointer; line-height: 1; }
    .admin-modal-body { padding: 1.4rem 1.5rem; }
    .admin-modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background: var(--bg-subtle); display: flex; justify-content: flex-end; gap: 8px; }
    .section-box { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
    .section-box-title { font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
  </style>
</head>
<body>

  <!-- ================= SIDEBAR NAVIGATION ================= -->
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <img src="../assets/images/logo.png" alt="UniWheels Logo" class="brand-logo-small">
      <div>
        <div class="brand-title-text">UNI<span>WHEELS</span></div>
        <div class="brand-sub-badge">ECMS ADMIN PORTAL</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Operations &amp; ECMS</div>

      <a href="index.php?tab=dashboard" class="nav-item <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
        <span>📊 Dashboard &amp; Analytics</span>
      </a>

      <a href="index.php?tab=cars" class="nav-item <?php echo $current_tab === 'cars' ? 'active' : ''; ?>">
        <span>🚗 Vehicle Inventory</span>
        <span class="nav-badge"><?php echo count($active_cars); ?></span>
      </a>

      <a href="index.php?tab=reservations" class="nav-item <?php echo $current_tab === 'reservations' ? 'active' : ''; ?>">
        <span>⏳ Reservation Holds</span>
        <span class="nav-badge <?php echo $active_holds_count > 0 ? 'nav-badge-warn' : ''; ?>"><?php echo $active_holds_count; ?></span>
      </a>

      <a href="index.php?tab=sold" class="nav-item <?php echo $current_tab === 'sold' ? 'active' : ''; ?>">
        <span>✅ Sold Archive</span>
        <span class="nav-badge"><?php echo count($sold_cars); ?></span>
      </a>

      <div class="nav-label" style="margin-top:8px;">Content Management</div>

      <a href="index.php?tab=ecms" class="nav-item <?php echo $current_tab === 'ecms' ? 'active' : ''; ?>">
        <span>⚙️ Website ECMS &amp; Policies</span>
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

  <!-- ================= MAIN ADMIN WORKSPACE ================= -->
  <main class="admin-main">

    <?php if (!empty($flash)): ?>
      <div class="alert-banner success"><strong>Success:</strong> <?php echo htmlspecialchars($flash); ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert-banner error"><strong>Error:</strong> <?php echo htmlspecialchars($flash_error); ?></div>
    <?php endif; ?>

    <!-- =========================================================
         TAB 1: DASHBOARD & FLEET ANALYTICS
         ========================================================= -->
    <?php if ($current_tab === 'dashboard'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Dealership Operations &amp; Fleet Overview</h1>
          <p>Real-time November 2026 showroom inventory, active customer holds, and financial valuation.</p>
        </div>
        <div style="display:flex; gap:8px;">
          <a href="index.php?tab=ecms" class="btn btn-outline">⚙️ Edit Website Policies (ECMS)</a>
          <button class="btn btn-primary" onclick="openCreateModal()">+ Add Vehicle</button>
        </div>
      </div>

      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-label">Total Fleet Units</div>
          <div class="metric-value"><?php echo $stats['total']; ?></div>
          <div class="metric-sub">Nov 2026 Price List Inventory</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Available Now</div>
          <div class="metric-value" style="color:#16a34a;"><?php echo $stats['available']; ?></div>
          <div class="metric-sub">Ready for booking or walk-in</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Reserved / On Hold</div>
          <div class="metric-value" style="color:#d97706;"><?php echo $stats['reserved']; ?></div>
          <div class="metric-sub">Auto-releases when timer expires</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Under Maintenance</div>
          <div class="metric-value" style="color:#7e22ce;"><?php echo $stats['maintenance']; ?></div>
          <div class="metric-sub">Detailing / LTO renewal prep</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Active Floor Valuation</div>
          <div class="metric-value" style="color:#2563eb; font-size:1.45rem;">₱<?php echo number_format($stats['total_valuation'], 0); ?></div>
          <div class="metric-sub">Avg ₱<?php echo number_format($stats['avg_price'], 0); ?> / unit</div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:1.5rem;">
        <div class="content-card">
          <div class="content-card-header">
            <h2 class="card-heading">Inventory by Brand (Top Makes)</h2>
            <small style="color:#64748b; font-weight:600;"><?php echo count($brand_distribution); ?> Brands</small>
          </div>
          <div style="padding:1.25rem 1.4rem;">
            <?php foreach (array_slice($brand_distribution, 0, 10, true) as $b_name => $b_data):
              $pct = $stats['total'] > 0 ? round(($b_data['count'] / $stats['total']) * 100) : 0;
            ?>
              <div style="margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; font-size:0.84rem; font-weight:700; margin-bottom:4px;">
                  <span><?php echo htmlspecialchars($b_name); ?> (<?php echo $b_data['count']; ?>)</span>
                  <span style="color:#2563eb;">₱<?php echo number_format($b_data['valuation'], 0); ?> &bull; <?php echo $pct; ?>%</span>
                </div>
                <div style="width:100%; height:7px; background:#f1f5f9; border-radius:4px; overflow:hidden;">
                  <div style="width:<?php echo max(2, $pct); ?>%; height:100%; background:#3b82f6;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="content-card">
          <div class="content-card-header">
            <h2 class="card-heading">Showroom Categories</h2>
            <small style="color:#64748b; font-weight:600;">5 Price List Segments</small>
          </div>
          <div style="padding:1.25rem 1.4rem; display:flex; flex-direction:column; gap:10px;">
            <?php foreach ($body_distribution as $body_name => $b_count):
              $b_pct = $stats['total'] > 0 ? round(($b_count / $stats['total']) * 100) : 0;
            ?>
              <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
                <div>
                  <strong style="color:var(--text-main); font-size:0.9rem;"><?php echo htmlspecialchars($body_name); ?></strong>
                  <div style="font-size:0.74rem; color:#64748b;"><?php echo $b_pct; ?>% of fleet</div>
                </div>
                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:0.82rem;"><?php echo $b_count; ?> Units</span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- =========================================================
         TAB 2: ACTIVE VEHICLE INVENTORY (WITH ADMIN-ONLY AGENT COLUMN)
         ========================================================= -->
    <?php if ($current_tab === 'cars'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Vehicle Inventory &amp; Condition Profiles</h1>
          <p>Update prices, vehicle age, ownership, damage disclosures, real-time availability, and Admin-only agent records.</p>
        </div>
        <button class="btn btn-primary" onclick="openCreateModal()">+ Add New Vehicle</button>
      </div>

      <div class="inventory-toolbar-card">
        <form method="GET" action="index.php" class="inventory-search-form">
          <input type="hidden" name="tab" value="cars">
          <div style="flex:1; min-width:220px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search model, stock #, plate #, color, or agent..." class="form-input">
          </div>
          <div style="width:150px;">
            <select name="brand" class="form-select">
              <option value="all">All Brands</option>
              <?php foreach ($all_existing_brands as $b): ?>
                <option value="<?php echo htmlspecialchars($b); ?>" <?php echo ($filter_brand === $b) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="width:175px;">
            <select name="status" class="form-select">
              <option value="ALL">All Active Statuses</option>
              <option value="AVAILABLE" <?php echo ($filter_car_status === 'AVAILABLE') ? 'selected' : ''; ?>>Available</option>
              <option value="RESERVED" <?php echo ($filter_car_status === 'RESERVED') ? 'selected' : ''; ?>>Reserved / On Hold</option>
              <option value="UNDER MAINTENANCE" <?php echo ($filter_car_status === 'UNDER MAINTENANCE') ? 'selected' : ''; ?>>Under Maintenance</option>
            </select>
          </div>
          <div style="width:165px;">
            <select name="agent" class="form-select" title="Filter by Agent / Consignor (Admin-Only)">
              <option value="all">All Agents (Admin)</option>
              <?php foreach ($all_existing_agents as $ag): ?>
                <option value="<?php echo htmlspecialchars($ag); ?>" <?php echo ($filter_agent === $ag) ? 'selected' : ''; ?>><?php echo htmlspecialchars($ag); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Filter</button>
          <?php if ($search_query !== '' || $filter_brand !== 'all' || $filter_car_status !== 'ALL' || $filter_agent !== 'all'): ?>
            <a href="index.php?tab=cars" class="btn btn-outline">Reset</a>
          <?php endif; ?>
        </form>
      </div>

      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">Showing <?php echo count($filtered_cars); ?> Active Vehicles</h2>
          <small style="color:#64748b; font-weight:600;">🔒 &ldquo;Agent / Owner&rdquo; column is visible to Staff/Admin only (hidden from public site)</small>
        </div>

        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Vehicle, Age &amp; Plate</th>
                <th>Selling Price &amp; Deposit</th>
                <th>Condition &amp; History</th>
                <th>Agent / Owner 🔒</th>
                <th>Availability Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filtered_cars)): ?>
                <tr>
                  <td colspan="6" style="text-align:center; padding:2.5rem; color:#64748b;">No vehicles matched your search criteria.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($filtered_cars as $c):
                  $ageInfo = uniwheels_compute_vehicle_age($c['year_model'] ?? 0, $c['name'] ?? '', $ref_year);
                  $st_upper = strtoupper($c['status'] ?? 'AVAILABLE');
                  $st_slug = strtolower(str_replace(' ', '-', $st_upper));
                ?>
                  <tr>
                    <td>
                      <div class="car-table-cell">
                        <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file'] ?: 'car-1.jpg'); ?>" class="car-table-thumb" alt="" loading="lazy" onerror="this.src='../assets/images/cars/car-1.jpg'">
                        <div>
                          <div class="car-table-name"><?php echo htmlspecialchars($c['name']); ?> <span style="color:#2563eb; font-size:0.78rem;">(<?php echo htmlspecialchars($ageInfo['short_age']); ?>)</span></div>
                          <div class="car-table-stock">
                            #<?php echo htmlspecialchars($c['stock_id'] ?: ('UW-' . $c['id'])); ?>
                            <?php if (!empty($c['plate_number'])): ?> &bull; Plate: <strong><?php echo htmlspecialchars($c['plate_number']); ?></strong><?php endif; ?>
                            &bull; <?php echo htmlspecialchars($c['mileage'] ?? ''); ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <strong>₱<?php echo number_format(floatval($c['price']), 0); ?></strong>
                      <div style="font-size:0.74rem; color:#64748b;"><?php echo htmlspecialchars($c['price_note'] ?? 'Negotiable'); ?> &bull; Dep: ₱<?php echo number_format(floatval($c['deposit_amount'] ?: 10000), 0); ?></div>
                    </td>
                    <td style="max-width:230px;">
                      <div style="font-size:0.79rem; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($c['ownership_history'] ?: '1 previous owner'); ?></div>
                      <div style="font-size:0.73rem; color:#15803d; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?php echo htmlspecialchars($c['damage_disclosure'] ?? ''); ?>">
                        <?php echo !empty($c['damage_disclosure']) ? '✓ Damage report documented' : 'Standard condition'; ?>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-agent"><?php echo htmlspecialchars($c['agent_owner'] ?: 'Boss'); ?></span>
                    </td>
                    <td>
                      <form method="POST" action="index.php?tab=cars" onsubmit="return confirm('Change availability status of <?php echo htmlspecialchars(addslashes($c['name'])); ?>?');">
                        <input type="hidden" name="action" value="change_status">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <select name="status" class="status-dropdown badge-<?php echo htmlspecialchars($st_slug); ?>" onchange="this.form.submit()">
                          <option value="AVAILABLE" <?php echo $st_upper === 'AVAILABLE' ? 'selected' : ''; ?>>AVAILABLE</option>
                          <option value="RESERVED" <?php echo $st_upper === 'RESERVED' ? 'selected' : ''; ?>>RESERVED</option>
                          <option value="UNDER MAINTENANCE" <?php echo $st_upper === 'UNDER MAINTENANCE' ? 'selected' : ''; ?>>MAINTENANCE</option>
                          <option value="SOLD" <?php echo $st_upper === 'SOLD' ? 'selected' : ''; ?>>SOLD</option>
                        </select>
                      </form>
                      <?php if ($st_upper === 'RESERVED' && !empty($c['reserved_until'])): ?>
                        <div style="font-size:0.7rem; color:#b45309; margin-top:3px;">Until <?php echo date('M j, g:i A', strtotime($c['reserved_until'])); ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="table-actions" style="justify-content:flex-end;">
                        <button type="button" class="btn-action-sm" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, "UTF-8"); ?>)'>Edit</button>
                        <form method="POST" action="index.php?tab=cars" style="display:inline;" onsubmit="return confirm('Mark <?php echo htmlspecialchars(addslashes($c['name'])); ?> as SOLD?');">
                          <input type="hidden" name="action" value="mark_sold">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-sold">Sold</button>
                        </form>
                        <form method="POST" action="index.php?tab=cars" style="display:inline;" onsubmit="return confirm('Permanently delete <?php echo htmlspecialchars(addslashes($c['name'])); ?>?');">
                          <input type="hidden" name="action" value="delete_car">
                          <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                          <button type="submit" class="btn-action-sm btn-action-danger">Delete</button>
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

    <!-- =========================================================
         TAB 3: RESERVATION HOLDS & DEADLINE MANAGER (Add-ons B & C)
         ========================================================= -->
    <?php if ($current_tab === 'reservations'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Customer Reservation Holds &amp; Auto-Release</h1>
          <p>Monitor live customer holds, booking references, deposits, and expiration deadlines. Expired holds release automatically.</p>
        </div>
      </div>

      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">All Reservation Records (<?php echo count($all_reservations); ?>)</h2>
          <small style="color:#64748b; font-weight:600;"><?php echo $active_holds_count; ?> Active Hold(s) Currently Locking Units</small>
        </div>

        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Booking Ref</th>
                <th>Vehicle</th>
                <th>Customer Contact</th>
                <th>Hold Duration &amp; Deadline</th>
                <th>Price &amp; Deposit</th>
                <th>Status</th>
                <th style="text-align:right;">Staff Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($all_reservations)): ?>
                <tr>
                  <td colspan="7" style="text-align:center; padding:2.5rem; color:#64748b;">No customer reservations recorded yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($all_reservations as $r):
                  $r_st = strtoupper($r['status']);
                  $badge_cls = ($r_st === 'ACTIVE') ? 'badge-reserved' : (($r_st === 'COMPLETED') ? 'badge-available' : 'badge-sold');
                ?>
                  <tr>
                    <td><strong style="font-family:monospace; color:#1d4ed8;"><?php echo htmlspecialchars($r['booking_ref']); ?></strong></td>
                    <td>
                      <strong><?php echo htmlspecialchars($r['car_name'] ?: ('Car #' . $r['car_id'])); ?></strong>
                      <div style="font-size:0.74rem; color:#64748b;">#<?php echo htmlspecialchars($r['stock_id'] ?? ''); ?> <?php if (!empty($r['agent_owner'])): ?>&bull; Agent: <?php echo htmlspecialchars($r['agent_owner']); ?><?php endif; ?></div>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($r['customer_name']); ?></strong>
                      <div style="font-size:0.75rem; color:#64748b;"><?php echo htmlspecialchars($r['customer_phone']); ?> &bull; <?php echo htmlspecialchars($r['customer_email']); ?></div>
                      <?php if (!empty($r['notes'])): ?>
                        <div style="font-size:0.73rem; color:#0369a1; margin-top:2px;">Note: <?php echo htmlspecialchars($r['notes']); ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <strong><?php echo intval($r['hold_days']); ?> Day<?php echo intval($r['hold_days']) === 1 ? '' : 's'; ?></strong>
                      <div style="font-size:0.75rem; color:#b45309;">Due: <?php echo date('M j, Y g:i A', strtotime($r['expires_at'])); ?></div>
                    </td>
                    <td>
                      <strong>₱<?php echo number_format(floatval($r['car_price']), 0); ?></strong>
                      <div style="font-size:0.75rem; color:#15803d;">Deposit: ₱<?php echo number_format(floatval($r['deposit_amount']), 0); ?></div>
                    </td>
                    <td><span class="badge <?php echo $badge_cls; ?>"><?php echo htmlspecialchars($r_st); ?></span></td>
                    <td>
                      <?php if ($r_st === 'ACTIVE'): ?>
                        <div class="table-actions" style="justify-content:flex-end;">
                          <form method="POST" action="index.php?tab=reservations" onsubmit="return confirm('Finalize booking <?php echo htmlspecialchars($r['booking_ref']); ?> and mark vehicle as SOLD?');">
                            <input type="hidden" name="action" value="complete_reservation">
                            <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn-action-sm btn-action-sold">Approve &amp; Mark Sold</button>
                          </form>
                          <form method="POST" action="index.php?tab=reservations" onsubmit="return confirm('Extend reservation hold by +24 hours?');">
                            <input type="hidden" name="action" value="extend_reservation">
                            <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn-action-sm btn-action-view">+1 Day</button>
                          </form>
                          <form method="POST" action="index.php?tab=reservations" onsubmit="return confirm('Are you sure you want to cancel this reservation and release the vehicle back to AVAILABLE?');">
                            <input type="hidden" name="action" value="cancel_reservation_admin">
                            <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                            <button type="submit" class="btn-action-sm btn-action-danger">Release Hold</button>
                          </form>
                        </div>
                      <?php else: ?>
                        <div style="text-align:right; font-size:0.76rem; color:#94a3b8;">Closed</div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- =========================================================
         TAB 4: SOLD VEHICLES ARCHIVE
         ========================================================= -->
    <?php if ($current_tab === 'sold'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Sold Vehicles Archive</h1>
          <p>Completed vehicle sales. Restore any unit back to active inventory if a transaction is cancelled.</p>
        </div>
      </div>

      <div class="content-card">
        <div class="content-card-header">
          <h2 class="card-heading">Sold Units (<?php echo count($filtered_sold_cars); ?>)</h2>
        </div>
        <div class="table-container">
          <table class="clean-table">
            <thead>
              <tr>
                <th>Vehicle &amp; Stock</th>
                <th>Sold Price</th>
                <th>Agent / Owner 🔒</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($filtered_sold_cars)): ?>
                <tr>
                  <td colspan="5" style="text-align:center; padding:2.5rem; color:#64748b;">No vehicles marked as SOLD yet.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($filtered_sold_cars as $c): ?>
                  <tr>
                    <td>
                      <div class="car-table-cell">
                        <img src="../assets/images/cars/<?php echo htmlspecialchars($c['image_file'] ?: 'car-1.jpg'); ?>" class="car-table-thumb" alt="">
                        <div>
                          <div class="car-table-name"><?php echo htmlspecialchars($c['name']); ?></div>
                          <div class="car-table-stock">#<?php echo htmlspecialchars($c['stock_id'] ?? ''); ?> &bull; <?php echo htmlspecialchars($c['plate_number'] ?? ''); ?></div>
                        </div>
                      </div>
                    </td>
                    <td><strong>₱<?php echo number_format(floatval($c['price']), 0); ?></strong></td>
                    <td><span class="badge badge-agent"><?php echo htmlspecialchars($c['agent_owner'] ?: 'Boss'); ?></span></td>
                    <td><span class="badge badge-sold">SOLD</span></td>
                    <td style="text-align:right;">
                      <form method="POST" action="index.php?tab=sold" style="display:inline;" onsubmit="return confirm('Restore <?php echo htmlspecialchars(addslashes($c['name'])); ?> back to AVAILABLE inventory?');">
                        <input type="hidden" name="action" value="transfer_to_inventory">
                        <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                        <button type="submit" class="btn-action-sm btn-action-view">Restore to Available</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <!-- =========================================================
         TAB 5: WEBSITE ECMS & POLICIES EDITOR (Add-on E)
         ========================================================= -->
    <?php if ($current_tab === 'ecms'): ?>
      <div class="top-action-bar">
        <div class="page-title-box">
          <h1>Editable Content Management System (ECMS)</h1>
          <p>Update website headlines, reservation deposit rules, hold limits, dealership policies, and contact info without touching website code.</p>
        </div>
      </div>

      <form method="POST" action="index.php?tab=ecms">
        <input type="hidden" name="action" value="save_ecms_settings">

        <div class="content-card">
          <div class="content-card-header">
            <h2 class="card-heading">1. Homepage Headlines &amp; Announcement Banner</h2>
          </div>
          <div style="padding:1.4rem;">
            <div class="form-group">
              <label class="form-label">Top Announcement Banner (Leave blank to hide)</label>
              <input type="text" name="ecms_announcement_banner" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['announcement_banner']); ?>">
            </div>
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Hero Main Title Prefix</label>
                <input type="text" name="ecms_hero_title_prefix" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['hero_title_prefix']); ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Hero Highlighted Phrase (Blue Text)</label>
                <input type="text" name="ecms_hero_title_highlight" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['hero_title_highlight']); ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Hero Lead Subtitle</label>
              <textarea name="ecms_hero_subtitle" class="form-textarea"><?php echo htmlspecialchars($ecms_settings['hero_subtitle']); ?></textarea>
            </div>
            <div class="form-grid-3">
              <div class="form-group">
                <label class="form-label">Catalog Section Tag</label>
                <input type="text" name="ecms_catalog_tag" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['catalog_tag']); ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Catalog Section Heading</label>
                <input type="text" name="ecms_catalog_title" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['catalog_title']); ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Reference Year (for Vehicle Age)</label>
                <input type="number" name="ecms_reference_year" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['reference_year']); ?>">
              </div>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Catalog Subtitle</label>
              <input type="text" name="ecms_catalog_subtitle" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['catalog_subtitle']); ?>">
            </div>
          </div>
        </div>

        <div class="content-card">
          <div class="content-card-header">
            <h2 class="card-heading">2. Reservation Hold Parameters &amp; Customer Policies</h2>
          </div>
          <div style="padding:1.4rem;">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Default Reservation Deposit (₱)</label>
                <input type="number" step="500" name="ecms_default_deposit" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['default_deposit']); ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Maximum Custom Hold Duration (Days)</label>
                <input type="number" min="1" max="30" name="ecms_max_hold_days" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['max_hold_days']); ?>">
              </div>
            </div>
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Policy 1: Reservation Hold &amp; Deposit Terms</label>
                <textarea name="ecms_policy_reservation" class="form-textarea"><?php echo htmlspecialchars($ecms_settings['policy_reservation']); ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Policy 2: Countdown &amp; Automatic Release Policy</label>
                <textarea name="ecms_policy_release" class="form-textarea"><?php echo htmlspecialchars($ecms_settings['policy_release']); ?></textarea>
              </div>
            </div>
            <div class="form-grid-2">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Policy 3: Vehicle Condition &amp; Damage Disclosure Policy</label>
                <textarea name="ecms_policy_condition" class="form-textarea"><?php echo htmlspecialchars($ecms_settings['policy_condition']); ?></textarea>
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Policy 4: Clean LTO Papers &amp; Inspection Guarantee</label>
                <textarea name="ecms_policy_papers" class="form-textarea"><?php echo htmlspecialchars($ecms_settings['policy_papers']); ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="content-card">
          <div class="content-card-header">
            <h2 class="card-heading">3. Showroom Contact Details &amp; Operating Hours</h2>
          </div>
          <div style="padding:1.4rem;">
            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label">Showroom Street Address</label>
                <input type="text" name="ecms_contact_address" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['contact_address']); ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Operating Hours</label>
                <input type="text" name="ecms_contact_hours" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['contact_hours']); ?>">
              </div>
            </div>
            <div class="form-grid-3">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Phone &amp; Mobile Numbers</label>
                <input type="text" name="ecms_contact_phone" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['contact_phone']); ?>">
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Showroom Email</label>
                <input type="email" name="ecms_contact_email" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['contact_email']); ?>">
              </div>
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Facebook Messenger Link</label>
                <input type="url" name="ecms_messenger_url" class="form-input" value="<?php echo htmlspecialchars($ecms_settings['messenger_url']); ?>">
              </div>
            </div>
          </div>
          <div class="admin-modal-footer">
            <button type="submit" class="btn btn-success" style="padding:11px 26px; font-size:0.95rem;">💾 Save All ECMS Website Changes</button>
          </div>
        </div>
      </form>
    <?php endif; ?>

  </main>

  <!-- =========================================================
       ADD / EDIT VEHICLE MODAL (WITH CONDITION, DAMAGE & COMPRESSION)
       ========================================================= -->
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
        <input type="hidden" name="existing_reserved_until" id="carExistingReservedUntil" value="">

        <div class="admin-modal-body">
          <!-- Basic Identity & Age -->
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Year Model *</label>
              <input type="number" name="year_model" id="formCarYear" class="form-input" min="1990" max="2030" value="2024" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
              <label class="form-label">Vehicle Listing Title *</label>
              <input type="text" name="name" id="formCarName" class="form-input" placeholder="e.g. 2024 Toyota Vios 1.3 XLE" required>
            </div>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Brand / Make *</label>
              <input type="text" name="brand" id="formCarBrand" class="form-input" placeholder="e.g. Toyota" required>
            </div>
            <div class="form-group">
              <label class="form-label">Category *</label>
              <select name="body_type" id="formCarBody" class="form-select">
                <option value="Sedan">Sedan</option>
                <option value="Hatchback">Hatchback</option>
                <option value="SUV">SUV</option>
                <option value="MPV">MPV (7-Seater)</option>
                <option value="Pickup">Pickup</option>
                <option value="Van / Truck">Van / Truck</option>
                <option value="Motorcycle">Motorcycle</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Agent / Owner (Admin Only) 🔒</label>
              <input type="text" name="agent_owner" id="formCarAgent" class="form-input" placeholder="e.g. Boss, Paolo, S. Randy" value="Boss">
            </div>
          </div>

          <!-- Pricing & Deposit -->
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Selling Price (₱) *</label>
              <input type="number" step="1000" name="price" id="formCarPrice" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Price / Markdown Note</label>
              <input type="text" name="price_note" id="formCarPriceNote" class="form-input" value="Negotiable" placeholder="e.g. Markdown to ₱670k (Neg.)">
            </div>
            <div class="form-group">
              <label class="form-label">Hold Deposit (₱)</label>
              <input type="number" step="500" name="deposit_amount" id="formCarDeposit" class="form-input" value="10000">
            </div>
          </div>

          <!-- Specs & Identifiers -->
          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Stock ID</label>
              <input type="text" name="stock_id" id="formCarStock" class="form-input" placeholder="e.g. UW-HS22">
            </div>
            <div class="form-group">
              <label class="form-label">Plate / Conduction #</label>
              <input type="text" name="plate_number" id="formCarPlate" class="form-input" placeholder="e.g. DBN 2465">
            </div>
            <div class="form-group">
              <label class="form-label">Odometer / Mileage *</label>
              <input type="text" name="mileage" id="formCarMileage" class="form-input" placeholder="e.g. 23,000 km" required>
            </div>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Transmission</label>
              <select name="transmission" id="formCarTrans" class="form-select">
                <option value="Automatic">Automatic</option>
                <option value="Manual">Manual</option>
                <option value="Automatic CVT">Automatic CVT</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Fuel Type</label>
              <select name="fuel_type" id="formCarFuel" class="form-select">
                <option value="Gasoline">Gasoline</option>
                <option value="Diesel">Diesel</option>
                <option value="Hybrid">Hybrid</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Exterior Color &amp; Seats</label>
              <div style="display:grid; grid-template-columns:1.3fr 1fr; gap:6px;">
                <input type="text" name="finish" id="formCarColor" class="form-input" placeholder="Color" value="Silver Metallic">
                <input type="text" name="seating" id="formCarSeating" class="form-input" placeholder="5 Seats" value="5 Seats">
              </div>
            </div>
          </div>

          <!-- Real-Time Availability & Hold Duration -->
          <div class="section-box">
            <div class="section-box-title">
              <span>Real-Time Availability &amp; Reservation Hold Status</span>
            </div>
            <div class="form-grid-2">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Current Status *</label>
                <select name="status" id="formCarStatus" class="form-select" onchange="toggleAdminHoldFields()">
                  <option value="AVAILABLE">AVAILABLE (Open for Booking)</option>
                  <option value="RESERVED">RESERVED (Temporary Hold + Countdown)</option>
                  <option value="UNDER MAINTENANCE">UNDER MAINTENANCE (Temporarily Unavailable)</option>
                  <option value="SOLD">SOLD (Archive to Sold Tab)</option>
                </select>
              </div>
              <div class="form-group" id="adminHoldDaysGroup" style="margin-bottom:0; display:none;">
                <label class="form-label">Hold Duration (Days from now)</label>
                <div style="display:flex; gap:8px; align-items:center;">
                  <input type="number" name="admin_hold_days" id="formAdminHoldDays" class="form-input" min="1" max="14" value="2" style="width:90px;">
                  <label style="font-size:0.78rem; display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="checkbox" name="reset_hold_timer" value="1"> Reset timer
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- ADD-ON A: VEHICLE CONDITION & HISTORY -->
          <div class="section-box">
            <div class="section-box-title">
              <span>Vehicle Condition &amp; History (Shown on Public Car Profile)</span>
              <span class="badge badge-available">Required Transparency</span>
            </div>
            <div class="form-group">
              <label class="form-label">Ownership History *</label>
              <input type="text" name="ownership_history" id="formOwnership" class="form-input" placeholder="e.g. 1 previous owner — Casa maintained (or Company-owned since 2022)" value="1 previous owner — Casa maintained" required>
            </div>
            <div class="form-group">
              <label class="form-label">Transparent Damage &amp; Condition Disclosure *</label>
              <textarea name="damage_disclosure" id="formDamageDisclosure" class="form-textarea" placeholder="Document any existing scratches, touch-ups, dents, or repairs transparently..."></textarea>
            </div>
            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Custom Vehicle Policy Override (Optional — uses ECMS policy if blank)</label>
              <input type="text" name="vehicle_policies" id="formVehiclePolicies" class="form-input" placeholder="Optional unit-specific deposit or release note">
            </div>
          </div>

          <!-- ADD-ON D: PHOTOS & AUTOMATIC COMPRESSION -->
          <div class="section-box" style="margin-bottom:10px;">
            <div class="section-box-title">
              <span>High-Res Zoomable Photos (Auto-Compressed on Upload)</span>
              <span style="font-size:0.74rem; color:#15803d; font-weight:700;">⚡ Fast Mobile Compression Active</span>
            </div>

            <div style="display:grid; grid-template-columns:92px 1fr; gap:12px; align-items:center; margin-bottom:12px;">
              <img id="mainPhotoPreview" src="../assets/images/cars/car-1.jpg" alt="Preview" style="width:92px; height:62px; object-fit:cover; border-radius:6px; border:1px solid #cbd5e1;">
              <div>
                <label class="form-label">Main Exterior Cover Photo</label>
                <input type="file" name="car_upload_file" id="carUploadFile" class="form-input compress-on-select" accept="image/*" style="padding:6px;">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Additional Gallery Photos (Interior, Trunk, Dashboard, Tires)</label>
              <div id="additionalPhotosPreviewList" style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:6px;"></div>
              <input type="file" name="additional_photos[]" id="additionalPhotosInput" class="form-input compress-on-select" multiple accept="image/*" style="padding:6px;">
            </div>

            <div class="form-group" style="margin-bottom:0;">
              <label class="form-label">Damage / Scratch Close-Up Photos (Displayed in Condition Report)</label>
              <div id="damagePhotosPreviewList" style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:6px;"></div>
              <input type="file" name="damage_photos_upload[]" id="damagePhotosInput" class="form-input compress-on-select" multiple accept="image/*" style="padding:6px;">
            </div>
          </div>

          <div style="display:flex; gap:20px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:7px; font-size:0.88rem; cursor:pointer;">
              <input type="checkbox" name="is_recommended" id="formCarRecommended" value="1">
              <span>Pin to Recommended</span>
            </label>
            <label style="display:flex; align-items:center; gap:7px; font-size:0.88rem; cursor:pointer;">
              <input type="checkbox" name="is_featured" id="formCarFeatured" value="1">
              <span>Include in Hero Spotlight</span>
            </label>
          </div>
        </div>

        <div class="admin-modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeCarModal()">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Vehicle Profile</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Client-side HTML5 Canvas image compressor (Requirement D: keep uploaded photos crisp yet compressed for fast mobile loading)
    async function compressImageFile(file, maxWidth = 1600, quality = 0.82) {
      if (!file || !file.type.startsWith('image/') || file.size < 180 * 1024) return file;
      return new Promise((resolve) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
          URL.revokeObjectURL(url);
          let w = img.width;
          let h = img.height;
          if (w > maxWidth) {
            h = Math.round(h * (maxWidth / w));
            w = maxWidth;
          }
          const canvas = document.createElement('canvas');
          canvas.width = w;
          canvas.height = h;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, w, h);
          canvas.toBlob((blob) => {
            if (!blob || blob.size >= file.size) {
              resolve(file);
              return;
            }
            const base = file.name.replace(/\.[^.]+$/, '');
            resolve(new File([blob], `${base}.jpg`, { type: 'image/jpeg', lastModified: Date.now() }));
          }, 'image/jpeg', quality);
        };
        img.onerror = () => {
          URL.revokeObjectURL(url);
          resolve(file);
        };
        img.src = url;
      });
    }

    document.querySelectorAll('.compress-on-select').forEach(input => {
      input.addEventListener('change', async function() {
        if (!this.files || !this.files.length || typeof DataTransfer === 'undefined') return;
        const dt = new DataTransfer();
        for (const f of Array.from(this.files)) {
          const compressed = await compressImageFile(f);
          dt.items.add(compressed);
        }
        this.files = dt.files;
        if (this.id === 'carUploadFile' && this.files[0]) {
          document.getElementById('mainPhotoPreview').src = URL.createObjectURL(this.files[0]);
        }
      });
    });

    function toggleAdminHoldFields() {
      const st = document.getElementById('formCarStatus').value;
      document.getElementById('adminHoldDaysGroup').style.display = (st === 'RESERVED') ? 'block' : 'none';
    }

    function openCreateModal() {
      document.getElementById('carModalTitle').textContent = 'Add Pre-Owned Vehicle';
      document.getElementById('carAction').value = 'create_car';
      document.getElementById('carId').value = '';
      document.getElementById('carForm').reset();
      document.getElementById('formCarYear').value = 2024;
      document.getElementById('formCarStatus').value = 'AVAILABLE';
      document.getElementById('formOwnership').value = '1 previous owner — Casa maintained';
      document.getElementById('formDamageDisclosure').value = 'Minor surface scratch on rear bumper — photographed and documented prior to showroom display. 100% flood-free.';
      document.getElementById('mainPhotoPreview').src = '../assets/images/cars/car-1.jpg';
      document.getElementById('additionalPhotosPreviewList').innerHTML = '';
      document.getElementById('damagePhotosPreviewList').innerHTML = '';
      toggleAdminHoldFields();
      document.getElementById('carModal').style.display = 'flex';
    }

    function openEditModal(car) {
      document.getElementById('carModalTitle').textContent = 'Edit Vehicle: ' + (car.name || '');
      document.getElementById('carAction').value = 'update_car';
      document.getElementById('carId').value = car.id;
      document.getElementById('carExistingImage').value = car.image_file || 'car-1.jpg';
      document.getElementById('carExistingReservedUntil').value = car.reserved_until || '';

      document.getElementById('formCarYear').value = car.year_model || 2024;
      document.getElementById('formCarName').value = car.name || '';
      document.getElementById('formCarBrand').value = car.brand || '';
      document.getElementById('formCarBody').value = car.body_type || 'Sedan';
      document.getElementById('formCarAgent').value = car.agent_owner || 'Boss';
      document.getElementById('formCarPrice').value = car.price || 0;
      document.getElementById('formCarPriceNote').value = car.price_note || 'Negotiable';
      document.getElementById('formCarDeposit').value = car.deposit_amount || 10000;
      document.getElementById('formCarStock').value = car.stock_id || '';
      document.getElementById('formCarPlate').value = car.plate_number || '';
      document.getElementById('formCarMileage').value = car.mileage || '';
      document.getElementById('formCarTrans').value = car.transmission || 'Automatic';
      document.getElementById('formCarFuel').value = car.fuel_type || 'Gasoline';
      document.getElementById('formCarColor').value = car.finish || '';
      document.getElementById('formCarSeating').value = car.seating || '5 Seats';
      document.getElementById('formCarStatus').value = (car.status || 'AVAILABLE').toUpperCase();
      document.getElementById('formOwnership').value = car.ownership_history || '1 previous owner';
      document.getElementById('formDamageDisclosure').value = car.damage_disclosure || '';
      document.getElementById('formVehiclePolicies').value = car.vehicle_policies || '';
      document.getElementById('formCarRecommended').checked = car.is_recommended == 1;
      document.getElementById('formCarFeatured').checked = car.is_featured == 1;

      document.getElementById('mainPhotoPreview').src = '../assets/images/cars/' + (car.image_file || 'car-1.jpg');

      // Existing Gallery checkboxes
      const galWrap = document.getElementById('additionalPhotosPreviewList');
      galWrap.innerHTML = '';
      let gal = [];
      try { gal = typeof car.gallery_images === 'string' ? JSON.parse(car.gallery_images) : (car.gallery_images || []); } catch (e) {}
      if (Array.isArray(gal)) {
        gal.forEach(img => {
          const lbl = document.createElement('label');
          lbl.style = 'display:inline-flex; align-items:center; gap:5px; background:#fff; border:1px solid #cbd5e1; border-radius:6px; padding:3px 7px; font-size:0.75rem; cursor:pointer;';
          lbl.innerHTML = `<input type="checkbox" name="kept_gallery_images[]" value="${img}" checked> <img src="../assets/images/cars/${img}" style="width:30px; height:20px; object-fit:cover; border-radius:2px;"> <span>${img}</span>`;
          galWrap.appendChild(lbl);
        });
      }

      // Existing Damage Close-Up checkboxes
      const dmgWrap = document.getElementById('damagePhotosPreviewList');
      dmgWrap.innerHTML = '';
      let dmg = [];
      try { dmg = typeof car.damage_photos === 'string' ? JSON.parse(car.damage_photos) : (car.damage_photos || []); } catch (e) {}
      if (Array.isArray(dmg)) {
        dmg.forEach(d => {
          const file = typeof d === 'string' ? d : d.file;
          if (!file) return;
          const lbl = document.createElement('label');
          lbl.style = 'display:inline-flex; align-items:center; gap:5px; background:#fff1f2; border:1px solid #fecdd3; border-radius:6px; padding:3px 7px; font-size:0.75rem; cursor:pointer;';
          lbl.innerHTML = `<input type="checkbox" name="kept_damage_images[]" value="${file}" checked> <img src="../assets/images/cars/${file}" style="width:30px; height:20px; object-fit:cover; border-radius:2px;"> <span>${file}</span>`;
          dmgWrap.appendChild(lbl);
        });
      }

      toggleAdminHoldFields();
      document.getElementById('carModal').style.display = 'flex';
    }

    function closeCarModal() {
      document.getElementById('carModal').style.display = 'none';
    }

    window.addEventListener('click', (e) => {
      if (e.target === document.getElementById('carModal')) closeCarModal();
    });
  </script>
</body>
</html>
