<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../helpers/validate.php';
require_once '../../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT * FROM menu_items ORDER BY category, name');
    json_response($stmt->fetchAll());
}

if ($method === 'POST') {
    require_role('admin');
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['name', 'price', 'category']);

    $name      = sanitize($body['name']);
    $price     = (float)$body['price'];
    $category  = sanitize($body['category']);
    $desc      = sanitize($body['description'] ?? '');
    $image_url = sanitize($body['image_url'] ?? '');
    $available = isset($body['available']) ? (int)$body['available'] : 1;

    if ($price <= 0) error_response('Price must be greater than 0');

    $stmt = $pdo->prepare(
        'INSERT INTO menu_items (name, description, price, category, image_url, available)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([$name, $desc, $price, $category, $image_url, $available]);
    json_response(['id' => $pdo->lastInsertId(), 'message' => 'Menu item created'], 201);
}

error_response('Method not allowed', 405);
