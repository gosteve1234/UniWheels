<?php
/**
 * UniWheels Customer Authentication API (Disabled)
 * Customer account features have been removed per Showroom-only specifications.
 * Direct inquiries are handled exclusively via Messenger.
 */

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'success' => false,
    'logged_in' => false,
    'message' => 'Customer accounts are disabled. Direct inquiries are handled via Messenger.'
]);
exit;
