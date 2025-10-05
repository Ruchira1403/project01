<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include '../../includes/dbh.inc.php';
?>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<link rel="stylesheet" href="notifications.css">
<div class="main-content">
  <div class="notifications-header">
    <h1>Notifications</h1>
    <p>Stay updated with important announcements and reminders.</p>
    <?php
    // Total notifications for instructors
  $totalResult = $conn->query("SELECT COUNT(*) as total FROM notifications WHERE FIND_IN_SET('instructor', audienceRole) AND sendDate <= NOW()");
  $totalCount = ($totalResult && $totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['total'] : 0;
  // Unread: not viewed by this user
  $userId = $_SESSION['userid'] ?? 0;
  $unreadResult = $conn->query("SELECT COUNT(*) as unread FROM notifications n WHERE FIND_IN_SET('instructor', n.audienceRole) AND sendDate <= NOW() AND NOT EXISTS (SELECT 1 FROM notification_views v WHERE v.notificationId = n.notificationId AND v.userId = $userId)");
  $unreadCount = ($unreadResult && $unreadResult->num_rows > 0) ? $unreadResult->fetch_assoc()['unread'] : 0;
  // High Priority
  $highPriorityResult = $conn->query("SELECT COUNT(*) as highpriority FROM notifications WHERE FIND_IN_SET('instructor', audienceRole) AND priority = 'urgent' AND sendDate <= NOW()");
  $highPriorityCount = ($highPriorityResult && $highPriorityResult->num_rows > 0) ? $highPriorityResult->fetch_assoc()['highpriority'] : 0;
  // Today
  $today = date('Y-m-d');
  $todayResult = $conn->query("SELECT COUNT(*) as today FROM notifications WHERE FIND_IN_SET('instructor', audienceRole) AND DATE(sendDate) = '$today' AND sendDate <= NOW()");
  $todayCount = ($todayResult && $todayResult->num_rows > 0) ? $todayResult->fetch_assoc()['today'] : 0;
    
    ?>
    <div class="notifications-stats">
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-number"><?php echo $totalCount; ?></div>
          <div class="stat-label">Total</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🔔</div>
        <div class="stat-content">
          <div class="stat-number"><?php echo $unreadCount; ?></div>
          <div class="stat-label">Unread</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⚠️</div>
        <div class="stat-content">
          <div class="stat-number"><?php echo $highPriorityCount; ?></div>
          <div class="stat-label">Urgent</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
          <div class="stat-number"><?php echo $todayCount; ?></div>
          <div class="stat-label">Today</div>
        </div>
      </div>
    </div>
    
    <form class="notifications-filters" method="get">
      <div class="filter-group">
        <label for="filter">Filter:</label>
        <select name="filter" id="filter">
          <option value="all" <?php echo ($_GET['filter'] ?? '') === 'all' ? 'selected' : ''; ?>>All</option>
          <option value="unread" <?php echo ($_GET['filter'] ?? '') === 'unread' ? 'selected' : ''; ?>>Unread</option>
          <option value="high" <?php echo ($_GET['filter'] ?? '') === 'high' ? 'selected' : ''; ?>>High Priority</option>
        </select>
      </div>
      <div class="filter-group">
        <label for="search">Search:</label>
        <input type="text" name="search" id="search" placeholder="Search notifications..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
      </div>
      <button type="submit">Filter</button>
    </form>
  </div>
  <div class="notifications-feed">
    <h2 class="feed-title">&#128276; Notification Feed</h2>
    <?php
  // Filtering logic
  $filter = $_GET['filter'] ?? 'all';
  $search = trim($_GET['search'] ?? '');
  $where = "FIND_IN_SET('instructor', audienceRole) AND sendDate <= NOW()";
  if ($filter === 'unread' && $userId) {
    $where .= " AND NOT EXISTS (SELECT 1 FROM notification_views v WHERE v.notificationId = notifications.notificationId AND v.userId = $userId)";
  } elseif ($filter === 'high') {
    $where .= " AND priority = 'urgent'";
  }
  if ($search !== '') {
    $searchEsc = $conn->real_escape_string($search);
    $where .= " AND (title LIKE '%$searchEsc%' OR massege LIKE '%$searchEsc%')";
  }
  $result = $conn->query("SELECT * FROM notifications WHERE $where ORDER BY sendDate DESC");
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $priorityClass = strtolower($row['priority']);
        if ($priorityClass === 'infomation') $priorityClass = 'info';
        $title = htmlspecialchars($row['title']);
        $message = htmlspecialchars($row['massege']);
        $sendDate = date('Y-m-d H:i', strtotime($row['sendDate']));
        $notificationId = isset($row['notificationId']) ? (int)$row['notificationId'] : 0;
        // Check if read
        $isRead = false;
        if ($notificationId && $userId) {
          $readQuery = $conn->query("SELECT 1 FROM notification_views WHERE notificationId = $notificationId AND userId = $userId LIMIT 1");
          $isRead = ($readQuery && $readQuery->num_rows > 0);
        }
        $jsTitle = htmlspecialchars(json_encode($title), ENT_QUOTES, 'UTF-8');
        $jsMessage = htmlspecialchars(json_encode($message), ENT_QUOTES, 'UTF-8');
        $jsPriority = htmlspecialchars(json_encode(ucfirst($priorityClass)), ENT_QUOTES, 'UTF-8');
        $jsDate = htmlspecialchars(json_encode($sendDate), ENT_QUOTES, 'UTF-8');
        $readIcon = $isRead ? '<span class="notification-read-icon" style="float:right; color:#059669; font-size:1.5em; margin-left:12px;">&#10003;</span>' : '<span class="notification-unread-icon" style="float:right; color:#2563eb; font-size:1.5em; margin-left:12px;">&#9679;</span>';
        echo '<div class="notification-card ' . $priorityClass . '" onclick="showMessageModal(' . $jsTitle . ', ' . $jsMessage . ', ' . $jsPriority . ', ' . $jsDate . ', ' . $notificationId . ')">';
        echo '<div class="notification-title">' . $title . $readIcon . '</div>';
        echo '<div class="notification-meta">' . ucfirst($priorityClass) . ' &nbsp;|&nbsp; ' . $sendDate . '</div>';
        echo '</div>';
      }
    } else {
      echo '<div>No notifications found.</div>';
    }
    ?>
    <!-- Modal for message -->
    <div id="message-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); justify-content:center; align-items:center; z-index:9999;">
      <div style="background:white; padding:24px; border-radius:8px; max-width:500px; width:90%; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        <h3 id="modal-title" style="margin-bottom:12px; font-size:18px; color:#2563eb;"></h3>
        <div id="modal-priority" style="margin-bottom:8px; font-size:14px; color:#374151;"></div>
        <div id="modal-date" style="margin-bottom:12px; font-size:13px; color:#6b7280;"></div>
        <div id="modal-message" style="margin-bottom:18px; font-size:15px; color:#222;"></div>
        <button onclick="closeMessageModal()" style="padding:6px 18px; background:#2563eb; color:#fff; border:none; border-radius:4px; font-size:14px;">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
