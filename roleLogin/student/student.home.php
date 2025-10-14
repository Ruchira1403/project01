<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';

// Get student information
$studentId = $_SESSION['userid'];
$studentBatch = '';
$batchRes = $conn->query("SELECT batch FROM users WHERE usersId = $studentId LIMIT 1");
if ($batchRes && $batchRes->num_rows > 0) {
    $studentBatch = $batchRes->fetch_assoc()['batch'];
}

// Get statistics
// Total assignments for this student's batch
$totalAssignmentsRes = $conn->query("SELECT COUNT(*) as count FROM assignments WHERE batch = '" . $conn->real_escape_string($studentBatch) . "'");
$totalAssignments = $totalAssignmentsRes ? $totalAssignmentsRes->fetch_assoc()['count'] : 0;

// Completed assignments by this student
$completedRes = $conn->query("SELECT COUNT(*) as count FROM submissions s 
    JOIN assignments a ON s.assignmentId = a.assignmentId 
    WHERE s.studentId = $studentId AND a.batch = '" . $conn->real_escape_string($studentBatch) . "'");
$completedAssignments = $completedRes ? $completedRes->fetch_assoc()['count'] : 0;

// Pending assignments (not submitted by this student)
$pendingRes = $conn->query("SELECT COUNT(*) as count FROM assignments a 
    WHERE a.batch = '" . $conn->real_escape_string($studentBatch) . "' 
    AND a.assignmentId NOT IN (SELECT assignmentId FROM submissions WHERE studentId = $studentId)");
$pendingAssignments = $pendingRes ? $pendingRes->fetch_assoc()['count'] : 0;

// Calculate completion percentage
$completionPercentage = $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100) : 0;

// Get attendance data
$attendanceRes = $conn->query("SELECT COUNT(*) as total, 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present 
    FROM attendance WHERE userUid = '" . $conn->real_escape_string($_SESSION['useruid']) . "'");
$attendanceData = $attendanceRes ? $attendanceRes->fetch_assoc() : ['total' => 0, 'present' => 0];
$attendanceRate = $attendanceData['total'] > 0 ? round(($attendanceData['present'] / $attendanceData['total']) * 100) : 0;

// Get recent assignments
$recentAssignmentsRes = $conn->query("SELECT a.*, s.submissionId, s.grade, s.comment, s.submittedAt 
    FROM assignments a 
    LEFT JOIN submissions s ON a.assignmentId = s.assignmentId AND s.studentId = $studentId
    WHERE a.batch = '" . $conn->real_escape_string($studentBatch) . "' 
    ORDER BY a.createdAt DESC LIMIT 5");

// Get recent notifications
$notificationsRes = $conn->query("SELECT * FROM notifications 
    WHERE FIND_IN_SET('student', audienceRole) AND sendDate <= NOW() 
    ORDER BY sendDate DESC LIMIT 3");
?>
<link rel="stylesheet" href="sidebark.css">
<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="student_dashboard.css">
<div class="main-content">
   

<div class="dashboard-header">
        <h1 style="margin-bottom: 5px;">Student Dashboard</h1>
        <p style="margin-top: 0; margin-bottom: 24px;">Welcome back! Here's your academic progress.</p>
    </div>


    <div class="dashboard-stats-row">
        <div class="dashboard-stat">
            <div class="stat-title">Total Assignments</div>
            <div class="stat-value"><?php echo $totalAssignments; ?></div>
            <div class="stat-desc">For your batch</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Completed</div>
            <div class="stat-value" style="color:#059669;"><?php echo $completedAssignments; ?></div>
            <div class="stat-desc"><?php echo $completionPercentage; ?>% completion</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Pending</div>
            <div class="stat-value" style="color:#f59e42;"><?php echo $pendingAssignments; ?></div>
            <div class="stat-desc">Not submitted</div>
        </div>
        <div class="dashboard-stat">
            <div class="stat-title">Attendance</div>
            <div class="stat-value" style="color:#2563eb;"><?php echo $attendanceRate; ?>%</div>
            <div class="stat-desc"><?php echo $attendanceData['present']; ?>/<?php echo $attendanceData['total']; ?> sessions</div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-col dashboard-tasks">
            <h2>Recent Assignments</h2>
            <?php
            if ($recentAssignmentsRes && $recentAssignmentsRes->num_rows > 0) {
                while ($assignment = $recentAssignmentsRes->fetch_assoc()) {
                    $status = 'pending';
                    $statusText = 'Pending';
                    $statusColor = '#f59e42';
                    $gradeText = '';
                    
                    if ($assignment['submissionId']) {
                        // Check if submission was late
                        $submissionLate = false;
                        if ($assignment['dueDate'] && $assignment['submittedAt']) {
                            $submissionTimestamp = strtotime($assignment['submittedAt']);
                            $dueTimestamp = strtotime($assignment['dueDate']);
                            if ($submissionTimestamp > $dueTimestamp) {
                                $submissionLate = true;
                            }
                        }
                        
                        if ($submissionLate) {
                            $status = 'late';
                            $statusText = 'Submitted Late';
                            $statusColor = '#f59e42';
                        } else {
                            $status = 'completed';
                            $statusText = 'Completed';
                            $statusColor = '#059669';
                        }
                        
                        if ($assignment['grade']) {
                            $gradeText = '<span class="task-grade">Grade: ' . htmlspecialchars($assignment['grade']) . '</span>';
                        }
                    } else {
                        // Check if assignment is overdue
                        $dueDate = strtotime($assignment['dueDate'] ?? '');
                        if ($dueDate && $dueDate < time()) {
                            $status = 'overdue';
                            $statusText = 'Overdue';
                            $statusColor = '#ef4444';
                        }
                    }
                    
                    echo '<div class="task-card">';
                    echo '<div class="task-title">' . htmlspecialchars($assignment['topic']) . '</div>';
                    echo '<div class="task-meta">Semester ' . $assignment['semester'] . ' &nbsp;|&nbsp; Due: ' . date('Y-m-d', strtotime($assignment['dueDate'])) . '</div>';
                    echo '<span class="task-status" style="color:' . $statusColor . ';">' . $statusText . '</span> ' . $gradeText;
                    echo '</div>';
                }
            } else {
                echo '<div class="task-card">';
                echo '<div class="task-title">No assignments available</div>';
                echo '<div class="task-meta">Check back later for new assignments</div>';
                echo '</div>';
            }
            ?>
            <div class="view-all-tasks"><a href="student.submissions.php">View All Assignments</a></div>
        </div>
        <div class="dashboard-col dashboard-progress">
            <h2>Progress Overview</h2>
            <div class="progress-bar-label">Overall Completion</div>
            <div class="progress-bar"><div class="progress-bar-fill" style="width:<?php echo $completionPercentage; ?>%;"></div></div>
            <div class="progress-bar-label">Attendance Rate</div>
            <div class="progress-bar"><div class="progress-bar-fill" style="width:<?php echo $attendanceRate; ?>%; background:#2563eb;"></div></div>
            <div class="quick-actions">
                <div><span>&#128190;</span> <a href="student.submissions.php" style="text-decoration: none; color: inherit;">Upload Assignment</a></div>
                <div><span>&#128197;</span> <a href="attendance.php" style="text-decoration: none; color: inherit;">View Attendance</a></div>
            </div>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-col dashboard-notifications">
            <h2>Recent Notifications</h2>
            <?php
            if ($notificationsRes && $notificationsRes->num_rows > 0) {
                while ($notification = $notificationsRes->fetch_assoc()) {
                    $cardClass = 'assignment';
                    if (strpos(strtolower($notification['title']), 'feedback') !== false || strpos(strtolower($notification['title']), 'grade') !== false) {
                        $cardClass = 'feedback';
                    } elseif (strpos(strtolower($notification['title']), 'viva') !== false) {
                        $cardClass = 'viva';
                    }
                    
                    echo '<div class="notification-card ' . $cardClass . '" style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 16px; margin-bottom: 12px;">';
                    echo '<div class="notification-title" style="color: #0c4a6e; font-weight: bold; font-size: 1.1em; margin-bottom: 8px;">' . htmlspecialchars($notification['title']) . '</div>';
                    echo '<div class="notification-desc" style="color: #0369a1; margin-bottom: 8px;">' . htmlspecialchars($notification['massege']) . '</div>';
                    echo '<div class="notification-time" style="color: #0284c7; font-size: 0.9em;">' . date('M j, Y', strtotime($notification['sendDate'])) . '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="notification-card" style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 16px; margin-bottom: 12px;">';
                echo '<div class="notification-title" style="color: #0c4a6e; font-weight: bold; font-size: 1.1em; margin-bottom: 8px;">No notifications</div>';
                echo '<div class="notification-desc" style="color: #0369a1; margin-bottom: 8px;">You have no recent notifications</div>';
                echo '</div>';
            }
            ?>
            <div class="view-all-notifications"><a href="student.notifications.php">View All Notifications</a></div>
        </div>
    </div>
</div>


