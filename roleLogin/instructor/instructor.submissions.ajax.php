<?php
session_start();
include_once '../../includes/dbh.inc.php';

$instructorId = $_SESSION['userid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch']) && !isset($_POST['semester'])) {
    $batch = $conn->real_escape_string($_POST['batch']);
    $semRes = $conn->query("SELECT DISTINCT semester FROM assignments WHERE instructorId = $instructorId AND batch = '$batch' ORDER BY semester ASC");
    if ($semRes && $semRes->num_rows > 0) {
        echo '<div style="margin-bottom:18px;"><h3 style="color:#2563eb;">Semesters</h3>';
        while ($row = $semRes->fetch_assoc()) {
            $semester = (int)$row['semester'];
            echo "<button class='semester-btn' onclick=\"showTopics('$batch', $semester)\">Semester $semester</button> ";
        }
        echo '</div>';
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch']) && isset($_POST['semester'])) {
    $batch = $conn->real_escape_string($_POST['batch']);
    $semester = (int)$_POST['semester'];
    $assignmentsRes = $conn->query("SELECT * FROM assignments WHERE instructorId = $instructorId AND batch = '$batch' AND semester = $semester ORDER BY createdAt DESC");
    if ($assignmentsRes && $assignmentsRes->num_rows > 0) {
        echo '<div style="margin-bottom:18px;"><h3 style="color:#2563eb;">Topics</h3>';
        while ($assignment = $assignmentsRes->fetch_assoc()) {
            echo '<div class="assignment-block">';
            echo '<h4>' . htmlspecialchars($assignment['topic']) . '</h4>';
            echo '<p>' . htmlspecialchars($assignment['description']) . '</p>';
            $assignmentId = $assignment['assignmentId'];
            $subsRes = $conn->query("SELECT s.*, u.usersUid FROM submissions s JOIN users u ON s.studentId = u.usersId WHERE s.assignmentId = $assignmentId");
            if ($subsRes && $subsRes->num_rows > 0) {
                echo '<table style="width:100%; margin-bottom:18px; border-collapse:collapse;">';
                echo '<tr style="background:#f1f5f9;"><th style="padding:8px; text-align:left;">Student UID</th><th style="padding:8px; text-align:left;">PDF</th><th style="padding:8px; text-align:left;">Submitted At</th></tr>';
                while ($sub = $subsRes->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td style="padding:8px;">' . htmlspecialchars($sub['usersUid']) . '</td>';
                    echo '<td style="padding:8px;"><a href="../../uploads/' . htmlspecialchars($sub['filePath']) . '" target="_blank">View PDF</a></td>';
                    echo '<td style="padding:8px;">' . htmlspecialchars($sub['submittedAt']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div style="color:#dc2626; margin-bottom:12px;">No submissions yet.</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<div style="color:#dc2626;">No topics found for this semester.</div>';
    }
    exit;
}
?>
