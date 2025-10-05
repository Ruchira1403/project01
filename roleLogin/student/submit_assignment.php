<?php
session_start();
require_once '../../includes/dbh.inc.php';

$studentId = $_SESSION['userid'];
$assignmentId = $_POST['assignmentId'];

// Check if file was uploaded
if (isset($_FILES['submissionFile']) && $_FILES['submissionFile']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    
    $fileInfo = $_FILES['submissionFile'];
    $fileName = $fileInfo['name'];
    $fileSize = $fileInfo['size'];
    $fileType = $fileInfo['type'];
    $fileTmpName = $fileInfo['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file types: PDF and DWG
    $allowedTypes = ['application/pdf', 'application/dwg', 'image/vnd.dwg'];
    $allowedExtensions = ['pdf', 'dwg'];
    $maxSize = 20 * 1024 * 1024; // 20MB
    
    // Validate file extension
    if (!in_array($fileExtension, $allowedExtensions)) {
        header("Location: student.submissions.php?error=invalidfile&message=" . urlencode("Only PDF and DWG files are allowed!"));
        exit();
    }
    
    // Validate file type (MIME type)
    if (!in_array($fileType, $allowedTypes) && $fileExtension !== 'dwg') {
        header("Location: student.submissions.php?error=invalidfile&message=" . urlencode("Invalid file type! Only PDF and DWG files are allowed."));
        exit();
    }
    
    // Validate file size
    if ($fileSize > $maxSize) {
        header("Location: student.submissions.php?error=filesize&message=" . urlencode("File size must be less than 20MB!"));
        exit();
    }
    
    // Generate unique filename
    $uniqueFileName = 'assignment_' . $assignmentId . '_' . $studentId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetFile = $targetDir . $uniqueFileName;
    
    if (move_uploaded_file($fileTmpName, $targetFile)) {
        $sql = "INSERT INTO submissions (assignmentId, studentId, filePath) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $assignmentId, $studentId, $uniqueFileName);
        $stmt->execute();
        $stmt->close();
        header("Location: student.submissions.php?success=1&message=" . urlencode("Assignment submitted successfully!"));
        exit();
    } else {
        header("Location: student.submissions.php?error=upload&message=" . urlencode("Error uploading file. Please try again."));
        exit();
    }
} else {
    header("Location: student.submissions.php?error=nofile&message=" . urlencode("Please select a file to submit."));
    exit();
}
