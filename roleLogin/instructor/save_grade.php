<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submissionId'])) {
    $instructorId = $_SESSION['userid'];
    $submissionId = (int)$_POST['submissionId'];
    $grade = isset($_POST['grade']) ? trim($_POST['grade']) : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;
    
    // Validate grade - check if it's a valid letter grade
    $validGrades = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'E'];
    if ($grade !== null && $grade !== '' && !in_array($grade, $validGrades)) {
        echo '<script>alert("Please select a valid grade!"); window.history.back();</script>';
        exit();
    }
    
    // Update the submission with grade and comment
    $sql = "UPDATE submissions SET grade = ?, comment = ?, gradedAt = NOW(), gradedBy = ? WHERE submissionId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $grade, $comment, $instructorId, $submissionId);
    
    if ($stmt->execute()) {
        echo '<script>alert("Grade saved successfully!"); window.location.href="instructor.submissions.php";</script>';
    } else {
        echo '<script>alert("Error saving grade. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.submissions.php');
    exit();
}
?>
