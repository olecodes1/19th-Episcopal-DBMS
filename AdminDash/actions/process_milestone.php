<?php
require_once '../db.php';
require_once '../includes/auth.php';
require_once '../includes/feature_tables.php';
ensure_feature_tables($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/media.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

$title = trim($_POST['title'] ?? '');
$year = (int)($_POST['milestone_year'] ?? 0);
$descriptions = trim($_POST['descriptions'] ?? '');
$achievements = trim($_POST['achievements'] ?? '');

if ($title === '' || $year <= 0) {
    header('Location: ../views/media.php?error=Milestone title and year are required');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO milestones (title, milestone_year, descriptions, achievements) VALUES (?, ?, ?, ?)");
$stmt->execute([$title, $year, $descriptions ?: null, $achievements ?: null]);
header('Location: ../views/media.php?milestone_added=1');
exit;

