<?php
require_once '../db.php';
require_once '../includes/auth.php';
require_once '../includes/feature_tables.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/events.php"); exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}

$id          = (int)$_POST['event_id'];
$name        = trim($_POST['event_name']   ?? '');
$date        = $_POST['event_date']        ?: null;
$location    = trim($_POST['location']     ?? '');
$description = trim($_POST['description'] ?? '');
$conferenceId        = (int)($_POST['conference_id']        ?? 0) ?: null;
$episcopalDistrictId = (int)($_POST['episcopal_district_id'] ?? 0) ?: null;
$attendance = (int)($_POST['attendance_count'] ?? 0);

try {
    $updates = [
        'event_name'          => $name,
        'event_date'          => $date,
        'location'            => $location ?: null,
        'description'         => $description ?: null,
        'conference_id'       => $conferenceId,
        'episcopal_district_id' => $episcopalDistrictId,
        'attendance_count'    => $attendance,
    ];
    $setSql = [];
    $params = [];
    foreach ($updates as $col => $val) {
        if (column_exists($pdo, 'events', $col)) {
            $setSql[] = "$col = :$col";
            $params[$col] = $val;
        }
    }
    $params['event_id'] = $id;
    $stmt = $pdo->prepare("UPDATE events SET " . implode(', ', $setSql) . " WHERE event_id = :event_id");
    $stmt->execute($params);
    header("Location: ../views/events.php?updated=1"); exit;
} catch (PDOException $e) {
    error_log("Event update failed: " . $e->getMessage());
    header("Location: ../forms/edit_event.php?id=$id&error=database_error"); exit;
}
