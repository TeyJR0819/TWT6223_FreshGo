<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') error_response('Method not allowed', 405);

require_role('admin');

$stats = [];

$row = $pdo->query('SELECT COUNT(*) AS total FROM orders')->fetch();
$stats['total_orders'] = (int)$row['total'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'")->fetch();
$stats['pending_orders'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM orders WHERE status = 'out_for_delivery'")->fetch();
$stats['active_deliveries'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COALESCE(SUM(total_price), 0) AS rev FROM orders WHERE status = 'delivered'")->fetch();
$stats['total_revenue'] = (float)$row['rev'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'customer'")->fetch();
$stats['total_customers'] = (int)$row['cnt'];

$row = $pdo->query("SELECT COUNT(*) AS cnt FROM menu_items WHERE available = 1")->fetch();
$stats['active_menu_items'] = (int)$row['cnt'];

json_response($stats);
