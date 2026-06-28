<?php
require_once '../../helpers/response.php';

require_once '../../helpers/auth.php';
require_once '../../config/db.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT id, name, email, phone FROM users WHERE role = 'rider' ORDER BY name");
    json_response($stmt->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../helpers/validate.php';
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) error_response('Invalid JSON body');
    require_fields($body, ['name', 'email', 'password']);

    if (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) error_response('Invalid email format');
    if (strlen($body['password']) < 6) error_response('Password must be at least 6 characters');

    $hash = password_hash($body['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'rider', ?)"
        );
        $stmt->execute([
            sanitize($body['name']),
            trim($body['email']),
            $hash,
            sanitize($body['phone'] ?? '')
        ]);
        json_response(['id' => $pdo->lastInsertId(), 'message' => 'Rider account created'], 201);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') error_response('Email already registered', 409);
        error_response('Failed to create rider account', 500);
    }
}

error_response('Method not allowed', 405);
