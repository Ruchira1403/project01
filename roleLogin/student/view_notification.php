<?php
session_start();
include '../../includes/dbh.inc.php';
$userId = $_SESSION['userid'] ?? null;
$notificationId = $_POST['notification_id'] ?? null;

if ($userId && $notificationId) {
    // Check if already viewed
    $check = $conn->prepare("SELECT 1 FROM notification_views WHERE notificationId=? AND userId=?");
    $check->bind_param('ii', $notificationId, $userId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        // Not viewed yet, insert and increment
        $insert = $conn->prepare("INSERT INTO notification_views (notificationId, userId) VALUES (?, ?)");
        $insert->bind_param('ii', $notificationId, $userId);
        $insert->execute();
        $update = $conn->prepare("UPDATE notifications SET views = views + 1 WHERE notificationId=?");
        $update->bind_param('i', $notificationId);
        $update->execute();
    }
    $check->close();
}
// No output needed for AJAX
?>