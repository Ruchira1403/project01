<?php
include '../../includes/dbh.inc.php';
?>

<link rel="stylesheet" href="topbar.css">
<div class="topbar">
    <div class="topbar-left" style="display: flex; align-items: center; flex-direction: row;">
        <img class="topbar-logo" src="../../images/2.jpg" alt="GeoSurvey Logo" style="width: 40px; height: 40px; margin-right: 12px; border-radius: 6px; display: inline-block; vertical-align: middle;">
        <span class="page-title" style="font-size: 1.4em; font-weight: bold; color: #2c3e50; display: inline; white-space: nowrap;">GeoSurvey Portal</span>
        <span class="page-desc" style="font-size: 0.9em; color: #7f8c8d; margin-left: 8px; display: inline; white-space: nowrap;">- Student</span>
    </div>
    <div class="topbar-right">
        <span class="notification-bell">
                <span class="bell-icon">&#128276;</span>
                <span class="notification-count">
                        <?php
                        // Show unread notification count for students
                        $unreadCount = 0;
                        $userId = $_SESSION['userid'] ?? 0;
                        if ($userId) {
                            // First try with notification_views table
                            $sql = "SELECT COUNT(*) AS unread FROM notifications n 
                                    WHERE FIND_IN_SET('student', n.audienceRole) 
                                    AND n.sendDate <= NOW() 
                                    AND NOT EXISTS (
                                        SELECT 1 FROM notification_views v 
                                        WHERE v.notificationId = n.notificationId 
                                        AND v.userId = ?
                                    )";
                            $stmt = $conn->prepare($sql);
                            if ($stmt) {
                                $stmt->bind_param("i", $userId);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                if ($result) {
                                    $unreadCount = $result->fetch_assoc()['unread'];
                                }
                                $stmt->close();
                            } else {
                                // Fallback: if notification_views table doesn't exist, show all notifications
                                $fallbackSql = "SELECT COUNT(*) AS unread FROM notifications WHERE FIND_IN_SET('student', audienceRole) AND sendDate <= NOW()";
                                $fallbackResult = $conn->query($fallbackSql);
                                if ($fallbackResult) {
                                    $unreadCount = $fallbackResult->fetch_assoc()['unread'];
                                }
                            }
                        }
                        echo $unreadCount;
                        ?>
                </span>
        </span>
        <span class="user-info">
                <span class="user-avatar">
                    <?php 
                    // Display initials from usersName
                    if (isset($_SESSION['useruid'])) {
                        $name = $_SESSION['useruid'];
                        $initials = '';
                        $parts = explode(' ', $name);
                        foreach ($parts as $part) {
                            if (strlen($part) > 0) {
                                $initials .= strtoupper($part[0]);
                            }
                        }
                        echo $initials;
                    } else {
                        echo 'NA';
                    }
                    ?>
                </span>
                <div class="info">
                   
                    <div class="name" style="font-weight:bold; font-size:1.15em;"><?php echo htmlspecialchars($_SESSION['useruid']); ?></div>
                     <div class="role" style="color:rgb(91, 92, 157);">
                        <?php echo isset($_SESSION['userrole']) ? htmlspecialchars($_SESSION['userrole']) : 'Unknown'; ?>
                    </div>
                </div>
        </span>
    </div>
</div>
