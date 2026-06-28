<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../helpers/validate.php';
require_once '../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) error_response('Invalid or missing item ID');

// Some hosts (confirmed on InfinityFree) don't reliably deliver the request
// body for PUT/DELETE -- only POST works consistently. The frontend sends
// both as POST with the real method folded into the JSON body instead.
$method = $_SERVER['REQUEST_METHOD'];
$body = null;
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (is_array($body) && isset($body['_method'])) {
        $method = strtoupper($body['_method']);
    }
}

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) error_response('Menu item not found', 404);
    json_response($item);
}

if ($method === 'PUT') {
    require_role('admin');
    if (!is_array($body)) error_response('Invalid JSON body');

    $stmt = $pdo->prepare('SELECT id FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) error_response('Menu item not found', 404);

    $fields = [];
    $values = [];
    $allowed = ['name', 'description', 'price', 'category', 'image_url', 'available'];
    foreach ($allowed as $f) {
        if (isset($body[$f])) {
            $fields[] = "$f = ?";
            $values[] = ($f === 'price') ? (float)$body[$f] : sanitize((string)$body[$f]);
        }
    }
    if (empty($fields)) error_response('No valid fields provided to update');

    $values[] = $id;
    $pdo->prepare('UPDATE menu_items SET ' . implode(', ', $fields) . ' WHERE id = ?')->execute($values);
    json_response(['message' => 'Menu item updated']);
}

if ($method === 'DELETE') {
    require_role('admin');
    $stmt = $pdo->prepare('SELECT id FROM menu_items WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) error_response('Menu item not found', 404);

    $pdo->prepare('DELETE FROM menu_items WHERE id = ?')->execute([$id]);
    json_response(['message' => 'Menu item deleted']);
}

error_response('Method not allowed', 405);
