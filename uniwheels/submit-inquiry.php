<?php
/**
 * UniWheels - Client Inquiry & Showroom Appointment Processor
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$name = trim($_POST['client_name'] ?? $_POST['name'] ?? '');
$email = trim($_POST['client_email'] ?? $_POST['email'] ?? '');
$phone = trim($_POST['client_phone'] ?? $_POST['phone'] ?? '');
$vehicle = trim($_POST['selected_car'] ?? $_POST['vehicle_name'] ?? 'General Showroom Fleet');
$inquiry_type = trim($_POST['inquiry_type'] ?? 'Showroom Visit (Face-to-Face)');
$preferred_date = trim($_POST['preferred_date'] ?? '');
$message = trim($_POST['client_message'] ?? $_POST['message'] ?? '');

if (!$name || (!$email && !$phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please provide your full name and at least a phone number or email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, vehicle_name, inquiry_type, preferred_date, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'new')");
    $stmt->execute([$name, $email, $phone, $vehicle, $inquiry_type, $preferred_date, $message]);
    $lead_id = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'lead_id' => $lead_id,
        'message' => 'Showroom consultation request received! A UniWheels acquisition specialist will contact you directly to confirm your private face-to-face appointment.'
    ]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
