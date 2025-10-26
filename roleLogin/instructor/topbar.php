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

<div class="topbar" style="margin-top: 0px;">
    <div class="topbar-left" style="display: flex; align-items: center; flex-direction: row;">
        <img class="topbar-logo" src="../../images/2.jpg" alt="GeoSurvey Logo" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 6px; display: inline-block; vertical-align: middle;">
        <span class="page-title">GeoSurvey Portal</span>
        <span class="page-desc" style="margin-left: 8px;">- Instructor</span>
    </div>
    <div class="topbar-right">
        <span class="notification-bell">
            <span class="bell-icon">&#128276;</span>
            <span class="notification-count"><?php echo $stats['unreadNotifications']; ?></span>
        </span>
        <span class="user-info">
              <span class="user-avatar">
                <?php
                // Try to show instructor avatar from instructor_profiles
                $avatarShown = false;
                if (isset($_SESSION['userid'])) {
                    $uid = $_SESSION['userid'];
                    $avStmt = $conn->prepare('SELECT avatar FROM instructor_profiles WHERE userId = ? LIMIT 1');
                    if ($avStmt) {
                        $avStmt->bind_param('i', $uid);
                        $avStmt->execute();
                        $avRes = $avStmt->get_result();
                        if ($avRes && $avRes->num_rows > 0) {
                            $avRow = $avRes->fetch_assoc();
                            if (!empty($avRow['avatar']) && file_exists(__DIR__ . '/uploads/avatar/' . $avRow['avatar'])) {
                                echo '<img src="uploads/avatar/' . htmlspecialchars($avRow['avatar']) . '" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                                $avatarShown = true;
                            }
                        }
                        $avStmt->close();
                    }
                }
                if (!$avatarShown) {
                    echo htmlspecialchars($initials);
                }
                ?>
              </span>
                    <div class="info">
                        <a href="profile.php" style="text-decoration:none; color:inherit;">
                            <div class="name" style="font-weight:bold; font-size:1.15em; cursor:pointer;">
                                <?php echo htmlspecialchars($instructorName); ?>
                            </div>
                        </a>
                    </div>
        </span>
    </div>
</div>
