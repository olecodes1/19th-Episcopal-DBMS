<?php
require_once '../db.php';
require_once '../includes/soft_delete.php';
require_once '../includes/auth.php';

if (!isset($_GET['id'])) { header("Location: ../views/conferences.php"); exit; }

// Only superadmin can delete conferences
require_role('superadmin');

$id = (int)$_GET['id'];

try {
    $deletedId = soft_delete_row($pdo, 'conferences', 'conference_id', $id, '../views/conferences.php');
    header("Location: ../views/conferences.php?deleted=1&deleted_item_id=" . (int)$deletedId); exit;
} catch (PDOException $e) {
    error_log("Conference delete failed: " . $e->getMessage());
    header("Location: ../views/conferences.php?error=delete_failed"); exit;
}
