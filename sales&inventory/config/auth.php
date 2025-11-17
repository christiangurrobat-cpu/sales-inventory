<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ensure_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool
{
    ensure_session();
    return isset($_SESSION['user_id']);
}

function current_user_role(): ?string
{
    ensure_session();
    return $_SESSION['user_role'] ?? null;
}

function require_page_auth(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function redirect_if_authenticated(): void
{
    if (is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function api_respond_and_exit(int $status, string $message): void
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode([
        'success' => false,
        'error' => $message
    ]);
    exit;
}

function ensure_api_auth(array $allowedRoles = []): void
{
    if (!is_logged_in()) {
        api_respond_and_exit(401, 'Authentication required');
    }

    if (!empty($allowedRoles) && !in_array(current_user_role(), $allowedRoles, true)) {
        api_respond_and_exit(403, 'You do not have permission to perform this action');
    }
}

function logout_user(): void
{
    ensure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

