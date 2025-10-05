<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = $_SESSION['userid'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $batch = $_POST['batch'];
    $semester = (int)$_POST['semester'];
    $assignedDate = $_POST['assignedDate'];
    $dueDate = $_POST['dueDate'];
    $status = $_POST['status'];
    $equipment = isset($_POST['equipment']) ? $_POST['equipment'] : [];
    $equipmentQuantities = [];
    
    // Get quantities for selected equipment
    foreach ($equipment as $equipmentName) {
        // Find equipment ID by name
        $equipmentRes = $conn->query("SELECT equipmentId FROM equipment WHERE name = '" . $conn->real_escape_string($equipmentName) . "'");
        if ($equipmentRes && $equipmentRes->num_rows > 0) {
            $equipmentId = $equipmentRes->fetch_assoc()['equipmentId'];
            $quantity = isset($_POST['quantity_' . $equipmentId]) ? (int)$_POST['quantity_' . $equipmentId] : 1;
            $equipmentQuantities[] = $equipmentName . ' (Qty: ' . $quantity . ')';
        }
    }
    
    $requiredEquipment = implode(', ', $equipmentQuantities);
    $instructions = trim($_POST['instructions']);
    
    // Handle PDF upload
    $pdfPath = null;
    if (isset($_FILES['taskPdf']) && $_FILES['taskPdf']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/field_tasks/';
        $allowedTypes = ['application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $fileInfo = $_FILES['taskPdf'];
        $fileName = $fileInfo['name'];
        $fileSize = $fileInfo['size'];
        $fileType = $fileInfo['type'];
        $fileTmpName = $fileInfo['tmp_name'];
        
        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            echo '<script>alert("Only PDF files are allowed!"); window.history.back();</script>';
            exit();
        }
        
        // Validate file size
        if ($fileSize > $maxSize) {
            echo '<script>alert("File size must be less than 10MB!"); window.history.back();</script>';
            exit();
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueFileName = 'task_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $uniqueFileName;
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $pdfPath = $uploadPath;
        } else {
            echo '<script>alert("Error uploading PDF file!"); window.history.back();</script>';
            exit();
        }
    }
    
    // Validate dates
    if (strtotime($assignedDate) > strtotime($dueDate)) {
        echo '<script>alert("Due date must be after assigned date!"); window.history.back();</script>';
        exit();
    }
    
    // Check if pdfPath column exists, if not add it
    $checkColumn = $conn->query("SHOW COLUMNS FROM field_tasks LIKE 'pdfPath'");
    if ($checkColumn->num_rows == 0) {
        $conn->query("ALTER TABLE field_tasks ADD COLUMN pdfPath VARCHAR(255) NULL");
    }
    
    // Insert field task
    if ($pdfPath) {
        $sql = "INSERT INTO field_tasks (instructorId, title, description, location, batch, semester, assignedDate, dueDate, status, requiredEquipment, instructions, pdfPath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssissssss", $instructorId, $title, $description, $location, $batch, $semester, $assignedDate, $dueDate, $status, $requiredEquipment, $instructions, $pdfPath);
    } else {
        $sql = "INSERT INTO field_tasks (instructorId, title, description, location, batch, semester, assignedDate, dueDate, status, requiredEquipment, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssisssss", $instructorId, $title, $description, $location, $batch, $semester, $assignedDate, $dueDate, $status, $requiredEquipment, $instructions);
    }
    
    if ($stmt->execute()) {
        echo '<script>alert("Field task created successfully! All students in batch ' . htmlspecialchars($batch) . ' can now view this task."); window.location.href="instructor.field_tasks.php";</script>';
    } else {
        echo '<script>alert("Error creating field task. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.field_tasks.php');
    exit();
}
?>