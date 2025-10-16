<?php
session_start();
require_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $resourceId = (int)$_POST['resource_id'];
    $instructorId = $_SESSION['userid'];
    
    // Verify that the resource belongs to this instructor
    $checkSql = "SELECT filePath FROM resources WHERE resourceId = ? AND instructorId = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $resourceId, $instructorId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $resource = $result->fetch_assoc();
        $filePath = $resource['filePath'];
        
        // Delete the file from filesystem
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete from database (cascade will handle resource_access)
        $deleteSql = "DELETE FROM resources WHERE resourceId = ? AND instructorId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("ii", $resourceId, $instructorId);
        
        if ($deleteStmt->execute()) {
            echo '<script>alert("Resource deleted successfully!"); window.location.href="instructor.resources.php";</script>';
        } else {
            echo '<script>alert("Error deleting resource from database!"); window.location.href="instructor.resources.php";</script>';
        }
        
        $deleteStmt->close();
    } else {
        echo '<script>alert("Resource not found or you do not have permission to delete it!"); window.location.href="instructor.resources.php";</script>';
    }
    
    $checkStmt->close();
} else {
    header('Location: instructor.resources.php');
    exit();
}
?>
