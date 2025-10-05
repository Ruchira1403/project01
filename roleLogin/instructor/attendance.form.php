<?php
session_start();
include_once '../../includes/dbh.inc.php';
$instructorId = $_SESSION['userid'];
// Get all batches
$batchRes = $conn->query("SELECT DISTINCT batch FROM users WHERE usersRole = 'student'");
?>
<div class="modal-content">
  <span class="close-btn" onclick="closeAttendanceForm()">&times;</span>
  <h2>Mark Attendance</h2>
  <form id="attendanceBatchForm" onsubmit="return false;">
    <label for="batch">Select Batch:</label>
    <select name="batch" id="batch-select" required>
      <option value="">-- Select Batch --</option>
      <?php while($row = $batchRes->fetch_assoc()) { echo '<option value="'.htmlspecialchars($row['batch']).'">'.htmlspecialchars($row['batch']).'</option>'; } ?>
    </select>
    <button type="button" onclick="loadStudents()">Next</button>
  </form>
  <form id="attendanceStudentForm" style="display:none;" method="post" action="save.attendance.php">
    <input type="hidden" name="batch" id="selected-batch">
    <label for="attendance-date">Attendance Date:</label>
    <input type="date" name="attendanceDate" id="attendance-date" required value="<?php echo date('Y-m-d'); ?>">
    <label for="session-topic">Topic:</label>
    <input type="text" name="topic" id="session-topic" placeholder="e.g., Topographic Survey Field Work" required>
    <label for="session-location">Location:</label>
    <input type="text" name="locationGlobal" id="session-location" placeholder="e.g., North Campus Field" required>
    <div style="display:flex; gap:12px; align-items:center; margin:10px 0;">
      <div>
        <label for="start-time">Start Time:</label>
        <input type="time" name="startTimeGlobal" id="start-time" required>
      </div>
      <div>
        <label for="end-time">End Time:</label>
        <input type="time" name="endTimeGlobal" id="end-time" required>
      </div>
    </div>
    <div id="students-list"></div>
    <button type="submit">Save Attendance</button>
  </form>
</div>