function showMessageModal(title, message, priority, date, notificationId) {
  document.getElementById('modal-title').textContent = title;
  document.getElementById('modal-message').textContent = message;
  document.getElementById('modal-priority').textContent = 'Priority: ' + priority;
  document.getElementById('modal-date').textContent = 'Sent: ' + date;
  document.getElementById('message-modal').style.display = 'flex';
  
  // AJAX to increment view count and update visual indicator
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'view_notification.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      // Update the visual indicator immediately
      updateNotificationReadStatus(notificationId);
    }
  };
  xhr.send('notification_id=' + encodeURIComponent(notificationId));
}

function updateNotificationReadStatus(notificationId) {
  // Find the specific notification card by its onclick attribute
  var notificationCards = document.querySelectorAll('.notification-card');
  notificationCards.forEach(function(card) {
    // Check if this card's onclick contains the specific notificationId
    var onclickAttr = card.getAttribute('onclick');
    if (onclickAttr && onclickAttr.includes(notificationId)) {
      var titleElement = card.querySelector('.notification-title');
      if (titleElement) {
        // Remove existing read/unread icons
        var existingIcons = titleElement.querySelectorAll('.notification-read-icon, .notification-unread-icon');
        existingIcons.forEach(function(icon) {
          icon.remove();
        });
        
        // Add green tick (read icon)
        var readIcon = document.createElement('span');
        readIcon.className = 'notification-read-icon';
        readIcon.style.cssText = 'float:right; color:#059669; font-size:1.5em; margin-left:12px;';
        readIcon.innerHTML = '&#10003;';
        titleElement.appendChild(readIcon);
      }
    }
  });
  
  // Update the unread count in summary
  updateUnreadCount();
}

function updateUnreadCount() {
  // Count unread notifications (blue dots)
  var unreadCount = document.querySelectorAll('.notification-unread-icon').length;
  
  // Update the unread count in the summary
  var unreadElement = document.querySelector('.stat-card:nth-child(2) .stat-number');
  if (unreadElement) {
    unreadElement.textContent = unreadCount;
  }
}

function closeMessageModal() {
  document.getElementById('message-modal').style.display = 'none';
}
</script>
</div>
