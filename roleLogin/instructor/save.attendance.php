<?php
session_start();
include_once '../../includes/dbh.inc.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['userUid'], $_POST['status'], $_POST['attendanceDate'], $_POST['batch'])) {
  $instructorId = $_SESSION['userid'];
  $batch = $conn->real_escape_string($_POST['batch']);
  $attendanceDate = $conn->real_escape_string($_POST['attendanceDate']);
  $topic = isset($_POST['topic']) ? $conn->real_escape_string($_POST['topic']) : '';
  $location = isset($_POST['locationGlobal']) ? $conn->real_escape_string($_POST['locationGlobal']) : '';
  $startTime = isset($_POST['startTimeGlobal']) ? $conn->real_escape_string($_POST['startTimeGlobal']) : '';
  $endTime = isset($_POST['endTimeGlobal']) ? $conn->real_escape_string($_POST['endTimeGlobal']) : '';
  $userUids = $_POST['userUid'];
  $statuses = $_POST['status'];
  $success = true;
  for ($i = 0; $i < count($userUids); $i++) {
    $userUid = $conn->real_escape_string($userUids[$i]);
    $status = $conn->real_escape_string($statuses[$i]);
    $sql = "INSERT INTO attendance (instructorId, userUid, batch, status, attendanceDate, topic, location, startTime, endTime) VALUES ($instructorId, '$userUid', '$batch', '$status', '$attendanceDate', '$topic', '$location', '$startTime', '$endTime') ON DUPLICATE KEY UPDATE status='$status', topic='$topic', location='$location', startTime='$startTime', endTime='$endTime'";
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
