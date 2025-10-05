<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipmentId']) && isset($_POST['isActive'])) {
    $equipmentId = (int)$_POST['equipmentId'];
    $isActive = $_POST['isActive'] === 'true' ? 1 : 0;
    
    $sql = "UPDATE equipment SET isActive = ? WHERE equipmentId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $isActive, $equipmentId);
    
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    
    $stmt->close();
} else {
    echo 'invalid_request';
}
?>
