<?php
/**
 * UniWheels Public Booking & Reservation Hold API
 * Handles real-time availability checks, temporary reservation holds,
 * and confirmed buy/rent booking requests.
 */

$suppress_db_die = true;
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/includes/app.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function api_json(array $payload, int $status = 200): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$pdo) {
    api_json(['success' => false, 'message' => 'Database is currently unavailable. Please contact us on Messenger.'], 503);
}

uw_ensure_schema($pdo);
uw_release_expired_holds($pdo);

$settings = uw_get_settings($pdo);

function api_client_token(): string {
    $cookieName = 'uw_client_token';
    $token = $_COOKIE[$cookieName] ?? '';
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        $token = bin2hex(random_bytes(32));
        setcookie($cookieName, $token, [
            'expires'  => time() + 86400 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    return $token;
}

function api_generate_ref(string $prefix = 'B'): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $rand = '';
    for ($i = 0; $i < 4; $i++) {
        $rand .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return 'UW-' . $prefix . date('ymd') . '-' . $rand; // 14 chars: fits char(15) & varchar(24)
}

function api_user_reservations(PDO $pdo, string $token): array {
    $hash = hash('sha256', $token);
    $items = [];

    $stmt = $pdo->prepare("
        SELECT b.car_id, b.reference, b.booking_type, b.state, b.rental_start, b.rental_end,
               b.total, b.deposit, b.customer_name, b.customer_contact, b.expires_at, b.created_at,
               c.name AS car_name, c.status AS car_status, c.image_file
        FROM vehicle_bookings b
        JOIN cars c ON c.id = b.car_id AND c.deleted_at IS NULL
        WHERE b.owner_token = ? AND b.state IN ('pending', 'confirmed')
        ORDER BY b.id DESC
    ");
    $stmt->execute([$token]);
    foreach ($stmt->fetchAll() as $row) {
        $items[] = [
            'kind'             => 'booking',
            'car_id'           => (int) $row['car_id'],
            'car_name'         => $row['car_name'],
            'image_file'       => $row['image_file'],
            'car_status'       => strtoupper($row['car_status']),
            'reference'        => $row['reference'],
            'booking_type'     => $row['booking_type'],
            'state'            => $row['state'],
            'rental_start'     => $row['rental_start'],
            'rental_end'       => $row['rental_end'],
            'total'            => (float) $row['total'],
            'deposit'          => (float) $row['deposit'],
            'customer_name'    => $row['customer_name'],
            'customer_contact' => $row['customer_contact'],
            'expires_at'       => $row['expires_at'],
            'expires_ts'       => strtotime($row['expires_at']),
            'expires_label'    => date('F j, Y, g:i A', strtotime($row['expires_at'])),
        ];
    }

    $stmt = $pdo->prepare("
        SELECT h.car_id, h.reference, h.state, h.customer_name, h.customer_contact,
               h.deposit, h.expires_at, h.created_at,
               c.name AS car_name, c.status AS car_status, c.image_file
        FROM uw_reservation_holds h
        JOIN cars c ON c.id = h.car_id AND c.deleted_at IS NULL
        WHERE h.owner_hash = ? AND h.state = 'active' AND h.expires_at > NOW()
        ORDER BY h.id DESC
    ");
    $stmt->execute([$hash]);
    foreach ($stmt->fetchAll() as $row) {
        $days = max(1, (int) round((strtotime($row['expires_at']) - strtotime($row['created_at'])) / 86400));
        $items[] = [
            'kind'             => 'hold',
            'car_id'           => (int) $row['car_id'],
            'car_name'         => $row['car_name'],
            'image_file'       => $row['image_file'],
            'car_status'       => strtoupper($row['car_status']),
            'reference'        => $row['reference'],
            'state'            => $row['state'],
            'days'             => $days,
            'deposit'          => (float) $row['deposit'],
            'customer_name'    => $row['customer_name'],
            'customer_contact' => $row['customer_contact'],
            'expires_at'       => $row['expires_at'],
            'expires_ts'       => strtotime($row['expires_at']),
            'expires_label'    => date('F j, Y, g:i A', strtotime($row['expires_at'])),
        ];
    }

    return $items;
}

function api_cars_status_map(PDO $pdo): array {
    $rows = $pdo->query("SELECT id, status, reserved_at, reserve_expires_at FROM cars WHERE deleted_at IS NULL")->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[(int) $r['id']] = [
            'status'          => strtoupper(trim($r['status'] ?: 'AVAILABLE')),
            'reserved_at'     => $r['reserved_at'],
            'reserve_expires' => $r['reserve_expires_at'],
            'reserve_ts'      => $r['reserve_expires_at'] ? strtotime($r['reserve_expires_at']) : null,
            'reserve_label'   => $r['reserve_expires_at'] ? date('F j, Y, g:i A', strtotime($r['reserve_expires_at'])) : null,
        ];
    }
    return $out;
}

$token = api_client_token();
$ownerHash = hash('sha256', $token);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    api_json([
        'success'      => true,
        'cars'         => api_cars_status_map($pdo),
        'reservations' => api_user_reservations($pdo, $token),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_json(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$action = trim((string) ($input['action'] ?? ''));

if ($action === 'hold') {
    $carId = (int) ($input['car_id'] ?? 0);
    $days = (int) ($input['days'] ?? 0);
    $maxDays = max(1, (int) ($settings['max_hold_days'] ?? 7));
    $name = trim((string) ($input['customer_name'] ?? ''));
    $contact = trim((string) ($input['customer_contact'] ?? ''));

    if ($carId <= 0) {
        api_json(['success' => false, 'message' => 'Invalid vehicle selected.'], 400);
    }
    if ($days < 1 || $days > $maxDays) {
        api_json(['success' => false, 'message' => "Please choose a hold duration between 1 and {$maxDays} days."], 400);
    }
    if ($name === '' || $contact === '') {
        api_json(['success' => false, 'message' => 'Please provide your name and phone/email so we can hold this car for you.'], 400);
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND deleted_at IS NULL FOR UPDATE");
        $stmt->execute([$carId]);
        $car = $stmt->fetch();

        if (!$car) {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Vehicle not found.'], 404);
        }
        $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
        if ($status !== 'AVAILABLE') {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Sorry, this car is no longer available to reserve.'], 409);
        }

        $deposit = (float) ($car['booking_deposit'] ?? 0);
        if ($deposit <= 0) {
            $deposit = (float) ($settings['default_deposit'] ?? 5000);
        }
        $total = (float) ($car['price'] ?? 0);
        if (strtoupper($car['listing_type'] ?? 'SALE') === 'RENT' && (float) ($car['rental_rate'] ?? 0) > 0) {
            $total = (float) $car['rental_rate'];
        }

        $ref = api_generate_ref('H');
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $createdAt = date('Y-m-d H:i:s');

        $ins = $pdo->prepare("INSERT INTO uw_reservation_holds
            (car_id, reference, owner_hash, customer_name, customer_contact, total, deposit, expires_at, created_at, state)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $ins->execute([$carId, $ref, $ownerHash, $name, $contact, $total, $deposit, $expiresAt, $createdAt]);

        $upd = $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = ?, reserve_expires_at = ? WHERE id = ?");
        $upd->execute([$createdAt, $expiresAt, $carId]);

        $pdo->commit();

        api_json([
            'success'      => true,
            'reference'    => $ref,
            'days'         => $days,
            'deposit'      => $deposit,
            'expires_at'   => $expiresAt,
            'expires_ts'   => strtotime($expiresAt),
            'expires_label'=> date('F j, Y, g:i A', strtotime($expiresAt)),
            'cars'         => api_cars_status_map($pdo),
            'reservations' => api_user_reservations($pdo, $token),
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_json(['success' => false, 'message' => 'Could not place reservation hold. Please try again.'], 500);
    }
}

if ($action === 'book_proceed') {
    $carId = (int) ($input['car_id'] ?? 0);
    $bookingType = strtoupper(trim((string) ($input['booking_type'] ?? 'BUY')));
    if (!in_array($bookingType, ['BUY', 'RENT'], true)) {
        $bookingType = 'BUY';
    }
    $name = trim((string) ($input['customer_name'] ?? ''));
    $contact = trim((string) ($input['customer_contact'] ?? ''));
    $start = trim((string) ($input['rental_start'] ?? ''));
    $end = trim((string) ($input['rental_end'] ?? ''));

    if ($carId <= 0 || $name === '' || $contact === '') {
        api_json(['success' => false, 'message' => 'Please enter your name and contact details.'], 400);
    }

    $today = date('Y-m-d');
    $days = 1;
    if ($bookingType === 'RENT') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            api_json(['success' => false, 'message' => 'Please choose valid rental start and end dates.'], 400);
        }
        if ($start < $today || $end < $start) {
            api_json(['success' => false, 'message' => 'Rental end date must be on or after the start date (today or later).'], 400);
        }
        $days = max(1, (int) round((strtotime($end) - strtotime($start)) / 86400) + 1);
    } else {
        if ($start !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || $start < $today)) {
            api_json(['success' => false, 'message' => 'Please select today or a future pickup date.'], 400);
        }
        $start = $start ?: $today;
        $end = $start;
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND deleted_at IS NULL FOR UPDATE");
        $stmt->execute([$carId]);
        $car = $stmt->fetch();

        if (!$car) {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Vehicle not found.'], 404);
        }

        $listingType = strtoupper(trim($car['listing_type'] ?: 'SALE'));
        if ($bookingType === 'RENT' && !in_array($listingType, ['RENT', 'BOTH'], true)) {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'This vehicle is listed for sale only.'], 400);
        }
        if ($bookingType === 'BUY' && $listingType === 'RENT') {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'This vehicle is listed for rental only.'], 400);
        }

        $status = strtoupper(trim($car['status'] ?: 'AVAILABLE'));
        $ownedHold = null;
        if ($status === 'RESERVED') {
            $hStmt = $pdo->prepare("SELECT * FROM uw_reservation_holds WHERE car_id = ? AND owner_hash = ? AND state = 'active' AND expires_at > NOW() LIMIT 1");
            $hStmt->execute([$carId, $ownerHash]);
            $ownedHold = $hStmt->fetch();

            $bStmt = $pdo->prepare("SELECT * FROM vehicle_bookings WHERE car_id = ? AND owner_token = ? AND state = 'pending' AND expires_at > NOW() LIMIT 1");
            $bStmt->execute([$carId, $token]);
            $ownedPending = $bStmt->fetch();

            if (!$ownedHold && !$ownedPending) {
                $pdo->rollBack();
                api_json(['success' => false, 'message' => 'Sorry, this car is currently reserved by another customer.'], 409);
            }
        } elseif ($status !== 'AVAILABLE') {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Sorry, this car is currently not available for booking.'], 409);
        }

        $rate = (float) ($car['rental_rate'] ?? 0);
        $total = $bookingType === 'RENT' ? ($rate * $days) : (float) ($car['price'] ?? 0);
        $deposit = (float) ($car['booking_deposit'] ?? 0);
        if ($deposit <= 0) {
            $deposit = (float) ($settings['default_deposit'] ?? 5000);
        }

        // Cancel any previous pending booking by this client for this car before inserting the updated one.
        $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND owner_token = ? AND state = 'pending'")
            ->execute([$carId, $token]);

        $ref = api_generate_ref('B');
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = $ownedHold ? $ownedHold['expires_at'] : date('Y-m-d H:i:s', strtotime('+1 day'));

        $ins = $pdo->prepare("INSERT INTO vehicle_bookings
            (car_id, reference, owner_token, booking_type, state, rental_start, rental_end, total, deposit, customer_name, customer_contact, expires_at, created_at)
            VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([$carId, $ref, $token, $bookingType, $start, $end, $total, $deposit, $name, $contact, $expiresAt, $createdAt]);

        $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = COALESCE(reserved_at, ?), reserve_expires_at = ? WHERE id = ?")
            ->execute([$createdAt, $expiresAt, $carId]);

        $pdo->commit();

        api_json([
            'success'          => true,
            'reference'        => $ref,
            'booking_type'     => $bookingType,
            'state'            => 'pending',
            'rental_start'     => $start,
            'rental_end'       => $end,
            'days'             => $days,
            'total'            => $total,
            'deposit'          => $deposit,
            'customer_name'    => $name,
            'customer_contact' => $contact,
            'expires_at'       => $expiresAt,
            'expires_ts'       => strtotime($expiresAt),
            'expires_label'    => date('F j, Y, g:i A', strtotime($expiresAt)),
            'cars'             => api_cars_status_map($pdo),
            'reservations'     => api_user_reservations($pdo, $token),
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_json(['success' => false, 'message' => 'Could not create booking. Please try again.'], 500);
    }
}

if ($action === 'book_finalize') {
    $ref = trim((string) ($input['reference'] ?? ''));
    if ($ref === '') {
        api_json(['success' => false, 'message' => 'Missing booking reference.'], 400);
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT * FROM vehicle_bookings WHERE reference = ? AND owner_token = ? AND state = 'pending' FOR UPDATE");
        $stmt->execute([$ref, $token]);
        $booking = $stmt->fetch();

        if (!$booking) {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Booking could not be found or has expired.'], 404);
        }

        $pdo->prepare("UPDATE vehicle_bookings SET state = 'confirmed' WHERE id = ?")->execute([$booking['id']]);
        $pdo->prepare("UPDATE uw_reservation_holds SET state = 'confirmed' WHERE car_id = ? AND owner_hash = ? AND state = 'active'")
            ->execute([$booking['car_id'], $ownerHash]);

        // Confirmed reservations stay RESERVED without auto-expiration until staff marks Sold/Rented on handover.
        $pdo->prepare("UPDATE cars SET status = 'RESERVED', reserved_at = COALESCE(reserved_at, NOW()), reserve_expires_at = NULL WHERE id = ?")
            ->execute([$booking['car_id']]);

        $pdo->commit();

        api_json([
            'success'      => true,
            'reference'    => $ref,
            'message'      => 'Your reservation is confirmed. A confirmation email/SMS will be sent.',
            'cars'         => api_cars_status_map($pdo),
            'reservations' => api_user_reservations($pdo, $token),
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_json(['success' => false, 'message' => 'Could not finalize booking.'], 500);
    }
}

if ($action === 'book_remove') {
    $ref = trim((string) ($input['reference'] ?? ''));
    $carId = (int) ($input['car_id'] ?? 0);

    try {
        $pdo->beginTransaction();

        if ($ref !== '') {
            $bStmt = $pdo->prepare("SELECT car_id FROM vehicle_bookings WHERE reference = ? AND owner_token = ? AND state IN ('pending', 'confirmed') LIMIT 1");
            $bStmt->execute([$ref, $token]);
            if ($row = $bStmt->fetch()) {
                $carId = (int) $row['car_id'];
            } else {
                $hStmt = $pdo->prepare("SELECT car_id FROM uw_reservation_holds WHERE reference = ? AND owner_hash = ? AND state IN ('active', 'confirmed') LIMIT 1");
                $hStmt->execute([$ref, $ownerHash]);
                if ($row = $hStmt->fetch()) {
                    $carId = (int) $row['car_id'];
                }
            }
        }

        if ($carId <= 0) {
            $pdo->rollBack();
            api_json(['success' => false, 'message' => 'Reservation not found.'], 404);
        }

        $pdo->prepare("UPDATE vehicle_bookings SET state = 'cancelled' WHERE car_id = ? AND owner_token = ? AND state IN ('pending', 'confirmed')")
            ->execute([$carId, $token]);
        $pdo->prepare("UPDATE uw_reservation_holds SET state = 'cancelled' WHERE car_id = ? AND owner_hash = ? AND state IN ('active', 'confirmed')")
            ->execute([$carId, $ownerHash]);

        // Release car back to AVAILABLE only if no other active/confirmed bookings or holds remain.
        $otherB = $pdo->prepare("SELECT COUNT(*) FROM vehicle_bookings WHERE car_id = ? AND state IN ('pending', 'confirmed')");
        $otherB->execute([$carId]);
        $otherH = $pdo->prepare("SELECT COUNT(*) FROM uw_reservation_holds WHERE car_id = ? AND (state = 'confirmed' OR (state = 'active' AND expires_at > NOW()))");
        $otherH->execute([$carId]);

        if ((int) $otherB->fetchColumn() === 0 && (int) $otherH->fetchColumn() === 0) {
            $pdo->prepare("UPDATE cars SET status = 'AVAILABLE', reserved_at = NULL, reserve_expires_at = NULL WHERE id = ? AND UPPER(status) = 'RESERVED'")
                ->execute([$carId]);
        }

        $pdo->commit();

        api_json([
            'success'      => true,
            'message'      => 'Reservation removed. The vehicle is available again.',
            'cars'         => api_cars_status_map($pdo),
            'reservations' => api_user_reservations($pdo, $token),
        ]);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        api_json(['success' => false, 'message' => 'Could not remove reservation.'], 500);
    }
}

api_json(['success' => false, 'message' => 'Unknown action.'], 400);
