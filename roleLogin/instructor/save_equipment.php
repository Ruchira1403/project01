<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipmentName'])) {
    $equipmentName = trim($_POST['equipmentName']);
    $equipmentCategory = $_POST['equipmentCategory'];
    $equipmentDescription = trim($_POST['equipmentDescription']);
    $equipmentQuantity = (int)$_POST['equipmentQuantity'];
    
    // Check if equipment already exists
    $checkRes = $conn->query("SELECT equipmentId FROM equipment WHERE name = '" . $conn->real_escape_string($equipmentName) . "'");
    
    if ($checkRes && $checkRes->num_rows > 0) {
        echo '<script>alert("Equipment with this name already exists!"); window.history.back();</script>';
        exit();
    }
    
    // Insert new equipment
    $sql = "INSERT INTO equipment (name, description, category, totalQuantity, availableQuantity, isActive) VALUES (?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $equipmentName, $equipmentDescription, $equipmentCategory, $equipmentQuantity, $equipmentQuantity);
    
    if ($stmt->execute()) {
        echo '<script>alert("Equipment added successfully!"); window.location.href="instructor.field_tasks.php";</script>';
    } else {
        echo '<script>alert("Error adding equipment. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.field_tasks.php');
    exit();
}
?>
