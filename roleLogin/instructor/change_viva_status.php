<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructorId = $_SESSION['userid'];
    $sessionId = (int)$_POST['sessionId'];
    $newStatus = trim($_POST['status']);
    
    // Validate status
    $allowedStatuses = ['scheduled', 'completed', 'cancelled'];
    if (!in_array($newStatus, $allowedStatuses)) {
        echo '<script>alert("Invalid status!"); window.history.back();</script>';
        exit();
    }
    
    // Verify the session belongs to this instructor
    $verifyRes = $conn->query("SELECT sessionId, status FROM viva_sessions WHERE sessionId = $sessionId AND instructorId = $instructorId");
    if (!$verifyRes || $verifyRes->num_rows === 0) {
        echo '<script>alert("Invalid session!"); window.history.back();</script>';
        exit();
    }
    
    $currentSession = $verifyRes->fetch_assoc();
    $currentStatus = $currentSession['status'];
    
    // Update the status
    $sql = "UPDATE viva_sessions SET status = ?, updatedAt = NOW() WHERE sessionId = ? AND instructorId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $newStatus, $sessionId, $instructorId);
    
    if ($stmt->execute()) {
        $statusText = ucfirst($newStatus);
        echo '<script>alert("Status changed to ' . $statusText . ' successfully!"); window.location.href="instructor.viva_sessions.php";</script>';
    } else {
        echo '<script>alert("Error changing status. Please try again."); window.history.back();</script>';
    }
    
    $stmt->close();
} else {
    header('Location: instructor.viva_sessions.php');
    exit();
}
?>
