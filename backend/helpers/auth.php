<?php
function require_role(string ...$roles): void {
    start_session();
    if (!isset($_SESSION['user_id'])) {
        error_response('Unauthenticated. Please log in.', 401);
    }
    if (!in_array($_SESSION['role'], $roles, true)) {
        error_response('Forbidden. You do not have permission.', 403);
    }
}

function current_user(): array {
    start_session();
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['role']    ?? null,
        'name' => $_SESSION['name']    ?? null,
    ];
}
