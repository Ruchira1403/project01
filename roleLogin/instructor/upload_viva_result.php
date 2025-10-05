<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = $_SESSION['userid'];
    $sessionId = (int)$_POST['sessionId'];
    $batch = trim($_POST['batch']);
    
    // Verify the session belongs to this instructor
    $verifyRes = $conn->query("SELECT sessionId FROM viva_sessions WHERE sessionId = $sessionId AND instructorId = $instructorId AND status = 'completed'");
    if (!$verifyRes || $verifyRes->num_rows === 0) {
        echo '<script>alert("Invalid session or session not completed!"); window.history.back();</script>';
        exit();
    }
    
    // Handle PDF upload
    $resultPdfPath = null;
    if (isset($_FILES['resultPdf']) && $_FILES['resultPdf']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        $fileInfo = $_FILES['resultPdf'];
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
        $uniqueFileName = 'result_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadDir = '../../uploads/viva_results/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $uniqueFileName;
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpName, $uploadPath)) {
            $resultPdfPath = $uploadPath;
        } else {
            echo '<script>alert("Error uploading PDF file!"); window.history.back();</script>';
            exit();
        }
    } else {
        echo '<script>alert("Please select a PDF file to upload!"); window.history.back();</script>';
        exit();
    }
    
    // Update the session with result PDF path
    $sql = "UPDATE viva_sessions SET resultPdfPath = ?, resultUploadedAt = NOW() WHERE sessionId = ? AND instructorId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $resultPdfPath, $sessionId, $instructorId);
    
    if ($stmt->execute()) {
        echo '<script>alert("Viva result uploaded successfully!"); window.location.href="instructor.viva_sessions.php";</script>';
    } else {
        echo '<script>alert("Error uploading result. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.viva_sessions.php');
    exit();
}
?>
