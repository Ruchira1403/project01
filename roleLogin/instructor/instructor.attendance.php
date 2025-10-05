<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="instructor.attendanceform.css">
<style>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-width: 90%;
  max-height: 90%;
  overflow-y: auto;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.mark-btn {
  background-color: #2196F3;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.mark-btn:hover {
  background-color: #1976D2;
}
</style>
<div class="main-content">
  <div class="dashboard-header">
    <h1>Attendance Management</h1>
    <p>Track and manage student attendance for field sessions.</p>
  </div>
  <div class="attendance-summary">
    <div class="summary-card">
      <div class="summary-title">Total Hours</div>
      <div class="summary-value">
        <?php
        $instructorId = $_SESSION['userid'];
        $hoursResult = $conn->query("SELECT SUM(TIMESTAMPDIFF(HOUR, startTime, endTime)) as total_hours FROM (SELECT DISTINCT attendanceDate, topic, startTime, endTime FROM attendance WHERE instructorId = $instructorId AND startTime IS NOT NULL AND endTime IS NOT NULL) as unique_sessions");
        $totalHours = $hoursResult ? $hoursResult->fetch_assoc()['total_hours'] ?? 0 : 0;
        echo $totalHours;
        ?>
      </div>
      <div class="summary-desc">This semester</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Overall Attendance</div>
      <div class="summary-value summary-attendance">
        <?php
        // Calculate overall attendance based on hours
        $totalHoursResult = $conn->query("SELECT SUM(TIMESTAMPDIFF(HOUR, startTime, endTime)) as total_hours FROM (SELECT DISTINCT attendanceDate, topic, startTime, endTime FROM attendance WHERE instructorId = $instructorId AND startTime IS NOT NULL AND endTime IS NOT NULL) as unique_sessions");
        $totalHours = $totalHoursResult ? $totalHoursResult->fetch_assoc()['total_hours'] ?? 0 : 0;
        
        // Calculate total possible hours for all students
        $studentsCount = $conn->query("SELECT COUNT(DISTINCT userUid) as cnt FROM attendance WHERE instructorId = $instructorId")->fetch_assoc()['cnt'] ?? 0;
        $totalPossibleHours = $totalHours * $studentsCount;
        
        // Calculate total present hours
        $presentHoursResult = $conn->query("SELECT SUM(TIMESTAMPDIFF(HOUR, a.startTime, a.endTime)) as present_hours 
                                           FROM attendance a 
                                           INNER JOIN (SELECT DISTINCT attendanceDate, topic, startTime, endTime FROM attendance WHERE instructorId = $instructorId AND startTime IS NOT NULL AND endTime IS NOT NULL) s 
                                           ON a.attendanceDate = s.attendanceDate AND a.topic = s.topic 
                                           WHERE a.instructorId = $instructorId AND a.status = 'present'");
        $presentHours = $presentHoursResult ? $presentHoursResult->fetch_assoc()['present_hours'] ?? 0 : 0;
        
        $percent = $totalPossibleHours > 0 ? round(($presentHours/$totalPossibleHours)*100) : 0;
        echo $percent . '%';
        ?>
      </div>
      <div class="summary-desc">Class average (hours-based)</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">Perfect Attendance</div>
      <div class="summary-value">
        <?php
        // Count students with 100% attendance based on hours
        $perfect = 0;
        $students = $conn->query("SELECT DISTINCT userUid FROM attendance WHERE instructorId = $instructorId");
        
        // Get total session hours
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
          
          // Check if student has 100% attendance (all hours)
          if ($presentHours >= $totalSessionHours && $totalSessionHours > 0) {
            $perfect++;
          }
        }
        echo $perfect;
        ?>
      </div>
      <div class="summary-desc">Students with 100% (hours-based)</div>
    </div>
    <div class="summary-card">
      <div class="summary-title">At Risk</div>
      <div class="summary-value summary-risk">
        <?php
        $risk = 0;
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
        
        // Calculate each student's attendance percentage based on hours
        $students = $conn->query("SELECT DISTINCT userUid FROM attendance WHERE instructorId = $instructorId");
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
          $attendancePercentage = $totalSessionHours > 0 ? ($presentHours / $totalSessionHours) * 100 : 0;
          if ($attendancePercentage < 80) {
            $risk++;
          }
        }
        echo $risk;
        ?>
      </div>
      <div class="summary-desc">Below 80% attendance (hours-based)</div>
    </div>
  </div>
  <div style="margin:32px 0 0 0;">
    <button class="mark-btn" onclick="showAttendanceForm()">Mark Attendance</button>
    <button class="mark-btn" onclick="showStudentPercentages()" style="margin-left: 10px; background-color: #4CAF50;">View Student Percentages</button>
  </div>
  <div class="attendance-view-grid">
    <div class="view-card" style="width:320px;">
      <div class="view-card-title">Session Calendar</div>
      <div id="calendar"></div>
      <div style="margin-top:12px;"></div>
    </div>
    <div class="view-card" style="flex:1;">
      <div class="view-card-title">Recent Sessions</div>
      <div id="recent-sessions" class="recent-list"></div>
    </div>
  </div>
  <div id="attendance-by-date" class="bydate-card"></div>
  <div id="attendance-form-modal" class="modal" style="display:none;"></div>
