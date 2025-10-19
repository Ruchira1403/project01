<?php
session_start();
require_once 'topbar.php';
require_once 'sidebar.php';
require_once '../../includes/dbh.inc.php';

// Get instructor ID from session
$instructorId = $_SESSION['userid'];

// Get dashboard statistics
$stats = [];

// Total Students
$totalStudentsQuery = "SELECT COUNT(*) as count FROM users WHERE usersRole = 'student'";
$totalStudentsResult = $conn->query($totalStudentsQuery);
$stats['totalStudents'] = $totalStudentsResult ? $totalStudentsResult->fetch_assoc()['count'] : 0;

// Submissions this week
$submissionsQuery = "SELECT COUNT(*) as count FROM submissions WHERE WEEK(submittedAt) = WEEK(CURDATE()) AND YEAR(submittedAt) = YEAR(CURDATE())";
$submissionsResult = $conn->query($submissionsQuery);
$stats['submissions'] = $submissionsResult ? $submissionsResult->fetch_assoc()['count'] : 0;

// Pending Reviews
$pendingQuery = "SELECT COUNT(*) as count FROM submissions WHERE gradedBy IS NULL";
$pendingResult = $conn->query($pendingQuery);
$stats['pendingReviews'] = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;

// Average Grade
$avgGradeQuery = "SELECT AVG(grade) as avg_grade FROM submissions WHERE grade IS NOT NULL";
$avgGradeResult = $conn->query($avgGradeQuery);
$stats['avgGrade'] = $avgGradeResult ? round($avgGradeResult->fetch_assoc()['avg_grade'], 1) : 0;

// Get recent submissions
$recentSubmissionsQuery = "SELECT s.*, u.firstName, u.lastName, a.title as assignment_title 
                          FROM submissions s 
                          JOIN users u ON s.studentId = u.usersId 
                          JOIN assignments a ON s.assignmentId = a.assignmentId 
                          ORDER BY s.submittedAt DESC 
                          LIMIT 3";
$recentSubmissionsResult = $conn->query($recentSubmissionsQuery);
$recentSubmissions = [];
if ($recentSubmissionsResult) {
    while ($row = $recentSubmissionsResult->fetch_assoc()) {
        $recentSubmissions[] = $row;
    }
}

