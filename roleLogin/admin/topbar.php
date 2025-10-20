<?php
include '../../includes/dbh.inc.php';
?>

<link rel="stylesheet" href="topbark.css">
<div class="topbar">
    <div class="topbar-left" style="margin-left: 260px;">
        <img class="topbar-logo" src="../../images/2.jpg" alt="GeoSurvey Logo">
        <span class="page-title">GeoSurvey Portal</span>
        <span class="page-desc">- Admin</span>
    </div>
    <div class="topbar-right">
        <span class="notification-bell">
            <span class="bell-icon">&#128276;</span>
            <span class="notification-count">
                <?php
                // Show unread notification count for admin
                $unreadCount = 0;
                $userId = $_SESSION['userid'] ?? 0;
                if ($userId) {
                    // First try with notification_views table
                    $sql = "SELECT COUNT(*) AS unread FROM notifications n 
                            WHERE FIND_IN_SET('admin', n.audienceRole) 
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
                        $fallbackSql = "SELECT COUNT(*) AS unread FROM notifications WHERE FIND_IN_SET('admin', audienceRole) AND sendDate <= NOW()";
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
                <div class="name"><?php echo htmlspecialchars($_SESSION['useruid']); ?></div>
            </div>
        </span>
    </div>
</div>