</div>
<script>
function showAttendanceForm() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'attendance.form.php', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('attendance-form-modal').innerHTML = '<div class="modal-content">' + xhr.responseText + '</div>';
      document.getElementById('attendance-form-modal').style.display = 'block';
    }
  };
  xhr.send();
}
function closeAttendanceForm() {
  document.getElementById('attendance-form-modal').style.display = 'none';
}
function showStudentPercentages() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'attendance.student.percentages.php', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('attendance-form-modal').innerHTML = '<div class="modal-content">' + xhr.responseText + '<br><button onclick="closeAttendanceForm()" style="margin-top: 20px; padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button></div>';
      document.getElementById('attendance-form-modal').style.display = 'block';
    }
  };
  xhr.send();
}
function loadStudents() {
  var batchSelect = document.getElementById('batch-select');
  if (!batchSelect) return;
  var batch = batchSelect.value;
  if (!batch) return;
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'attendance.students.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var batchForm = document.getElementById('attendanceBatchForm');
      var studentForm = document.getElementById('attendanceStudentForm');
      if (batchForm) batchForm.style.display = 'none';
      if (studentForm) studentForm.style.display = 'block';
      var selectedBatch = document.getElementById('selected-batch');
      if (selectedBatch) selectedBatch.value = batch;
      var list = document.getElementById('students-list');
      if (list) list.innerHTML = xhr.responseText;
    }
  };
  xhr.send('batch=' + encodeURIComponent(batch));
}
function loadAttendanceByDate(dateStr, topic) {
  if (!dateStr) return;
  var xhr = new XMLHttpRequest();
  var url = 'attendance.bydate.php?date=' + encodeURIComponent(dateStr);
  if (topic) url += '&topic=' + encodeURIComponent(topic);
  xhr.open('GET', url, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('attendance-by-date').innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}
function loadRecentSessions() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'attendance.recent.php', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('recent-sessions').innerHTML = xhr.responseText;
    }
  };
  xhr.send();
}
// init
document.addEventListener('DOMContentLoaded', function(){
  loadRecentSessions();
  renderCalendar(new Date());
  loadCalendarSessions();
});

// Simple calendar
function renderCalendar(date){
  var year = date.getFullYear();
  var month = date.getMonth();
  var first = new Date(year, month, 1);
  var last = new Date(year, month+1, 0);
  var startDay = first.getDay();
  var days = last.getDate();
  var html = '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">'
    + '<button type="button" onclick="prevMonth()">◀</button>'
    + '<div>'+ first.toLocaleString('default',{month:'long'}) +' '+year+'</div>'
    + '<button type="button" onclick="nextMonth()">▶</button>'
    + '</div>';
  html += '<table class="students-table"><tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr><tr>';
  for (var i=0;i<startDay;i++){ html += '<td></td>'; }
  for (var d=1; d<=days; d++){
    var ds = d<10? '0'+d : ''+d;
    var m = (month+1)<10? '0'+(month+1): ''+(month+1);
    var iso = year+'-'+m+'-'+ds;
    html += '<td style="cursor:pointer;" onclick="onCalendarDate(\''+iso+'\')">'+d+'</td>';
    if ((startDay + d) % 7 === 0) html += '</tr><tr>';
  }
  html += '</tr></table>';
  document.getElementById('calendar').innerHTML = html;
  // load filters for today by default
  var today = new Date();
  var tm = (today.getMonth()+1)<10? '0'+(today.getMonth()+1): ''+(today.getMonth()+1);
  var td = today.getDate()<10? '0'+today.getDate(): ''+today.getDate();
  onCalendarDate(today.getFullYear()+'-'+tm+'-'+td);
}
var currentMonthDate = new Date();
function prevMonth(){ currentMonthDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth()-1, 1); renderCalendar(currentMonthDate); }
function nextMonth(){ currentMonthDate = new Date(currentMonthDate.getFullYear(), currentMonthDate.getMonth()+1, 1); renderCalendar(currentMonthDate); }
function onCalendarDate(iso){
  loadAttendanceByDate(iso);
}
function loadCalendarSessions() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'attendance.sessions.meta.php?calendar=1', true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      try {
        var data = JSON.parse(xhr.responseText);
        // Highlight dates with sessions
        data.dates.forEach(function(date) {
          var cells = document.querySelectorAll('#calendar td');
          cells.forEach(function(cell) {
            if (cell.textContent.trim() === date.split('-')[2]) {
              cell.style.backgroundColor = '#e3f2fd';
              cell.style.fontWeight = 'bold';
            }
          });
        });
      } catch(e) { /* ignore */ }
    }
  };
  xhr.send();
}
</script>
