<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include '../../includes/dbh.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GeoSurvey Topbar</title>

<style>
/* Reset default margins/padding */
* {
  margin:0;
  padding: 0;
  box-sizing: border-box;
  
}

/* --- TOPBAR MAIN CONTAINER --- */
.topbar {
    max-width: 1560px;
    margin-left: 300px;
    margin-top: 30px;
    margin-right: 0;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 65px;
  background-color: #f4f5f7;
  border-bottom: 1px solid #ddd;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 100;
}

/* --- LEFT SECTION (Logo + Title) --- */
.topbar-left {
  display: flex;
  align-items: center;
  gap: 12px;
  
}

.topbar-logo {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.page-title {
  font-size: 20px;
  font-weight: 600;
  color: #2c2c54;
}

.page-desc {
  font-size: 14px;
  color: #555;
  margin-left: 6px;
}

/* --- RIGHT SECTION (Notifications + User Info) --- */
.topbar-right {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-right: 30px;
}

/* --- Notification Bell --- */
.notification-bell {
  position: relative;
  cursor: pointer;
  font-size: 22px;
  color: #333;
}

.bell-icon {
  font-size: 22px;
}

.notification-count {
  position: absolute;
  top: -8px;
  right: -10px;
  background-color: #e74c3c;
  color: #fff;
  font-size: 12px;
  font-weight: 600;
  border-radius: 50%;
  padding: 2px 6px;
}

/* --- USER INFO --- */
.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  background-color: #2e86de;
  color: white;
  width: 34px;
  height: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
  font-size: 16px;
}

.info .name {
  color: #333;
  font-size: 15px;
  font-weight: 500;
}

/* --- Hover Effects --- */
.notification-bell:hover,
.user-info:hover {
  opacity: 0.8;
  transition: 0.2s ease;
}
</style>
</head>

<body>

<div class="topbar">
  <div class="topbar-left">
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
          // Check unread notifications
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
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result) {
              $unreadCount = $result->fetch_assoc()['unread'];
            }
            $stmt->close();
          } else {
            // Fallback: if notification_views table doesn't exist
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
        <div class="name"><?php echo htmlspecialchars($_SESSION['useruid'] ?? 'User'); ?></div>
      </div>
    </span>
  </div>
</div>

</body>
</html>