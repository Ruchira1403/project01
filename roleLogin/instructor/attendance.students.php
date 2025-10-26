<?php
session_start();
include_once '../../includes/dbh.inc.php';
if (isset($_POST['batch'])) {
  $batch = $conn->real_escape_string($_POST['batch']);
  $students = $conn->query("SELECT usersUid FROM users WHERE batch = '$batch' AND usersRole = 'student'");
  if ($students && $students->num_rows > 0) {
    echo '<table class="students-table"><tr><th>Student UID</th><th>Status</th></tr>';
    while ($row = $students->fetch_assoc()) {
      $uid = htmlspecialchars($row['usersUid']);
      echo '<tr>';
      echo '<td><input type="hidden" name="userUid[]" value="'.$uid.'">'.$uid.'</td>';
      // Add an option for students who are not part of this group. These will be ignored when saving.
      echo '<td><select name="status[]"><option value="present">Present</option><option value="absent">Absent</option><option value="not_in_group">Not in group</option></select></td>';
      echo '</tr>';
    }
    echo '</table>';
  } else {
    echo '<div>No students found for this batch.</div>';
  }
}
?>