<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

// Some hosts (confirmed on InfinityFree) don't reliably deliver the request
// body for PUT -- only POST works consistently. The frontend sends PUT as
// POST with the real method folded into the JSON body instead.
$method = $_SERVER['REQUEST_METHOD'];
$body = null;
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body) && isset($body['_method'])) {
        $method = strtoupper($body['_method']);
    }
}
if ($method !== 'PUT') error_response('Method not allowed', 405);

require_role('admin');

if (!is_array($body) || !isset($body['order_id'], $body['rider_id'])) {
    error_response('order_id and rider_id are required');
}

$orderId  = (int)$body['order_id'];
$riderId  = (int)$body['rider_id'];

// Verify order exists
$stmt = $pdo->prepare('SELECT id FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
if (!$stmt->fetch()) error_response('Order not found', 404);

// Verify rider exists and has correct role
$stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'rider'");
$stmt->execute([$riderId]);
if (!$stmt->fetch()) error_response('Rider not found', 404);

$pdo->prepare('UPDATE orders SET rider_id = ? WHERE id = ?')->execute([$riderId, $orderId]);
json_response(['message' => 'Rider assigned successfully']);
