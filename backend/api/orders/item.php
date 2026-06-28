<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) error_response('Invalid or missing order ID');

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
$user = current_user();

if ($method === 'GET') {
    require_role('admin', 'customer', 'rider');
    $stmt = $pdo->prepare(
        'SELECT o.*,
                u.name AS customer_name, u.phone AS customer_phone,
                r.name AS rider_name
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         LEFT JOIN users r ON o.rider_id = r.id
         WHERE o.id = ?'
    );
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) error_response('Order not found', 404);

    // Customers can only view their own orders
    if ($user['role'] === 'customer' && $order['customer_id'] != $user['id']) {
        error_response('Forbidden', 403);
    }

    $stmt = $pdo->prepare(
        'SELECT oi.*, m.name AS item_name, m.image_url
         FROM order_items oi
         JOIN menu_items m ON oi.menu_item_id = m.id
         WHERE oi.order_id = ?'
    );
    $stmt->execute([$id]);
    $order['items'] = $stmt->fetchAll();

    json_response($order);
}

if ($method === 'PUT') {
    require_role('admin', 'rider');
    if (!is_array($body) || !isset($body['status'])) error_response('Status field is required');

    $allowed = ['pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled'];
    if (!in_array($body['status'], $allowed, true)) {
        error_response('Invalid status value');
    }

    $stmt = $pdo->prepare('SELECT id, rider_id FROM orders WHERE id = ?');
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    if (!$order) error_response('Order not found', 404);

    // Riders can only update their own assigned orders
    if ($user['role'] === 'rider' && $order['rider_id'] != $user['id']) {
        error_response('Forbidden', 403);
    }

    $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$body['status'], $id]);
    json_response(['message' => 'Order status updated']);
}

error_response('Method not allowed', 405);
