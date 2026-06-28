<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../helpers/validate.php';
require_once '../../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    require_role('admin');
    $stmt = $pdo->query(
        'SELECT o.*,
                u.name AS customer_name, u.phone AS customer_phone,
                r.name AS rider_name,
                COUNT(oi.id) AS item_count
         FROM orders o
         JOIN users u ON o.customer_id = u.id
         LEFT JOIN users r ON o.rider_id = r.id
         LEFT JOIN order_items oi ON o.id = oi.order_id
         GROUP BY o.id
         ORDER BY o.created_at DESC'
    );
    json_response($stmt->fetchAll());
}

if ($method === 'POST') {
    require_role('customer');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['delivery_address', 'items']);

    if (!is_array($body['items']) || count($body['items']) === 0) {
        error_response('Order must contain at least one item');
    }

    $user    = current_user();
    $address = sanitize($body['delivery_address']);
    $notes   = sanitize($body['notes'] ?? '');
    $items   = $body['items'];

    // Validate items and calculate total
    $total = 0;
    $validatedItems = [];
    foreach ($items as $item) {
        if (!isset($item['menu_item_id'], $item['quantity'])) {
            error_response('Each item must have menu_item_id and quantity');
        }
        $qty = (int)$item['quantity'];
        if ($qty <= 0) error_response('Quantity must be at least 1');

        $stmt = $pdo->prepare('SELECT id, price, available FROM menu_items WHERE id = ?');
        $stmt->execute([(int)$item['menu_item_id']]);
        $menuItem = $stmt->fetch();
        if (!$menuItem) error_response('Menu item ID ' . (int)$item['menu_item_id'] . ' not found', 404);
        if (!$menuItem['available']) error_response('Menu item is currently unavailable');

        $total += $menuItem['price'] * $qty;
        $validatedItems[] = ['id' => $menuItem['id'], 'price' => $menuItem['price'], 'qty' => $qty];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO orders (customer_id, delivery_address, total_price, notes)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$user['id'], $address, $total, $notes]);
        $orderId = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)'
        );
        foreach ($validatedItems as $vi) {
            $stmt->execute([$orderId, $vi['id'], $vi['qty'], $vi['price']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_response('Failed to place order. Please try again.', 500);
    }

    json_response(['id' => $orderId, 'total' => $total, 'message' => 'Order placed successfully'], 201);
}

error_response('Method not allowed', 405);
