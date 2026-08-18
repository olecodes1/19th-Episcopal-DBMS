<?php
require_once '../db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forms/add_conference.php"); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

// Only superadmin can create conferences
require_role('superadmin');

$name = trim($_POST['conference_name'] ?? '');
$president = trim($_POST['conference_president'] ?? '');
$director = trim($_POST['conference_director'] ?? '');
if (!$name) { header("Location: ../forms/add_conference.php?error=missing"); exit; }

try {
    $stmt = $pdo->prepare("INSERT INTO conferences (district_id, conference_name, conference_president, conference_director) VALUES (19, ?, ?, ?)");
    $stmt->execute([$name, $president ?: null, $director ?: null]);
    header("Location: ../views/conferences.php?success=1"); exit;
} catch (PDOException $e) {
    error_log("Conference insert failed: " . $e->getMessage());
    header("Location: ../forms/add_conference.php?error=database_error"); exit;
}
