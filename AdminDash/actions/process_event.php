<?php
require_once '../db.php';
require_once '../includes/auth.php';
require_once '../includes/feature_tables.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forms/add_event.php"); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

$name        = trim($_POST['event_name']   ?? '');
$date        = $_POST['event_date']        ?: null;
$location    = trim($_POST['location']     ?? '');
$description = trim($_POST['description'] ?? '');
$conferenceId       = (int)($_POST['conference_id']        ?? 0) ?: null;
$episcopalDistrictId = (int)($_POST['episcopal_district_id'] ?? 0) ?: null;
$attendance = (int)($_POST['attendance_count'] ?? 0);

if (!$name || !$date) {
    header("Location: ../forms/add_event.php?error=missing_fields"); exit;
}

try {
    $data = [
        'event_name'          => $name,
        'event_date'          => $date,
        'location'            => $location ?: null,
        'description'         => $description ?: null,
        'conference_id'       => $conferenceId,
        'episcopal_district_id' => $episcopalDistrictId,
        'attendance_count'    => $attendance,
    ];
    $insert = [];
    foreach ($data as $col => $val) {
        if (column_exists($pdo, 'events', $col)) {
            $insert[$col] = $val;
        }
    }
    $cols = array_keys($insert);
    $placeholders = array_map(fn($c) => ':' . $c, $cols);
    $stmt = $pdo->prepare("INSERT INTO events (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")");
    $stmt->execute($insert);
    header("Location: ../views/events.php?success=1"); exit;
} catch (PDOException $e) {
    error_log("Event insert failed: " . $e->getMessage());
    header("Location: ../forms/add_event.php?error=database_error"); exit;
}
