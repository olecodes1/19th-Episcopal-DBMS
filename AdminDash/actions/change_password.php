<?php
require_once '../db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forms/change_password.php"); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

require_auth();
$user = current_auth_user();

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validate current password
$stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->execute([$user['user_id']]);
$storedHash = $stmt->fetchColumn();

if (!password_verify($currentPassword, $storedHash)) {
    header("Location: ../forms/change_password.php?error=current_wrong"); exit;
}

// Validate new password
if (strlen($newPassword) < 8) {
    header("Location: ../forms/change_password.php?error=too_short"); exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../forms/change_password.php?error=mismatch"); exit;
}

// Update password
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE user_id = ?");
$stmt->execute([$newHash, $user['user_id']]);

header("Location: ../forms/change_password.php?success=1"); exit;