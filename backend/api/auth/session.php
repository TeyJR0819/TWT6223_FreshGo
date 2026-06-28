<?php
require_once '../../helpers/response.php';

start_session();

if (!isset($_SESSION['user_id'])) {
    error_response('Not authenticated', 401);
}

json_response([
    'id'   => $_SESSION['user_id'],
    'name' => $_SESSION['name'],
    'role' => $_SESSION['role'],
]);
