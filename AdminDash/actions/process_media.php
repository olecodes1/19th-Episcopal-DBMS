<?php
require_once '../db.php';
require_once '../includes/auth.php';
require_once '../includes/feature_tables.php';
require_once '../includes/validation.php';

ensure_feature_tables($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/media.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

$title = validate_string($_POST['title'] ?? '', 1, 255);
$type = validate_enum($_POST['media_type'] ?? '', ['image', 'video', 'audio']);
$category = validate_string($_POST['category'] ?? '', 0, 120);
$tags = validate_string($_POST['tags'] ?? '', 0, 255);
$eventTag = validate_string($_POST['event_tag'] ?? '', 0, 120);
$personTag = validate_string($_POST['person_tag'] ?? '', 0, 120);
$mediaYear = validate_int($_POST['media_year'] ?? 0, 1800, (int)date('Y'));
$description = validate_string($_POST['description'] ?? '', 0, 65535);

if (!$title || !$type || !isset($_FILES['media_file'])) {
    header('Location: ../views/media.php?error=Invalid media submission');
    exit;
}

// Check for double extensions
if (has_double_extension($_FILES['media_file']['name'])) {
    header('Location: ../views/media.php?error=File with double extensions are not allowed');
    exit;
}

$allowedTypes = [
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'video' => ['mp4', 'webm', 'ogg', 'mov'],
    'audio' => ['mp3', 'wav', 'ogg', 'm4a'],
];

$validFile = validate_file_upload($_FILES['media_file'], $allowedTypes[$type], 52428800); // 50MB
if (!$validFile) {
    header('Location: ../views/media.php?error=Invalid file upload');
    exit;
}

$uploadDir = __DIR__ . '/../assets/uploads/media';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0775, true)) {
        error_log("Failed to create upload directory: " . $uploadDir);
        header('Location: ../views/media.php?error=Could not create upload folder - check directory permissions');
        exit;
    }
}
if (!is_writable($uploadDir)) {
    error_log("Upload directory not writable: " . $uploadDir);
    header('Location: ../views/media.php?error=Upload folder not writable - check directory permissions');
    exit;
}

$fileName = generate_safe_filename($_FILES['media_file']['name']);
$target = $uploadDir . '/' . $fileName;

if (!move_uploaded_file($validFile['tmp_name'], $target)) {
    header('Location: ../views/media.php?error=Upload failed');
    exit;
}

$relative = 'assets/uploads/media/' . $fileName;

$data = [
    'title' => $title,
    'media_type' => $type,
    'category' => $category ?: null,
    'tags' => $tags ?: null,
    'event_tag' => $eventTag ?: null,
    'person_tag' => $personTag ?: null,
    'media_year' => $mediaYear,
    'description' => $description ?: null,
    'file_path' => $relative,
];
$cols = [];
foreach ($data as $col => $val) {
    if (column_exists($pdo, 'media_items', $col)) {
        $cols[$col] = $val;
    }
}
$columns = array_keys($cols);
$placeholders = array_map(fn($c) => ':' . $c, $columns);
$stmt = $pdo->prepare("INSERT INTO media_items (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")");
$stmt->execute($cols);

header('Location: ../views/media.php?uploaded=1');
exit;
