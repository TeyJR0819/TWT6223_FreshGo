<?php
// Frontend and backend are served from the same origin (see README
// Architecture) -- no CORS headers needed anywhere, and the session cookie
// is same-site, so plain SameSite=Lax always works. `secure` still adapts
// to HTTPS so the cookie isn't sent insecurely if the site is ever served
// over plain HTTP.
function start_session(): void {
    if (session_status() !== PHP_SESSION_NONE) return;

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function json_response($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function error_response(string $message, int $code = 400): void {
    json_response(['error' => $message], $code);
}
