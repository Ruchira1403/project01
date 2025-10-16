<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="students.submissionsk.css">
<div class="main-content" style="margin-top: 120px; margin-right: 40px;">
  <div class="page-header">
    <h1>📝 Submissions</h1>
    <p>View and manage your assignment submissions across all semesters.</p>
  </div>
  
  <?php
  // Display success/error messages
  if (isset($_GET['success'])) {
    echo '<div style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px;">';
    echo '✓ ' . htmlspecialchars($_GET['message'] ?? 'Assignment submitted successfully!');
    echo '</div>';
  }
  
  if (isset($_GET['error'])) {
    $errorMessages = [
      'invalidfile' => 'Invalid file type! Only PDF and DWG files are allowed.',
      'filesize' => 'File size must be less than 20MB!',
      'upload' => 'Error uploading file. Please try again.',
      'nofile' => 'Please select a file to submit.'
    ];
    
    $errorMessage = $_GET['message'] ?? ($errorMessages[$_GET['error']] ?? 'An error occurred. Please try again.');
    
    echo '<div style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 20px;">';
    echo '✗ ' . htmlspecialchars($errorMessage);
    echo '</div>';
  }
  ?>
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
          $dueDate = $row['dueDate'];

          // Check if already submitted by this student
          $subRes = $conn->query("SELECT * FROM submissions WHERE assignmentId = $assignmentId AND studentId = $studentId");
          $submitted = ($subRes && $subRes->num_rows > 0);
          
          // Check if assignment is overdue
          $isOverdue = false;
          $overdueText = '';
          if ($dueDate) {
            $dueTimestamp = strtotime($dueDate);
            $currentTimestamp = time();
            if ($currentTimestamp > $dueTimestamp) {
              $isOverdue = true;
              $daysLate = floor(($currentTimestamp - $dueTimestamp) / (24 * 60 * 60));
              $overdueText = " (Overdue by $daysLate day" . ($daysLate > 1 ? 's' : '') . ")";
            }
          }

          echo "<div class='assignment'>";
          echo "<h4>$topic</h4>";
          echo "<p>$desc</p>";
          
          // Display due date and overdue status
          if ($dueDate) {
            $dueDateFormatted = date('M j, Y', strtotime($dueDate));
            $dueDateColor = $isOverdue ? '#ef4444' : '#6b7280';
            echo "<div style='margin-bottom: 12px; padding: 8px; background: " . ($isOverdue ? '#fef2f2' : '#f9fafb') . "; border: 1px solid " . ($isOverdue ? '#fecaca' : '#e5e7eb') . "; border-radius: 6px;'>";
            echo "<span style='font-weight: bold; color: $dueDateColor;'>📅 Due Date: $dueDateFormatted</span>";
            if ($isOverdue) {
              echo "<span style='color: #ef4444; font-weight: bold; margin-left: 8px;'>⚠️ OVERDUE$overdueText</span>";
            }
            echo "</div>";
          }
          if ($submitted) {
            $subRow = $subRes->fetch_assoc();
            // Only show the link to the student who submitted
              if ($subRow['studentId'] == $studentId) {
                // Determine file type and icon
                $fileExtension = strtolower(pathinfo($subRow['filePath'], PATHINFO_EXTENSION));
                $fileIcon = ($fileExtension === 'pdf') ? '📄' : '📐';
                $fileTypeText = ($fileExtension === 'pdf') ? 'PDF' : 'DWG';
                
                // Check if submission was late
                $submissionLate = false;
                $lateSubmissionText = '';
                if ($dueDate && $subRow['submittedAt']) {
                  $submissionTimestamp = strtotime($subRow['submittedAt']);
                  $dueTimestamp = strtotime($dueDate);
                  if ($submissionTimestamp > $dueTimestamp) {
                    $submissionLate = true;
                    $daysLate = floor(($submissionTimestamp - $dueTimestamp) / (24 * 60 * 60));
                    $lateSubmissionText = " (Late by $daysLate day" . ($daysLate > 1 ? 's' : '') . ")";
                  }
                }
                
                echo "<div style='margin-bottom:12px;'>";
                $submissionStatusColor = $submissionLate ? '#f59e42' : '#059669';
                $submissionStatusText = $submissionLate ? '✓ Submitted Late' : '✓ Already submitted';
                echo "<span style='color:$submissionStatusColor; font-weight:bold;'>$submissionStatusText$lateSubmissionText</span> ";
                echo "<a href='../uploads/{$subRow['filePath']}' target='_blank' style='color:#2563eb; text-decoration:none; margin-left:8px;'>";
                echo $fileIcon . " View " . $fileTypeText . "</a>";
                echo "</div>";
              
              // Show grade and comment if available
              if ($subRow['grade'] !== null) {
                echo "<div style='background:#f0f9ff; border:1px solid #0ea5e9; border-radius:6px; padding:12px; margin-top:8px;'>";
                echo "<div style='display:flex; align-items:center; gap:12px; margin-bottom:8px;'>";
                echo "<span style='font-weight:bold; color:#0c4a6e;'>Grade: </span>";
                echo "<span style='font-size:18px; font-weight:bold; color:#059669;'>{$subRow['grade']}</span>";
                if ($subRow['gradedAt']) {
                  echo "<span style='color:#6b7280; font-size:12px;'>Graded on: " . date('M j, Y', strtotime($subRow['gradedAt'])) . "</span>";
                }
                echo "</div>";
                
                if ($subRow['comment'] && trim($subRow['comment']) !== '') {
                  echo "<div style='margin-top:8px;'>";
                  echo "<span style='font-weight:bold; color:#0c4a6e;'>Instructor Comment:</span>";
                  echo "<div style='background:white; border:1px solid #e5e7eb; border-radius:4px; padding:8px; margin-top:4px; color:#374151;'>" . htmlspecialchars($subRow['comment']) . "</div>";
                  echo "</div>";
                }
                echo "</div>";
              } else {
                echo "<div style='color:#6b7280; font-size:14px; margin-top:8px;'>⏳ Waiting for instructor to grade this submission</div>";
              }
            }
          } else {
            echo "<form action='submit_assignment.php' method='post' enctype='multipart/form-data'>";
            echo "<input type='hidden' name='assignmentId' value='$assignmentId'>";
            echo "<div style='margin-bottom: 8px;'>";
            echo "<label style='display: block; font-weight: bold; margin-bottom: 4px;'>Select File (PDF or DWG):</label>";
            echo "<input type='file' name='submissionFile' accept='.pdf,.dwg' required style='width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;'>";
            echo "<div style='font-size: 12px; color: #6b7280; margin-top: 4px;'>Accepted formats: PDF, DWG (Max size: 20MB)</div>";
            echo "</div>";
            echo "<button type='submit' class='submission-btn'>Submit Assignment</button>";
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
