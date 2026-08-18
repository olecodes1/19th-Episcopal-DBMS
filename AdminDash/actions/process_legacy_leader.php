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

$role = trim($_POST['role_type'] ?? 'Other');
$name = trim($_POST['full_name'] ?? '');
$conference = trim($_POST['conference_name'] ?? '');
$startYear = (int)($_POST['start_year'] ?? 0) ?: null;
$endYear = (int)($_POST['end_year'] ?? 0) ?: null;
$descriptions = trim($_POST['descriptions'] ?? '');
$achievements = trim($_POST['achievements'] ?? '');

if ($name === '') {
    header('Location: ../views/media.php?error=Leader name is required');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO legacy_leaders (role_type, full_name, conference_name, start_year, end_year, descriptions, achievements) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$role, $name, $conference ?: null, $startYear, $endYear, $descriptions ?: null, $achievements ?: null]);

header('Location: ../views/media.php?leader_added=1');
exit;

