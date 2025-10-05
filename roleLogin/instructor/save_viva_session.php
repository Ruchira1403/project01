<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = $_SESSION['userid'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = (int)$_POST['duration'];
    $location = trim($_POST['location']);
    $batch = $_POST['batch'];
    $semester = (int)$_POST['semester'];
    $sessionId = isset($_POST['sessionId']) && $_POST['sessionId'] ? (int)$_POST['sessionId'] : null;
    
    // Handle PDF upload
    $pdfPath = null;
    if (isset($_FILES['vivaPdf']) && $_FILES['vivaPdf']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $fileInfo = $_FILES['vivaPdf'];
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
        $uniqueFileName = 'viva_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadDir = '../../uploads/viva_sessions/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $uniqueFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $pdfPath = $uploadPath;
        } else {
            echo '<script>alert("Error uploading PDF file!"); window.history.back();</script>';
            exit();
        }
    }
    
    // Validate required fields
    if (empty($title) || empty($date) || empty($time) || empty($location) || empty($batch) || empty($semester)) {
        echo '<script>alert("Please fill in all required fields!"); window.history.back();</script>';
        exit();
    }
    
    // Validate date is not in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        echo '<script>alert("Viva date cannot be in the past!"); window.history.back();</script>';
        exit();
    }
    
    // Validate duration
    if ($duration < 30 || $duration > 300) {
        echo '<script>alert("Duration must be between 30 and 300 minutes!"); window.history.back();</script>';
        exit();
    }
    
    if ($sessionId) {
        // Update existing session
        if ($pdfPath) {
            $sql = "UPDATE viva_sessions SET title = ?, description = ?, date = ?, time = ?, duration = ?, location = ?, batch = ?, semester = ?, pdfPath = ?, updatedAt = NOW() WHERE sessionId = ? AND instructorId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiisssii", $title, $description, $date, $time, $duration, $location, $batch, $semester, $pdfPath, $sessionId, $instructorId);
        } else {
            $sql = "UPDATE viva_sessions SET title = ?, description = ?, date = ?, time = ?, duration = ?, location = ?, batch = ?, semester = ?, updatedAt = NOW() WHERE sessionId = ? AND instructorId = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssiisiii", $title, $description, $date, $time, $duration, $location, $batch, $semester, $sessionId, $instructorId);
        }
    } else {
        // Insert new session
        if ($pdfPath) {
            $sql = "INSERT INTO viva_sessions (instructorId, title, description, date, time, duration, location, batch, semester, pdfPath) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssissis", $instructorId, $title, $description, $date, $time, $duration, $location, $batch, $semester, $pdfPath);
        } else {
            $sql = "INSERT INTO viva_sessions (instructorId, title, description, date, time, duration, location, batch, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssissi", $instructorId, $title, $description, $date, $time, $duration, $location, $batch, $semester);
        }
    }
    
    if ($stmt->execute()) {
        $action = $sessionId ? 'updated' : 'created';
        echo '<script>alert("Viva session ' . $action . ' successfully!"); window.location.href="instructor.viva_sessions.php";</script>';
    } else {
        echo '<script>alert("Error saving viva session. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.viva_sessions.php');
    exit();
}
?>
