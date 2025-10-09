<?php
// Get current page name for active highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="sidebar.css">
<div class="sidebar">
    <div class="logo">GeoSurvey</div>
    <div class="role">Student</div>
    <nav>
        <a href="student.home.php" class="<?php echo ($currentPage == 'student.home.php') ? 'active' : ''; ?>">&#127968; Dashboard</a>
        <a href="student.field_tasks.php" class="<?php echo ($currentPage == 'student.field_tasks.php') ? 'active' : ''; ?>">&#128221; Field Tasks</a>
        <a href="student.submissions.php" class="<?php echo ($currentPage == 'student.submissions.php') ? 'active' : ''; ?>">&#128196; Submissions</a>
        <a href="student.viva_sessions.php" class="<?php echo ($currentPage == 'student.viva_sessions.php') ? 'active' : ''; ?>">&#128203; Viva Sessions</a>
        <a href="feedback.php" class="<?php echo ($currentPage == 'feedback.php') ? 'active' : ''; ?>">&#128172; Feedback</a>
        <a href="attendance.php" class="<?php echo ($currentPage == 'attendance.php') ? 'active' : ''; ?>">&#128197; Attendance</a>
        <a href="student.notifications.php" class="<?php echo ($currentPage == 'student.notifications.php') ? 'active' : ''; ?>">&#128276; Notifications</a>
    </nav>
    <a href="../../login.php" class="logout">&#8592; Logout</a>
</div>
