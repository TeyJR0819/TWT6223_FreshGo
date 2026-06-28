<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') error_response('Method not allowed', 405);

require_role('rider');
$user = current_user();

$stmt = $pdo->prepare(
    'SELECT o.*,
            u.name AS customer_name, u.phone AS customer_phone,
            COUNT(oi.id) AS item_count
     FROM orders o
     JOIN users u ON o.customer_id = u.id
     LEFT JOIN order_items oi ON o.id = oi.order_id
     WHERE o.rider_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
json_response($stmt->fetchAll());
