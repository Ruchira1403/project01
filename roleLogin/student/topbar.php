<?php
include '../../includes/dbh.inc.php';
?>

<link rel="stylesheet" href="topbar.css">
<div class="topbar">
    <div class="topbar-left">
        <span class="page-title"></span>
        <span class="page-desc"></span>
    </div>
    <div class="topbar-right">
        <span class="notification-bell">
                <span class="bell-icon">&#128276;</span>
                <span class="notification-count">
                        <?php
                        // Show unread notification count using notification_views table
                        $unreadCount = 0;
                        $userId = $_SESSION['userid'] ?? 0;
                        if ($userId) {
                            $sql = "SELECT COUNT(*) AS unread FROM notifications n WHERE FIND_IN_SET('students', n.audienceRole) AND sendDate <= NOW() AND NOT EXISTS (SELECT 1 FROM notification_views v WHERE v.notificationId = n.notificationId AND v.userId = ? )";
                            if ($stmt = $conn->prepare($sql)) {
                                $stmt->bind_param("i", $userId);
                                $stmt->execute();
                                $stmt->bind_result($unreadCount);
                                $stmt->fetch();
                                $stmt->close();
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
                     <div class="role">
                        <?php echo isset($_SESSION['userrole']) ? htmlspecialchars($_SESSION['userrole']) : 'Unknown'; ?>
                    </div>
                </div>
        </span>
    </div>
</div>
