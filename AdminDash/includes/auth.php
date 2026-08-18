<?php

function ensure_session_started(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function current_auth_user(): ?array
{
    ensure_session_started();
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 3600) {
        // Session expired
        session_unset();
        session_destroy();
        return null;
    }
    
    // Update last activity time
    if (isset($_SESSION['auth_user'])) {
        $_SESSION['last_activity'] = time();
    }
    
    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']) ? $_SESSION['auth_user'] : null;
}

function require_auth(): void
{
    ensure_session_started();
    $user = current_auth_user();
    if (!$user) {
        header('Location: /PhpstormProjects/19thepiscopaldistrict/AdminDash/login.php');
        exit;
    }
}

function generate_csrf_token(): string
{
    ensure_session_started();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool
{
    ensure_session_started();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function require_role(string $allowedRole): void
{
    require_auth();
    $user = current_auth_user();
    if (!$user || $user['role'] !== $allowedRole) {
        http_response_code(403);
        exit('Access denied: insufficient permissions');
    }
}

function require_conference_admin(int $conferenceId): void
{
    require_auth();
    $user = current_auth_user();
    if ($user['role'] === 'superadmin') return;
    if ($user['role'] === 'conference_admin' && (int)$user['conference_id'] === $conferenceId) return;
    http_response_code(403);
    exit('Access denied: you do not manage this conference');
}

