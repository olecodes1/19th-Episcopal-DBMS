<?php
require_once '../db.php';
require_once '../includes/soft_delete.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ../views/media.php?error=invalid_id');
    exit;
}

try {
    $deletedId = soft_delete_row($pdo, 'media_items', 'media_id', $id, '../views/media.php');
    header('Location: ../views/media.php?deleted=1&deleted_item_id=' . (int)$deletedId);
    exit;
} catch (PDOException $e) {
    error_log('Media delete failed: ' . $e->getMessage());
    header('Location: ../views/media.php?error=delete_failed');
    exit;
}
