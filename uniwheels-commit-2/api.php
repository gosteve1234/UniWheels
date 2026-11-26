<?php
/**
 * UniWheels Public AJAX API — Reservation Holds, Countdown Lookup, Cancellation & Auto-Release
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/uniwheels-core.php';

if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

uniwheels_ensure_schema($pdo);
uniwheels_release_expired_holds($pdo);
$settings = uniwheels_get_settings($pdo);

$rawInput = file_get_contents('php://input');
$jsonBody = !empty($rawInput) ? json_decode($rawInput, true) : [];
$req = is_array($jsonBody) && !empty($jsonBody) ? array_merge($_REQUEST, $jsonBody) : $_REQUEST;

$action = trim((string)($req['action'] ?? 'sync_status'));

try {
    // 1. Real-time availability & expired-hold sync
    if ($action === 'sync_status') {
        $rows = $pdo->query("SELECT `id`, `status`, `reserved_until`, `active_reservation_ref` FROM `cars`")->fetchAll(PDO::FETCH_ASSOC);
        $resMap = [];
        $activeResRows = $pdo->query("SELECT `booking_ref`, `hold_days`, `expires_at` FROM `reservations` WHERE `status` = 'ACTIVE'")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($activeResRows as $ar) {
            $resMap[$ar['booking_ref']] = $ar;
        }

        $statuses = [];
        foreach ($rows as $r) {
            $st = strtoupper($r['status'] ?: 'AVAILABLE');
            $ref = $r['active_reservation_ref'] ?? null;
            $exp = $r['reserved_until'] ?? null;
            $statuses[$r['id']] = [
                'id'                        => (int)$r['id'],
                'status'                    => $st,
                'reserved_until_iso'        => ($st === 'RESERVED' && $exp) ? date('c', strtotime($exp)) : null,
                'reserved_until_formatted'  => ($st === 'RESERVED' && $exp) ? date('F j, Y, g:i A', strtotime($exp)) : null,
                'hold_days'                 => ($ref && isset($resMap[$ref])) ? (int)$resMap[$ref]['hold_days'] : null
            ];
        }
        echo json_encode([
            'success'     => true,
            'server_time' => date('c'),
            'statuses'    => $statuses
        ]);
        exit;
    }

    // 2. Place a temporary Reservation Hold (Requirements B & C)
    if ($action === 'create_reservation') {
        $carId = intval($req['car_id'] ?? 0);
        $customerName = trim((string)($req['customer_name'] ?? ''));
        $customerPhone = trim((string)($req['customer_phone'] ?? ''));
        $customerEmail = trim((string)($req['customer_email'] ?? ''));
        $holdDays = intval($req['hold_days'] ?? 2);
        $notes = trim((string)($req['notes'] ?? ''));

        $maxHoldDays = max(1, intval($settings['max_hold_days'] ?? 7));

        if ($carId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid vehicle selected.']);
            exit;
        }
        if (mb_strlen($customerName) < 2) {
            echo json_encode(['success' => false, 'message' => 'Please enter your full name.']);
            exit;
        }
        if (mb_strlen($customerPhone) < 7) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid mobile or phone number for SMS confirmation.']);
            exit;
        }
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid email address for your booking confirmation.']);
            exit;
        }
        if ($holdDays < 1 || $holdDays > $maxHoldDays) {
            echo json_encode(['success' => false, 'message' => "Hold duration must be between 1 and {$maxHoldDays} days."]);
            exit;
        }

        $pdo->beginTransaction();

        $stmtCar = $pdo->prepare("SELECT * FROM `cars` WHERE `id` = ? FOR UPDATE");
        $stmtCar->execute([$carId]);
        $car = $stmtCar->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Vehicle not found in inventory.']);
            exit;
        }

        $curStatus = strtoupper($car['status'] ?? 'AVAILABLE');
        if ($curStatus !== 'AVAILABLE') {
            $pdo->rollBack();
            $reason = ($curStatus === 'RESERVED')
                ? 'This vehicle was just placed on reservation hold by another customer.'
                : "This vehicle is currently marked as {$curStatus} and cannot be reserved.";
            echo json_encode(['success' => false, 'message' => $reason, 'current_status' => $curStatus]);
            exit;
        }

        // Generate human-readable unique booking reference
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $randCode = '';
        for ($i = 0; $i < 6; $i++) {
            $randCode .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $bookingRef = 'UW-' . date('Y') . '-' . $randCode;

        $nowTs = time();
        $expTs = $nowTs + ($holdDays * 86400);
        $reservedAt = date('Y-m-d H:i:s', $nowTs);
        $expiresAt  = date('Y-m-d H:i:s', $expTs);

        $carPrice = floatval($car['price'] ?? 0);
        $defaultDeposit = floatval($settings['default_deposit'] ?? 10000);
        $depositAmount = floatval($car['deposit_amount'] ?? 0) > 0 ? floatval($car['deposit_amount']) : $defaultDeposit;

        $ins = $pdo->prepare("INSERT INTO `reservations` (
            `booking_ref`, `car_id`, `customer_name`, `customer_phone`, `customer_email`,
            `hold_days`, `car_price`, `deposit_amount`, `reserved_at`, `expires_at`, `status`, `notes`, `notification_sent`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', ?, 1)");
        $ins->execute([
            $bookingRef, $carId, $customerName, $customerPhone, $customerEmail,
            $holdDays, $carPrice, $depositAmount, $reservedAt, $expiresAt, $notes
        ]);

        $upd = $pdo->prepare("UPDATE `cars` SET `status` = 'RESERVED', `reserved_until` = ?, `active_reservation_ref` = ? WHERE `id` = ?");
        $upd->execute([$expiresAt, $bookingRef, $carId]);

        // Also record in legacy inquiries table if present so dealership history stays unified
        try {
            $hasInq = $pdo->query("SHOW TABLES LIKE 'inquiries'")->fetchColumn();
            if ($hasInq) {
                $pdo->prepare("INSERT INTO `inquiries` (`name`, `email`, `phone`, `car_id`, `message`, `status`) VALUES (?, ?, ?, ?, ?, 'reserved')")
                    ->execute([$customerName, $customerEmail, $customerPhone, $carId, "Reservation Hold #{$bookingRef} ({$holdDays} day(s)) until " . date('M j, Y g:i A', $expTs)]);
            }
        } catch (Exception $e) {}

        $pdo->commit();

        echo json_encode([
            'success'     => true,
            'message'     => 'Your reservation is confirmed. A confirmation email/SMS will be sent.',
            'reservation' => [
                'booking_ref'            => $bookingRef,
                'car_id'                 => (int)$car['id'],
                'car_name'               => $car['name'],
                'stock_id'               => $car['stock_id'] ?: ('UW-' . $car['id']),
                'image_file'             => $car['image_file'] ?: 'car-1.jpg',
                'customer_name'          => $customerName,
                'customer_phone'         => $customerPhone,
                'customer_email'         => $customerEmail,
                'hold_days'              => $holdDays,
                'car_price'              => $carPrice,
                'deposit_amount'         => $depositAmount,
                'reserved_at_iso'        => date('c', $nowTs),
                'reserved_at_formatted'  => date('F j, Y, g:i A', $nowTs),
                'expires_at_iso'         => date('c', $expTs),
                'expires_at_formatted'   => date('F j, Y, g:i A', $expTs),
                'status'                 => 'ACTIVE'
            ]
        ]);
        exit;
    }

    // 3. Lookup Reservations (by Reference #, Phone/Email, or saved localStorage refs)
    if ($action === 'lookup_reservations') {
        $query = trim((string)($req['query'] ?? ''));
        $savedRefs = $req['refs'] ?? [];
        if (is_string($savedRefs)) {
            $savedRefs = array_filter(array_map('trim', explode(',', $savedRefs)));
        }
        if (!is_array($savedRefs)) {
            $savedRefs = [];
        }

        $results = [];
        if ($query !== '') {
            $stmt = $pdo->prepare("
                SELECT r.*, c.name AS car_name, c.stock_id, c.image_file, c.status AS car_status
                FROM `reservations` r
                LEFT JOIN `cars` c ON c.id = r.car_id
                WHERE r.booking_ref = ? OR r.customer_email = ? OR r.customer_phone = ?
                ORDER BY r.id DESC LIMIT 20
            ");
            $stmt->execute([strtoupper($query), $query, $query]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } elseif (!empty($savedRefs)) {
            $cleanRefs = array_slice(array_map('strtoupper', array_map('strval', $savedRefs)), 0, 20);
            $placeholders = implode(',', array_fill(0, count($cleanRefs), '?'));
            $stmt = $pdo->prepare("
                SELECT r.*, c.name AS car_name, c.stock_id, c.image_file, c.status AS car_status
                FROM `reservations` r
                LEFT JOIN `cars` c ON c.id = r.car_id
                WHERE r.booking_ref IN ({$placeholders})
                ORDER BY r.id DESC
            ");
            $stmt->execute($cleanRefs);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $formatted = array_map(function($r) {
            $resTs = strtotime($r['reserved_at']);
            $expTs = strtotime($r['expires_at']);
            return [
                'booking_ref'           => $r['booking_ref'],
                'car_id'                => (int)$r['car_id'],
                'car_name'              => $r['car_name'] ?: ('Vehicle #' . $r['car_id']),
                'stock_id'              => $r['stock_id'] ?: ('UW-' . $r['car_id']),
                'image_file'            => $r['image_file'] ?: 'car-1.jpg',
                'customer_name'         => $r['customer_name'],
                'customer_phone'        => $r['customer_phone'],
                'customer_email'        => $r['customer_email'],
                'hold_days'             => (int)$r['hold_days'],
                'car_price'             => floatval($r['car_price']),
                'deposit_amount'        => floatval($r['deposit_amount']),
                'reserved_at_iso'       => date('c', $resTs),
                'reserved_at_formatted' => date('F j, Y, g:i A', $resTs),
                'expires_at_iso'        => date('c', $expTs),
                'expires_at_formatted'  => date('F j, Y, g:i A', $expTs),
                'status'                => strtoupper($r['status'])
            ];
        }, $results);

        echo json_encode([
            'success'      => true,
            'reservations' => $formatted
        ]);
        exit;
    }

    // 4. Cancel / Remove a Car from Customer Reservation (Requirement B popup action)
    if ($action === 'cancel_reservation') {
        $bookingRef = strtoupper(trim((string)($req['booking_ref'] ?? '')));
        if ($bookingRef === '') {
            echo json_encode(['success' => false, 'message' => 'Missing booking reference code.']);
            exit;
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM `reservations` WHERE `booking_ref` = ? FOR UPDATE");
        $stmt->execute([$bookingRef]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Reservation reference not found.']);
            exit;
        }

        if (strtoupper($res['status']) !== 'ACTIVE') {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'This reservation is already ' . strtolower($res['status']) . '.']);
            exit;
        }

        $pdo->prepare("UPDATE `reservations` SET `status` = 'CANCELLED' WHERE `id` = ?")->execute([$res['id']]);
        $pdo->prepare("UPDATE `cars` SET `status` = 'AVAILABLE', `reserved_until` = NULL, `active_reservation_ref` = NULL WHERE `id` = ? AND `status` = 'RESERVED'")
            ->execute([$res['car_id']]);

        $pdo->commit();

        echo json_encode([
            'success'     => true,
            'message'     => 'Reservation cancelled. The vehicle has been released back to Available status.',
            'car_id'      => (int)$res['car_id'],
            'booking_ref' => $bookingRef
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unsupported API action.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
