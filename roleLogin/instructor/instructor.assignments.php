<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $instructorId = $_SESSION['userid'];
  $semester = $_POST['semester'];
  $topic = $_POST['topic'];
  $description = $_POST['description'];
  $batch = $_POST['batch'];
  $dueDate = $_POST['dueDate'];
  $sql = "INSERT INTO assignments (instructorId, semester, topic, description, batch, dueDate) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iissss", $instructorId, $semester, $topic, $description, $batch, $dueDate);
  $stmt->execute();
  $stmt->close();
  echo "<div style='color:green; margin:16px;'>Assignment created successfully!</div>";
}
?>
<link rel="stylesheet" href="instructor.assignments.css">
<div class="main-content">
  <div class="dashboard-header">
    <h1>Create Assignment</h1>
    <form action="instructor.assignments.php" method="post" style="max-width:400px;">
      <label for="semester">Semester</label>
      <select name="semester" id="semester" required>
        <?php for ($i=1; $i<=8; $i++) echo "<option value='$i'>Semester $i</option>"; ?>
      </select>
      <label for="batch">Batch</label>
      <select name="batch" id="batch" required>
        <?php
        $batchRes = $conn->query("SELECT DISTINCT batch FROM users WHERE usersRole='student' AND batch IS NOT NULL AND batch != '' ORDER BY batch ASC");
        while ($row = $batchRes->fetch_assoc()) {
          $batchVal = htmlspecialchars($row['batch']);
          echo "<option value='$batchVal'>$batchVal</option>";
        }
        ?>
      </select>
      <label for="topic">Topic</label>
      <input type="text" name="topic" id="topic" required placeholder="Assignment Topic">
      <label for="description">Description</label>
      <textarea name="description" id="description" placeholder="Description"></textarea>
      <label for="dueDate">Due Date</label>
      <input type="date" name="dueDate" id="dueDate" required>
      <button type="submit" class="submission-btn" style="margin-top:12px;">Create Assignment</button>
    </form>
  </div>
</div>
