<?php
require_once '../../helpers/response.php';

require_once '../../helpers/validate.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_response('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) error_response('Invalid JSON body');

require_fields($body, ['name', 'email', 'password']);

$name  = sanitize($body['name']);
$email = trim($body['email']);
$pass  = $body['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error_response('Invalid email format');
if (strlen($pass) < 6) error_response('Password must be at least 6 characters');
if (strlen($name) < 2) error_response('Name must be at least 2 characters');

$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "customer")');
    $stmt->execute([$name, $email, $hash]);
    $id = $pdo->lastInsertId();
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        error_response('Email is already registered', 409);
    }
    error_response('Registration failed. Please try again.', 500);
}

start_session();
$_SESSION['user_id'] = $id;
$_SESSION['role']    = 'customer';
$_SESSION['name']    = $name;

json_response(['id' => $id, 'name' => $name, 'role' => 'customer', 'email' => $email], 201);