// Get upcoming classes (if you have a classes/schedule table)
$upcomingClassesQuery = "SELECT * FROM classes WHERE class_date >= CURDATE() ORDER BY class_date ASC LIMIT 2";
$upcomingClassesResult = $conn->query($upcomingClassesQuery);
$upcomingClasses = [];
if ($upcomingClassesResult) {
    while ($row = $upcomingClassesResult->fetch_assoc()) {
        $upcomingClasses[] = $row;
    }
}
?>
<link rel="stylesheet" href="instructor.dashbord.css">
<style>
/* Gradient page header to match Resources */
.page-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    margin-bottom: 50px;
    border-radius: 12px; /* align with content margins */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-width: 1560px;
}
.page-header h1 { margin: 0; font-size: 2.5em; font-weight: 300; }
.page-header p { margin: 10px 0 0 0; opacity: 0.9; font-size: 1.1em; }
</style>
<div class="main-content">
    <div class="page-header">
        <h1>📊 Instructor Dashboard</h1>
        <p>Manage your classes and student progress</p>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-title">Total Students</div>
            <div class="card-value"><?php echo $stats['totalStudents']; ?></div>
            <div class="card-desc">Active students</div>
        </div>
        <div class="summary-card">
            <div class="card-title">Submissions</div>
            <div class="card-value"><?php echo $stats['submissions']; ?></div>
            <div class="card-desc">This week</div>
        </div>
        <div class="summary-card">
            <div class="card-title">Pending Reviews</div>
            <div class="card-value"><?php echo $stats['pendingReviews']; ?></div>
            <div class="card-desc">Need attention</div>
        </div>
        <div class="summary-card">
            <div class="card-title">Average Grade</div>
            <div class="card-value"><?php echo $stats['avgGrade']; ?>%</div>
            <div class="card-desc">Class performance</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
        <!-- Recent Submissions Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3>📅 Recent Submissions</h3>
            </div>
            <div class="submissions-list">
                <?php if (!empty($recentSubmissions)): ?>
                    <?php foreach ($recentSubmissions as $submission): ?>
                        <div class="submission-item">
                            <div class="submission-info">
                                <div class="student-name"><?php echo htmlspecialchars($submission['firstName'] . ' ' . $submission['lastName']); ?></div>
                                <div class="assignment-title"><?php echo htmlspecialchars($submission['assignment_title']); ?></div>
                                <div class="submission-date">Submitted: <?php echo date('Y-m-d H:i', strtotime($submission['submission_date'])); ?></div>
                            </div>
                            <div class="submission-status <?php echo $submission['status'] == 'pending' ? 'pending' : 'reviewed'; ?>">
                                <?php 
                                if ($submission['status'] == 'pending') {
                                    echo 'Pending Review';
                                } else {
                                    echo 'Reviewed';
                                    if ($submission['grade']) {
                                        echo ' • Grade: ' . $submission['grade'];
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="submission-item">
                        <div class="submission-info">
                            <div class="student-name">No recent submissions</div>
                            <div class="assignment-title">No assignments submitted yet</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="section-button" onclick="window.location.href='instructor.submissions.php'">Review All Submissions</button>
        </div>

        <!-- Upcoming Classes Section -->
        <div class="dashboard-section">
            <div class="section-header">
                <h3>📅 Upcoming Classes</h3>
            </div>
            <div class="classes-list">
                <?php if (!empty($upcomingClasses)): ?>
                    <?php foreach ($upcomingClasses as $class): ?>
                        <div class="class-item">
                            <div class="class-info">
                                <div class="class-title"><?php echo htmlspecialchars($class['title'] ?? $class['class_name'] ?? 'Class'); ?></div>
                                <div class="class-topic"><?php echo htmlspecialchars($class['description'] ?? $class['topic'] ?? 'Class Description'); ?></div>
                                <div class="class-time-location">
                                    <?php 
                                    $classDate = date('H:i A', strtotime($class['class_date'] ?? $class['start_time'] ?? '09:00:00'));
                                    $location = $class['location'] ?? 'Classroom';
                                    echo $classDate . ' • ' . $location;
                                    ?>
                                </div>
                            </div>
                            <div class="class-tag <?php 
                                $classDate = $class['class_date'] ?? $class['date'] ?? date('Y-m-d');
                                $today = date('Y-m-d');
                                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                
                                if ($classDate == $today) {
                                    echo 'today';
                                } elseif ($classDate == $tomorrow) {
                                    echo 'tomorrow';
                                } else {
                                    echo 'upcoming';
                                }
                            ?>">
                                <?php 
                                $classDate = $class['class_date'] ?? $class['date'] ?? date('Y-m-d');
                                $today = date('Y-m-d');
                                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                
                                if ($classDate == $today) {
                                    echo 'Today';
                                } elseif ($classDate == $tomorrow) {
                                    echo 'Tomorrow';
                                } else {
                                    echo date('M j', strtotime($classDate));
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="class-item">
                        <div class="class-info">
                            <div class="class-title">No upcoming classes</div>
                            <div class="class-topic">No classes scheduled</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="section-button" onclick="window.location.href='instructor.assignments.php'">View Full Schedule</button>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <button class="action-btn" onclick="window.location.href='instructor.submissions.php'">Grade Submissions</button>
        <button class="action-btn" onclick="window.location.href='instructor.attendance.php'">Take Attendance</button>
        <button class="action-btn" onclick="window.location.href='instructor.field_tasks.php'">Upload Resources</button>
    </div>
</div>


