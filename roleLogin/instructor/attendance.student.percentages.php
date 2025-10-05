<?php
session_start();
include_once '../../includes/dbh.inc.php';
if (!isset($_SESSION['userid'])) { exit; }
$instructorId = intval($_SESSION['userid']);

// Get all unique sessions with their durations
$sessionsQuery = "SELECT DISTINCT attendanceDate, topic, startTime, endTime, 
                 TIMESTAMPDIFF(HOUR, startTime, endTime) as session_hours 
                 FROM attendance 
                 WHERE instructorId = $instructorId AND startTime IS NOT NULL AND endTime IS NOT NULL";
$sessionsResult = $conn->query($sessionsQuery);
$totalSessionHours = 0;
$sessions = [];

if ($sessionsResult && $sessionsResult->num_rows > 0) {
  while ($session = $sessionsResult->fetch_assoc()) {
    $sessionHours = $session['session_hours'];
    $totalSessionHours += $sessionHours;
    $sessions[] = [
      'date' => $session['attendanceDate'],
      'topic' => $session['topic'],
      'hours' => $sessionHours
    ];
  }
}

// Get all students and calculate their attendance percentages
$students = $conn->query("SELECT DISTINCT userUid FROM attendance WHERE instructorId = $instructorId ORDER BY userUid");
$studentData = [];

while ($student = $students->fetch_assoc()) {
  $studentId = $student['userUid'];
  $presentHours = 0;
  
  // Calculate present hours for this student
  foreach ($sessions as $session) {
    $presentQuery = "SELECT COUNT(*) as present FROM attendance 
                   WHERE instructorId = $instructorId 
                   AND userUid = '$studentId' 
                   AND attendanceDate = '{$session['date']}' 
                   AND topic = '{$session['topic']}' 
                   AND status = 'present'";
    $presentResult = $conn->query($presentQuery);
    if ($presentResult && $presentResult->fetch_assoc()['present'] > 0) {
      $presentHours += $session['hours'];
    }
  }
  
  // Calculate percentage based on hours
  $attendancePercentage = $totalSessionHours > 0 ? round(($presentHours / $totalSessionHours) * 100, 1) : 0;
  
  $studentData[] = [
    'studentId' => $studentId,
    'presentHours' => $presentHours,
    'totalHours' => $totalSessionHours,
    'percentage' => $attendancePercentage,
    'status' => $attendancePercentage < 80 ? 'at-risk' : ($attendancePercentage == 100 ? 'perfect' : 'good')
  ];
}

// Sort by percentage (lowest first)
usort($studentData, function($a, $b) {
  return $a['percentage'] <=> $b['percentage'];
});

echo '<div class="student-attendance-details">';
echo '<h3>Student Attendance Percentages (Hours-Based)</h3>';
echo '<p>Total Session Hours: ' . $totalSessionHours . ' hours</p>';

if (!empty($studentData)) {
  echo '<table class="students-table">';
  echo '<tr><th>Student ID</th><th>Present Hours</th><th>Total Hours</th><th>Percentage</th><th>Status</th></tr>';
  
  foreach ($studentData as $student) {
    $statusClass = '';
    $statusText = '';
    
    switch ($student['status']) {
      case 'at-risk':
        $statusClass = 'status-at-risk';
        $statusText = 'At Risk (< 80%)';
        break;
      case 'perfect':
        $statusClass = 'status-perfect';
        $statusText = 'Perfect (100%)';
        break;
      default:
        $statusClass = 'status-good';
        $statusText = 'Good (≥ 80%)';
        break;
    }
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($student['studentId']) . '</td>';
    echo '<td>' . $student['presentHours'] . ' hours</td>';
    echo '<td>' . $student['totalHours'] . ' hours</td>';
    echo '<td>' . $student['percentage'] . '%</td>';
    echo '<td class="' . $statusClass . '">' . $statusText . '</td>';
    echo '</tr>';
  }
  
  echo '</table>';
  
  // Summary statistics
  $atRiskCount = count(array_filter($studentData, function($s) { return $s['status'] == 'at-risk'; }));
  $perfectCount = count(array_filter($studentData, function($s) { return $s['status'] == 'perfect'; }));
  $goodCount = count(array_filter($studentData, function($s) { return $s['status'] == 'good'; }));
  
  echo '<div class="attendance-summary-stats">';
  echo '<h4>Summary:</h4>';
  echo '<p>Total Students: ' . count($studentData) . '</p>';
  echo '<p>At Risk (< 80%): ' . $atRiskCount . '</p>';
  echo '<p>Good (80-99%): ' . $goodCount . '</p>';
  echo '<p>Perfect (100%): ' . $perfectCount . '</p>';
  echo '</div>';
  
} else {
  echo '<p>No attendance data found.</p>';
}

echo '</div>';
?>

<style>
.student-attendance-details {
  margin: 20px 0;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
}

.students-table {
  width: 100%;
  border-collapse: collapse;
  margin: 15px 0;
}

.students-table th,
.students-table td {
  padding: 10px;
  text-align: left;
  border: 1px solid #ddd;
}

.students-table th {
  background-color: #f2f2f2;
  font-weight: bold;
}

.status-at-risk {
  color: #d32f2f;
  font-weight: bold;
}

.status-perfect {
  color: #2e7d32;
  font-weight: bold;
}

.status-good {
  color: #1976d2;
  font-weight: bold;
}

.attendance-summary-stats {
  margin-top: 20px;
  padding: 15px;
  background: #e3f2fd;
  border-radius: 5px;
}

.attendance-summary-stats h4 {
  margin-top: 0;
  color: #1976d2;
}
</style>
