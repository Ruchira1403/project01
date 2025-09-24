<?php
session_start();
include_once '../../includes/dbh.inc.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userUid'], $_POST['status'], $_POST['attendanceDate'], $_POST['batch'])) {
  $instructorId = $_SESSION['userid'];
  $batch = $conn->real_escape_string($_POST['batch']);
  $attendanceDate = $conn->real_escape_string($_POST['attendanceDate']);
  $userUids = $_POST['userUid'];
  $statuses = $_POST['status'];
  $success = true;
  for ($i = 0; $i < count($userUids); $i++) {
    $userUid = $conn->real_escape_string($userUids[$i]);
    $status = $conn->real_escape_string($statuses[$i]);
    $sql = "INSERT INTO attendance (instructorId, userUid, batch, status, attendanceDate) VALUES ($instructorId, '$userUid', '$batch', '$status', '$attendanceDate') ON DUPLICATE KEY UPDATE status='$status'";
    if (!$conn->query($sql)) {
      $success = false;
    }
  }
  if ($success) {
    echo '<script>alert("Attendance saved successfully!");window.location.href="instructor.attendance.php";</script>';
  } else {
    echo '<script>alert("Error saving attendance.");window.location.href="instructor.attendance.php";</script>';
  }
} else {
  header('Location: instructor.attendance.php');
  exit;
}
