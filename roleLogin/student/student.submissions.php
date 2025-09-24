<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="student.submissions.css">
<div class="main-content">
  <div class="submissions-header">
    <h1>Submissions</h1>
    <p>All semesters are shown below.</p>
  </div>
  <div class="submissions-panels">
    <?php
    $studentId = $_SESSION['userid'];
    $studentBatch = '';
    $batchRes = $conn->query("SELECT batch FROM users WHERE usersId = $studentId LIMIT 1");
    if ($batchRes && $batchRes->num_rows > 0) {
      $studentBatch = $batchRes->fetch_assoc()['batch'];
    }
    for ($i = 1; $i <= 8; $i++) {
      echo '<div class="semester-panel">';
      echo '<button class="panel-toggle" onclick="togglePanel(this)">';
      echo '<span class="panel-arrow">&#9654;</span> Semester ' . $i . '</button>';
      echo '<div class="panel-content" style="display:none;">';

      // Fetch assignments for this semester and student's batch
      $result = $conn->query("SELECT * FROM assignments WHERE semester = $i AND batch = '" . $conn->real_escape_string($studentBatch) . "'");
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $assignmentId = $row['assignmentId'];
          $topic = htmlspecialchars($row['topic']);
          $desc = htmlspecialchars($row['description']);

          // Check if already submitted by this student
          $subRes = $conn->query("SELECT * FROM submissions WHERE assignmentId = $assignmentId AND studentId = $studentId");
          $submitted = ($subRes && $subRes->num_rows > 0);

          echo "<div class='assignment'>";
          echo "<h4>$topic</h4>";
          echo "<p>$desc</p>";
          if ($submitted) {
            $subRow = $subRes->fetch_assoc();
            // Only show the link to the student who submitted
            if ($subRow['studentId'] == $studentId) {
              echo "<span style='color:green;'>Already submitted</span> ";
              echo "<a href='../uploads/{$subRow['filePath']}' target='_blank'>View PDF</a>";
            }
          } else {
            echo "<form action='submit_assignment.php' method='post' enctype='multipart/form-data'>";
            echo "<input type='hidden' name='assignmentId' value='$assignmentId'>";
            echo "<input type='file' name='pdfFile' accept='application/pdf' required>";
            echo "<button type='submit' class='submission-btn'>Submit PDF</button>";
            echo "</form>";
          }
          echo "</div>";
        }
      } else {
        echo "<div class='submission-desc'>No assignments for Semester $i.</div>";
      }
      echo '</div>';
      echo '</div>';
    }
    ?>
  </div>
  <script>
    function togglePanel(btn) {
      var panel = btn.parentElement;
      var content = panel.querySelector('.panel-content');
      var arrow = btn.querySelector('.panel-arrow');
      if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        arrow.innerHTML = '&#9660;';
      } else {
        content.style.display = 'none';
        arrow.innerHTML = '&#9654;';
      }
    }
  </script>
</div>
