<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['taskId'])) {
    $taskId = (int)$_POST['taskId'];
    $instructorId = $_SESSION['userid'];
    
    // Delete field task (only if it belongs to this instructor)
    $sql = "DELETE FROM field_tasks WHERE taskId = ? AND instructorId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $taskId, $instructorId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'not_found';
        }
    } else {
        echo 'error';
    }
    
    $stmt->close();
} else {
    echo 'invalid_request';
}
?>
