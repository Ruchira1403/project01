<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="instructor.home.css">

<div class="main-content">
    <div class="dashboard-header">
        <h1>Assignment Submissions</h1>
        <p>View all student submissions for your assignments.</p>
    </div>

    <div class="submissions-list">
        <?php
        $instructorId = $_SESSION['userid'];
        $batchRes = $conn->query("SELECT DISTINCT batch FROM assignments WHERE instructorId = $instructorId ORDER BY batch ASC");
        if ($batchRes && $batchRes->num_rows > 0) {
            echo '<div style="margin-bottom:24px;">';
            echo '<h3 style="color:#2563eb;">Batches</h3>';
            while ($row = $batchRes->fetch_assoc()) {
                $batch = htmlspecialchars($row['batch']);
                echo "<button class='batch-btn' onclick=\"showSemester('$batch')\">$batch</button> ";
            }
            echo '</div>';
        }
        ?>
        <div id="semester-panel"></div>
        <div id="topic-panel"></div>
    </div>
</div>

<script>
function showSemester(batch) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'instructor.submissions.ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('semester-panel').innerHTML = xhr.responseText;
            document.getElementById('topic-panel').innerHTML = '';
        }
    };
    xhr.send('batch=' + encodeURIComponent(batch));
}

function showTopics(batch, semester) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'instructor.submissions.ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('topic-panel').innerHTML = xhr.responseText;
        }
    };
    xhr.send('batch=' + encodeURIComponent(batch) + '&semester=' + encodeURIComponent(semester));
}
</script>
