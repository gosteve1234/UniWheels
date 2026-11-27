<?php
/**
 * UniWheels Public Availability API
 * Returns real-time vehicle availability statuses for the public showroom.
 * Reservations are managed directly by showroom staff in the admin dashboard.
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    api_json([
        'success' => true,
        'cars'    => api_cars_status_map($pdo),
    ]);
}

api_json([
    'success' => false,
    'message' => 'Reservations are managed directly by our showroom staff. Please inquire on Messenger.',
], 403);
