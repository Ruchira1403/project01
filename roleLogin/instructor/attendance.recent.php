<?php
session_start();
include_once '../../includes/dbh.inc.php';
if (!isset($_SESSION['userid'])) { exit; }
$instructorId = intval($_SESSION['userid']);


$res = $conn->query("SELECT attendanceDate, topic, MIN(startTime) as startTime, MAX(endTime) as endTime, MAX(location) as location, COUNT(DISTINCT userUid) as students, SUM(status='present') as present FROM attendance WHERE instructorId = $instructorId GROUP BY attendanceDate, topic ORDER BY attendanceDate DESC, MIN(startTime) ASC LIMIT 30");
if ($res && $res->num_rows > 0) {
  echo '<ul class="recent-ul">';
  while ($row = $res->fetch_assoc()) {
    $date = htmlspecialchars($row['attendanceDate']);
    $topic = htmlspecialchars($row['topic'] ?? '');
    $present = intval($row['present'] ?? 0);
    $students = intval($row['students'] ?? 0);
    $loc = htmlspecialchars($row['location'] ?? '');
    $timeRange = '';
    if (!empty($row['startTime']) || !empty($row['endTime'])) {
      $s = htmlspecialchars($row['startTime'] ?? '');
      $e = htmlspecialchars($row['endTime'] ?? '');
      $timeRange = ' (' . $s . ' - ' . $e . ')';
    }
    $title = $topic !== '' ? $topic : ('Session on ' . $date);
    $duration = '';
    if ($s && $e) {
      $start = new DateTime($s);
      $end = new DateTime($e);
      $diff = $start->diff($end);
      $hours = $diff->h;
      $minutes = $diff->i;
      if ($hours > 0) {
        $duration = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($minutes > 0) $duration .= ' ' . $minutes . ' min';
      } else {
        $duration = $minutes . ' min';
      }
    }
    
    echo '<li class="recent-item" onclick="loadAttendanceByDate(\''.$date.'\', \''.htmlspecialchars($topic, ENT_QUOTES).'\')">'
      . '<div class="recent-item-title">'
      . '<span>'.$title.'</span>'
      . '</div>'
      . '<div class="recent-item-meta">'
      . '<div class="date-time meta-row">📅 '.$date.$timeRange.'</div>'
      . ($loc ? '<div class="location meta-row">📍 '.$loc.'</div>' : '<div class="location meta-row">📍 No location</div>')
      . '<div class="attendance meta-row">👥 '.$present.'/'.$students.' present</div>'
      . ($duration ? '<div class="duration meta-row">⏱️ '.$duration.'</div>' : '<div class="duration meta-row">⏱️ No duration</div>')
      . '</div>'
      . '</li>';
  }
  echo '</ul>';
} else {
  echo '<div>No recent sessions.</div>';
}
?>

