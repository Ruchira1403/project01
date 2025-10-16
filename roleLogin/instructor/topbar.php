<?php
include '../../includes/dbh.inc.php';

// Get instructor information
$instructorId = $_SESSION['userid'] ?? 0;
$instructorName = $_SESSION['useruid'] ?? 'Instructor';
$instructorRole = $_SESSION['userrole'] ?? 'Instructor';

// Get instructor statistics
$stats = [];

// Total students
$totalStudentsQuery = "SELECT COUNT(*) as count FROM users WHERE usersRole = 'student'";
$totalStudentsResult = $conn->query($totalStudentsQuery);
$stats['totalStudents'] = $totalStudentsResult ? $totalStudentsResult->fetch_assoc()['count'] : 0;

// Pending submissions to review
$pendingSubmissionsQuery = "SELECT COUNT(*) as count FROM submissions WHERE gradedBy IS NULL";
$pendingSubmissionsResult = $conn->query($pendingSubmissionsQuery);
$stats['pendingSubmissions'] = $pendingSubmissionsResult ? $pendingSubmissionsResult->fetch_assoc()['count'] : 0;

// Unread notifications for instructors
$unreadNotificationsQuery = "SELECT COUNT(*) as count FROM notifications n 
                            WHERE FIND_IN_SET('instructor', n.audienceRole) 
                            AND n.sendDate <= NOW() 
                            AND NOT EXISTS (
                                SELECT 1 FROM notification_views v 
                                WHERE v.notificationId = n.notificationId 
                                AND v.userId = ?
                            )";
$stmt = $conn->prepare($unreadNotificationsQuery);
if ($stmt) {
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['unreadNotifications'] = $result ? $result->fetch_assoc()['count'] : 0;
    $stmt->close();
} else {
    // Fallback: if notification_views table doesn't exist, show all notifications
    $fallbackQuery = "SELECT COUNT(*) as count FROM notifications WHERE FIND_IN_SET('instructor', audienceRole) AND sendDate <= NOW()";
    $fallbackResult = $conn->query($fallbackQuery);
    $stats['unreadNotifications'] = $fallbackResult ? $fallbackResult->fetch_assoc()['count'] : 0;
}

// Get instructor initials
$initials = '';
if (isset($_SESSION['useruid'])) {
    $name = $_SESSION['useruid'];
    $parts = explode(' ', $name);
    foreach ($parts as $part) {
        if (strlen($part) > 0) {
            $initials .= strtoupper($part[0]);
        }
    }
} else {
    $initials = 'IN';
}
?>

<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="sidebar.css">

<div class="topbar" style="margin-top: 30px;">
    <div class="topbar-left" style="display: flex; align-items: center; flex-direction: row;">
        <img class="topbar-logo" src="../../images/2.jpg" alt="GeoSurvey Logo" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 6px; display: inline-block; vertical-align: middle;">
        <span class="page-title" style="font-size: 1.4em; font-weight: bold; color: #2c3e50; display: inline; white-space: nowrap;">GeoSurvey Portal</span>
        <span class="page-desc" style="font-size: 0.9em; color: #7f8c8d; margin-left: 8px; display: inline; white-space: nowrap;">- Instructor</span>
    </div>
    <div class="topbar-right">
        <span class="notification-bell">
            <span class="bell-icon">&#128276;</span>
            <span class="notification-count"><?php echo $stats['unreadNotifications']; ?></span>
        </span>
        <span class="user-info">
            <span class="user-avatar"><?php echo $initials; ?></span>
            <div class="info">
                <div class="name" style="font-weight:bold; font-size:1.15em;"><?php echo htmlspecialchars($instructorName); ?></div>
            </div>
        </span>
    </div>
</div>
