<?php
session_start();
require_once '../../includes/dbh.inc.php';

$studentId = $_SESSION['userid'];
$assignmentId = $_POST['assignmentId'];

if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['type'] === 'application/pdf') {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = uniqid() . "_" . basename($_FILES["pdfFile"]["name"]);
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES["pdfFile"]["tmp_name"], $targetFile)) {
        $sql = "INSERT INTO submissions (assignmentId, studentId, filePath) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $assignmentId, $studentId, $fileName);
        $stmt->execute();
        $stmt->close();
        header("Location: student.submissions.php?success=1");
        exit();
    } else {
        header("Location: student.submissions.php?error=upload");
        exit();
    }
} else {
    header("Location: student.submissions.php?error=invalidfile");
    exit();
}
