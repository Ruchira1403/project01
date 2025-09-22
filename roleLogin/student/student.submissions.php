<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="student.submissions.css">
<div class="main-content">
  <div class="submissions-header">
    <h1>Submissions</h1>
    <p>All semesters are shown below.</p>
  </div>
  <div class="submissions-panels">
    <?php
    for ($i = 1; $i <= 8; $i++) {
      echo '<div class="semester-panel">';
      echo '<button class="panel-toggle" onclick="togglePanel(this)">';
      echo '<span class="panel-arrow">&#9654;</span> Semester ' . $i . '</button>';
      echo '<div class="panel-content" style="display:none;">';
      echo '<div class="submission-desc">Upload your submissions for Semester ' . $i . '.</div>';
      echo '<button class="submission-btn">Upload</button>';
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
