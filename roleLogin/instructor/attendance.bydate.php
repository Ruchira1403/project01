<?php
session_start();
include_once '../../includes/dbh.inc.php';
if (!isset($_SESSION['userid'])) { exit; }
$instructorId = intval($_SESSION['userid']);
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
if (!$date) { echo '<div>Please select a date.</div>'; exit; }

$where = "a.instructorId = $instructorId AND a.attendanceDate = '$date'";

// If no topic is provided, list sessions (topic + time + location) for the date
if (!isset($_GET['topic']) || $_GET['topic'] === '') {
  $sess = $conn->query("SELECT topic, MIN(startTime) AS startTime, MAX(endTime) AS endTime, MAX(location) AS location FROM attendance a WHERE $where GROUP BY topic ORDER BY MIN(startTime)");
  if ($sess && $sess->num_rows > 0) {
    echo '<div class="bydate-header"><div class="bydate-topic">Sessions on '. htmlspecialchars($date) .'</div></div>';
    echo '<ul class="recent-ul">';
    while ($r = $sess->fetch_assoc()) {
      $tpc = htmlspecialchars($r['topic'] ?? '');
      $s = htmlspecialchars($r['startTime'] ?? '');
      $e = htmlspecialchars($r['endTime'] ?? '');
      $loc = htmlspecialchars($r['location'] ?? '');
      $meta = ($s || $e ? ' (' . $s . ' - ' . $e . ')' : '') . ($loc ? ' · ' . $loc : '');
      echo '<li class="recent-item" onclick="loadAttendanceByDate(\''.htmlspecialchars($date, ENT_QUOTES).'\', \''.$tpc.'\')">'
        . '<div class="recent-item-title">'.$tpc.'</div>'
        . '<div class="recent-item-meta">'.$meta.'</div>'
        . '</li>';
    }
    echo '</ul>';
  } else {
    echo '<div>No sessions for this date.</div>';
  }
  exit;
}

// Topic provided → show students for that topic
$topicFilter = $conn->real_escape_string($_GET['topic']);
$where .= " AND TRIM(a.topic) = TRIM('$topicFilter')";

// Header details reflecting current filters
$metaRes = $conn->query("SELECT MAX(a.topic) as topic, MIN(a.startTime) as startTime, MAX(a.endTime) as endTime, MAX(a.location) as location FROM attendance a WHERE $where");
$topic = '';
$timeRange = '';
if ($metaRes && $metaRes->num_rows > 0) {
  $t = $metaRes->fetch_assoc();
  $topic = htmlspecialchars($t['topic'] ?? '');
  $s = htmlspecialchars($t['startTime'] ?? '');
  $e = htmlspecialchars($t['endTime'] ?? '');
  if ($s || $e) { $timeRange = ' (' . $s . ' - ' . $e . ')'; }
  $loc = htmlspecialchars($t['location'] ?? '');
}
if ($topic) { echo '<div class="bydate-header"><div class="bydate-topic">'. $topic .'</div><div class="bydate-meta">'. htmlspecialchars($date) . $timeRange . ($loc ? ' · ' . $loc : '') .'</div></div>';
echo '<div style="margin:8px 0;"><button type="button" onclick="loadAttendanceByDate(\''.htmlspecialchars($date, ENT_QUOTES).'\')" style="padding:4px 8px; font-size:12px;">← Back to sessions</button></div>'; }
else { echo '<div class="bydate-header"><div class="bydate-topic">All topics</div><div class="bydate-meta">'. htmlspecialchars($date) .'</div></div>'; }
$res = $conn->query(
  "SELECT a.userUid,
          u.usersName,
          MAX(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS presentFlag
     FROM attendance a
     LEFT JOIN users u ON u.usersUid = a.userUid
    WHERE $where
 GROUP BY a.userUid, u.usersName
 ORDER BY u.usersName, a.userUid"
);
if ($res && $res->num_rows > 0) {
  echo '<table class="students-table"><tr><th>Student</th><th>Status</th></tr>';
  while ($row = $res->fetch_assoc()) {
    $uid = htmlspecialchars($row['userUid']);
    $name = htmlspecialchars($row['usersName'] ?? '');
    $label = $name ? ($name.' ('.$uid.')') : $uid;
    $status = intval($row['presentFlag']) === 1 ? 'present' : 'absent';
    echo '<tr>';
    echo '<td>'.$label.'</td>';
    echo '<td>'.$status.'</td>';
    echo '</tr>';
  }
  echo '</table>';
} else {
  echo '<div>No attendance records for this date.</div>';
}
?>

