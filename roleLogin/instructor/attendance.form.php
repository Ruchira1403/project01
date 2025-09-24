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
    <div id="students-list"></div>
    <button type="submit">Save Attendance</button>
  </form>
</div>
<script>
function loadStudents() {
  var batch = document.getElementById('batch-select').value;
  if (!batch) return;
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'attendance.students.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      document.getElementById('attendanceBatchForm').style.display = 'none';
      document.getElementById('attendanceStudentForm').style.display = 'block';
      document.getElementById('selected-batch').value = batch;
      document.getElementById('students-list').innerHTML = xhr.responseText;
    }
  };
  xhr.send('batch=' + encodeURIComponent(batch));
}
</script>
