<?php
session_start();
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="instructor.dashbord.css">
<style>
.main-content {
    margin-left: 250px !important;
    margin-right: 50px !important;
}
</style>

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
            echo '<h3 style="color:#2563eb; margin-bottom:16px;">Batches</h3>';
            echo '<div style="display:flex; gap:8px; margin-bottom:16px;">';
            echo '<button onclick="expandAllBatches()" style="background:#f8fafc; border:1px solid #e2e8f0; color:#475569; padding:8px 12px; border-radius:4px; cursor:pointer; font-size:14px;">▷ Expand all</button>';
            echo '</div>';
            
            while ($row = $batchRes->fetch_assoc()) {
                $batch = htmlspecialchars($row['batch']);
                echo '<div class="batch-panel" style="margin-bottom:8px;">';
                echo '<button class="batch-toggle" onclick="toggleBatch(this, \'' . $batch . '\')" style="width:100%; background:#3b82f6; color:white; border:none; padding:12px 16px; text-align:left; cursor:pointer; border-radius:4px; display:flex; align-items:center; gap:8px;">';
                echo '<span class="batch-arrow" style="transition:transform 0.2s;">▷</span>';
                echo '<span>Batch ' . $batch . '</span>';
                echo '</button>';
                echo '<div class="batch-content" id="batch-' . $batch . '" style="display:none; background:#f8fafc; border:1px solid #e2e8f0; border-top:none; border-radius:0 0 4px 4px; padding:16px;">';
                echo '<div id="semester-panel-' . $batch . '"></div>';
                echo '<div id="topic-panel-' . $batch . '"></div>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Grading Modal -->
<div id="gradingModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:24px; border-radius:8px; width:500px; max-width:90%;">
        <h3 style="margin:0 0 16px 0; color:#1f2937;">Grade Submission</h3>
        <form id="gradingForm" method="post" action="save_grade.php">
            <input type="hidden" id="submissionId" name="submissionId">
            <div style="margin-bottom:16px;">
                <label for="studentUid" style="display:block; margin-bottom:4px; font-weight:500;">Student:</label>
                <input type="text" id="studentUid" readonly style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; background:#f9fafb;">
            </div>
            <div style="margin-bottom:16px;">
                <label for="grade" style="display:block; margin-bottom:4px; font-weight:500;">Grade:</label>
                <select id="grade" name="grade" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">Select Grade</option>
                    <option value="A+">A+ (Excellent)</option>
                    <option value="A">A (Very Good)</option>
                    <option value="A-">A- (Good)</option>
                    <option value="B+">B+ (Above Average)</option>
                    <option value="B">B (Average)</option>
                    <option value="B-">B- (Below Average)</option>
                    <option value="C+">C+ (Satisfactory)</option>
                    <option value="C">C (Pass)</option>
                    <option value="C-">C- (Barely Pass)</option>
                    <option value="D">D (Poor)</option>
                    <option value="E">E (Fail)</option>
                </select>
            </div>
            <div style="margin-bottom:20px;">
                <label for="comment" style="display:block; margin-bottom:4px; font-weight:500;">Comment:</label>
                <textarea id="comment" name="comment" rows="4" style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:4px; resize:vertical;"></textarea>
            </div>
            <div style="display:flex; gap:12px; justify-content:flex-end;">
                <button type="button" onclick="closeGradingModal()" style="padding:8px 16px; border:1px solid #d1d5db; background:white; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:8px 16px; background:#2563eb; color:white; border:none; border-radius:4px; cursor:pointer;">Save Grade</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleBatch(button, batch) {
    var content = document.getElementById('batch-' + batch);
    var arrow = button.querySelector('.batch-arrow');
    
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        arrow.style.transform = 'rotate(90deg)';
        // Load semesters for this batch
        showSemester(batch);
    } else {
        content.style.display = 'none';
        arrow.style.transform = 'rotate(0deg)';
    }
}

function expandAllBatches() {
    var batchPanels = document.querySelectorAll('.batch-panel');
    batchPanels.forEach(function(panel) {
        var content = panel.querySelector('.batch-content');
        var arrow = panel.querySelector('.batch-arrow');
        content.style.display = 'block';
        arrow.style.transform = 'rotate(90deg)';
    });
}

function showSemester(batch) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'instructor.submissions.ajax.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('semester-panel-' + batch).innerHTML = xhr.responseText;
            document.getElementById('topic-panel-' + batch).innerHTML = '';
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
            document.getElementById('topic-panel-' + batch).innerHTML = xhr.responseText;
        }
    };
    xhr.send('batch=' + encodeURIComponent(batch) + '&semester=' + encodeURIComponent(semester));
}

function openGradingModal(submissionId, studentUid, currentGrade, currentComment) {
    document.getElementById('submissionId').value = submissionId;
    document.getElementById('studentUid').value = studentUid || '';
    document.getElementById('grade').value = (currentGrade && currentGrade !== 'null') ? currentGrade : '';
    document.getElementById('comment').value = currentComment || '';
    document.getElementById('gradingModal').style.display = 'block';
}

function closeGradingModal() {
    document.getElementById('gradingModal').style.display = 'none';
    document.getElementById('gradingForm').reset();
}

// Close modal when clicking outside
document.getElementById('gradingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeGradingModal();
    }
});
</script>
