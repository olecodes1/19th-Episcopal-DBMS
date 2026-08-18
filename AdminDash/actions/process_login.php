<?php
define('ALLOW_GUEST', true);
require_once '../db.php';
require_once '../includes/feature_tables.php';
require_once '../includes/auth.php';

ensure_feature_tables($pdo);
ensure_session_started();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF validation failed');
}

// Rate limiting: 5 failed attempts per 15 minutes per IP
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'login_attempts_' . md5($clientIp);
$currentTime = time();

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['attempts' => 0, 'first_attempt' => $currentTime];
}

$rateLimitData = $_SESSION[$rateLimitKey];
$timeWindow = 15 * 60; // 15 minutes

// Reset if time window has passed
if (($currentTime - $rateLimitData['first_attempt']) > $timeWindow) {
    $_SESSION[$rateLimitKey] = ['attempts' => 0, 'first_attempt' => $currentTime];
    $rateLimitData = $_SESSION[$rateLimitKey];
}

// Check if rate limit exceeded
if ($rateLimitData['attempts'] >= 5) {
    $remainingTime = $timeWindow - ($currentTime - $rateLimitData['first_attempt']);
    header('Location: ../login.php?error=' . urlencode('Too many login attempts. Please try again in ' . ceil($remainingTime / 60) . ' minutes.'));
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: ../login.php?error=' . urlencode('Username and password are required'));
    exit;
}

$stmt = $pdo->prepare("SELECT user_id, username, password_hash, role, conference_id, is_active FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || (int)$user['is_active'] !== 1 || !password_verify($password, (string)$user['password_hash'])) {
    // Increment failed attempt counter
    $_SESSION[$rateLimitKey]['attempts']++;
    header('Location: ../login.php?error=' . urlencode('Invalid credentials'));
    exit;
}

// Reset rate limit counter on successful login
unset($_SESSION[$rateLimitKey]);

session_regenerate_id(true);
$_SESSION['auth_user'] = [
    'user_id' => (int)$user['user_id'],
    'username' => (string)$user['username'],
    'role' => (string)$user['role'],
    'conference_id' => $user['conference_id'] !== null ? (int)$user['conference_id'] : null,
];
$_SESSION['last_activity'] = time();

header('Location: ../index.php');
exit;
