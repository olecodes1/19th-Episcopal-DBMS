<?php
require_once '../db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/conferences.php"); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

// Only superadmin can update conferences
require_role('superadmin');

$id   = (int)$_POST['conference_id'];
$name = trim($_POST['conference_name'] ?? '');
$president = trim($_POST['conference_president'] ?? '');
$director = trim($_POST['conference_director'] ?? '');

try {
    $stmt = $pdo->prepare("UPDATE conferences SET conference_name = ?, conference_president = ?, conference_director = ? WHERE conference_id = ?");
    $stmt->execute([$name, $president ?: null, $director ?: null, $id]);
    header("Location: ../views/conferences.php?updated=1"); exit;
} catch (PDOException $e) {
    error_log("Conference update failed: " . $e->getMessage());
    header("Location: ../forms/edit_conference.php?id=$id&error=database_error"); exit;
}
