<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="instructor.attendance.css">
<div class="main-content">
  <div class="dashboard-header">
    <h1>Attendance Management</h1>
    <p>Track and manage student attendance for field sessions.</p>
  </div>
  <div class="attendance-summary">
    <div class="summary-card">
      <div class="summary-title">Total Sessions</div>
      <div class="summary-value">
        <?php
        $instructorId = $_SESSION['userid'];
        $totalSessions = $conn->query("SELECT COUNT(DISTINCT attendanceDate) as cnt FROM attendance WHERE instructorId = $instructorId")->fetch_assoc()['cnt'] ?? 0;
        echo $totalSessions;
        ?>
      </div>
      <div class="summary-desc">This semester</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Overall Attendance</div>
      <div class="summary-value summary-attendance">
        <?php
        $present = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE instructorId = $instructorId AND status = 'present'")->fetch_assoc()['cnt'] ?? 0;
        $total = $conn->query("SELECT COUNT(*) as cnt FROM attendance WHERE instructorId = $instructorId")->fetch_assoc()['cnt'] ?? 0;
        $percent = $total > 0 ? round(($present/$total)*100) : 0;
        echo $percent . '%';
        ?>
      </div>
      <div class="summary-desc">Class average</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Perfect Attendance</div>
      <div class="summary-value">
        <?php
        $perfect = $conn->query("SELECT COUNT(DISTINCT userUid) as cnt FROM attendance WHERE instructorId = $instructorId AND status = 'present' GROUP BY userUid HAVING COUNT(*) = (SELECT COUNT(DISTINCT attendanceDate) FROM attendance WHERE instructorId = $instructorId)")->num_rows ?? 0;
        echo $perfect;
        ?>
      </div>
      <div class="summary-desc">Students with 100%</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">At Risk</div>
      <div class="summary-value summary-risk">
        <?php
        $risk = 0;
        $students = $conn->query("SELECT userUid, COUNT(*) as total, SUM(status = 'present') as present FROM attendance WHERE instructorId = $instructorId GROUP BY userUid");
        while ($row = $students->fetch_assoc()) {
          $att = $row['total'] > 0 ? ($row['present']/$row['total'])*100 : 0;
          if ($att < 75) $risk++;
        }
        echo $risk;
        ?>
      </div>
      <div class="summary-desc">Below 75% attendance</div>
    </div>
  </div>
  <div style="margin:32px 0 0 0;">
    <button class="mark-btn" onclick="showAttendanceForm()">Mark Attendance</button>
  </div>
  <div id="attendance-form-modal" class="modal" style="display:none;"></div>
</div>
<script>
function showAttendanceForm() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'attendance.form.php', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('attendance-form-modal').innerHTML = xhr.responseText;
      document.getElementById('attendance-form-modal').style.display = 'block';
    }
  };
  xhr.send();
}
function closeAttendanceForm() {
  document.getElementById('attendance-form-modal').style.display = 'none';
}
</script>
