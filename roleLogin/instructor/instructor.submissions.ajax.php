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
                echo '<table style="width:100%; margin-bottom:18px; border-collapse:collapse; border:1px solid #e5e7eb;">';
                echo '<tr style="background:#f1f5f9;"><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">Student UID</th><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">File</th><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">Submitted At</th><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">Grade</th><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">Comment</th><th style="padding:12px; text-align:left; border:1px solid #e5e7eb;">Actions</th></tr>';
                while ($sub = $subsRes->fetch_assoc()) {
                    // Determine file type and icon
                    $fileExtension = strtolower(pathinfo($sub['filePath'], PATHINFO_EXTENSION));
                    $fileIcon = ($fileExtension === 'pdf') ? '📄' : '📐';
                    $fileTypeText = ($fileExtension === 'pdf') ? 'PDF' : 'DWG';
                    
                    echo '<tr>';
                    echo '<td style="padding:12px; border:1px solid #e5e7eb;">' . htmlspecialchars($sub['usersUid']) . '</td>';
                    echo '<td style="padding:12px; border:1px solid #e5e7eb;">';
                    echo '<a href="../uploads/' . htmlspecialchars($sub['filePath']) . '" target="_blank" style="color:#2563eb; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">';
                    echo $fileIcon . ' View ' . $fileTypeText;
                    echo '</a>';
                    echo '</td>';
                    echo '<td style="padding:12px; border:1px solid #e5e7eb;">' . htmlspecialchars($sub['submittedAt']) . '</td>';
                    
                    // Grade column
                    if ($sub['grade'] !== null) {
                        echo '<td style="padding:12px; border:1px solid #e5e7eb; color:#059669; font-weight:bold;">' . htmlspecialchars($sub['grade']) . '</td>';
                    } else {
                        echo '<td style="padding:12px; border:1px solid #e5e7eb; color:#dc2626;">Not Graded</td>';
                    }
                    
                    // Comment column
                    if ($sub['comment'] !== null) {
                        $comment = htmlspecialchars($sub['comment']);
                        if (strlen($comment) > 50) {
                            $comment = substr($comment, 0, 50) . '...';
                        }
                        echo '<td style="padding:12px; border:1px solid #e5e7eb;" title="' . htmlspecialchars($sub['comment']) . '">' . $comment . '</td>';
                    } else {
                        echo '<td style="padding:12px; border:1px solid #e5e7eb; color:#6b7280;">No Comment</td>';
                    }
                    
                    // Actions column
                    echo '<td style="padding:12px; border:1px solid #e5e7eb;">';
                    echo '<button onclick="openGradingModal(' . $sub['submissionId'] . ', \'' . htmlspecialchars($sub['usersUid']) . '\', ' . ($sub['grade'] !== null ? $sub['grade'] : 'null') . ', \'' . htmlspecialchars($sub['comment'] ?? '') . '\')" style="background:#2563eb; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:12px;">Grade</button>';
                    echo '</td>';
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
