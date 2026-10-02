<?php
/**
 * UniWheels - Client Inquiry Endpoint
 * Showroom inquiries are handled directly via Facebook Messenger.
 */
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => true,
    'message' => 'Direct inquiries are handled via Facebook Messenger.',
    'messenger_url' => 'https://m.me/uniwheels'
]);
exit;
