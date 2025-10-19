<?php
session_start();
include_once '../../includes/dbh.inc.php';
header('Content-Type: application/json');
if (!isset($_SESSION['userid'])) { echo json_encode(['topics'=>[], 'starts'=>[], 'dates'=>[]]); exit; }
$instructorId = intval($_SESSION['userid']);

// If calendar parameter is set, return dates with sessions
if (isset($_GET['calendar']) && $_GET['calendar'] == '1') {
  $dates = [];
  // Normalize to YYYY-MM-DD to avoid time components affecting comparisons
  $dRes = $conn->query("SELECT DISTINCT DATE_FORMAT(DATE(attendanceDate), '%Y-%m-%d') AS attendanceDate FROM attendance WHERE instructorId = $instructorId ORDER BY attendanceDate DESC");
  if ($dRes) { while($r = $dRes->fetch_assoc()) { $dates[] = $r['attendanceDate']; } }
  echo json_encode(['dates'=>$dates]);
  exit;
}

$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
if (!$date) { echo json_encode(['topics'=>[], 'starts'=>[]]); exit; }

$topics = [];
$starts = [];
$tRes = $conn->query("SELECT DISTINCT topic FROM attendance WHERE instructorId = $instructorId AND attendanceDate = '$date' AND topic IS NOT NULL AND topic <> '' ORDER BY topic");
if ($tRes) { while($r = $tRes->fetch_assoc()) { $topics[] = $r['topic']; } }
$sRes = $conn->query("SELECT DISTINCT startTime FROM attendance WHERE instructorId = $instructorId AND attendanceDate = '$date' AND startTime IS NOT NULL ORDER BY startTime");
if ($sRes) { while($r = $sRes->fetch_assoc()) { $starts[] = $r['startTime']; } }
echo json_encode(['topics'=>$topics, 'starts'=>$starts]);
?>

