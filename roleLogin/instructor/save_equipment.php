<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipmentName'])) {
    $equipmentName = trim($_POST['equipmentName']);
    $equipmentCategory = $_POST['equipmentCategory'];
    $equipmentDescription = trim($_POST['equipmentDescription']);
    $equipmentQuantity = 1; // Default quantity since form doesn't have quantity field
    
    // Check if equipment already exists
    $checkRes = $conn->query("SELECT equipmentId FROM equipment WHERE name = '" . $conn->real_escape_string($equipmentName) . "'");
    
    if ($checkRes && $checkRes->num_rows > 0) {
        echo '<script>alert("Equipment with this name already exists!"); window.history.back();</script>';
        exit();
    }
    
    // Insert new equipment - using simpler table structure
    $sql = "INSERT INTO equipment (name, description, category, isActive) VALUES (?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        echo '<script>alert("Database error: ' . $conn->error . '"); window.history.back();</script>';
        exit();
    }
    
    $stmt->bind_param("sss", $equipmentName, $equipmentDescription, $equipmentCategory);
    
    if ($stmt->execute()) {
        echo '<script>alert("Equipment added successfully!"); window.location.href="instructor.field_tasks.php";</script>';
    } else {
        echo '<script>alert("Error adding equipment: ' . $stmt->error . '"); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.field_tasks.php');
    exit();
}
?>
