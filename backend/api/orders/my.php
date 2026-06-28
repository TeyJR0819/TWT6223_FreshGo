<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') error_response('Method not allowed', 405);

require_role('customer');
$user = current_user();

$stmt = $pdo->prepare(
    'SELECT o.*, r.name AS rider_name,
            COUNT(oi.id) AS item_count
     FROM orders o
     LEFT JOIN users r ON o.rider_id = r.id
     LEFT JOIN order_items oi ON o.id = oi.order_id
     WHERE o.customer_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
json_response($stmt->fetchAll());
