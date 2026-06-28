<?php
require_once '../../helpers/response.php';

require_once '../../helpers/validate.php';
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') error_response('Method not allowed', 405);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) error_response('Invalid JSON body');

require_fields($body, ['email', 'password']);

$email = trim($body['email']);
$pass  = $body['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_response('Invalid email format');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password'])) {
    error_response('Invalid email or password', 401);
}

start_session();
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
$_SESSION['name']    = $user['name'];

json_response([
    'id'    => $user['id'],
    'name'  => $user['name'],
    'role'  => $user['role'],
    'email' => $user['email'],
]);
