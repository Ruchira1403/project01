<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
  header("location: ../../login.php");
  exit();
}
include '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="fff.css">
<link rel="stylesheet" href="notifications-history.css">
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
<div class="main-content" style="margin-top: 100px; max-width: 1560px; margin-left: 300px; margin-right:50px">
  <div class="notifications-header">
  <div class="page-header1" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1560px;">
    <h1 style="margin:0; font-size:2.5em; font-weight:300;color: white;">&#128276; Notifications</h1>
    <p style="margin:10px 0 0 0; opacity:0.9; font-size:1.1em; color: white;">Send announcements and manage communications.</p>
  </div>
    <div class="notifications-tabs" style="margin-bottom:50px">
      <button onclick="window.location.href='notifications.php'">Compose</button>
      <button class="active">History</button>
      <button onclick="window.location.href='notifications-analytics.php'" >Analytics</button>
    </div>
    <?php
      // Get notification counts
      $totalResult = $conn->query("SELECT COUNT(*) as total FROM notifications");
      $sentResult = $conn->query("SELECT COUNT(*) as sent FROM notifications WHERE sendDate <= NOW()");
      $scheduledResult = $conn->query("SELECT COUNT(*) as scheduled FROM notifications WHERE sendDate > NOW()");
      $totalCount = ($totalResult && $totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['total'] : 0;
      $sentCount = ($sentResult && $sentResult->num_rows > 0) ? $sentResult->fetch_assoc()['sent'] : 0;
      $scheduledCount = ($scheduledResult && $scheduledResult->num_rows > 0) ? $scheduledResult->fetch_assoc()['scheduled'] : 0;
    ?>
    <div class="notifications-summary">
      <div class="summary-box"><span class="summary-count"><?php echo $totalCount; ?></span><span>Total</span></div>
      <div class="summary-box"><span class="summary-count"><?php echo $sentCount; ?></span><span>Sent</span></div>
      <div class="summary-box"><span class="summary-count"><?php echo $scheduledCount; ?></span><span>Scheduled</span></div>
    </div>
    <div class="notifications-history-container">
      <h2>Notification History</h2>
      <table class="notifications-history-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Audience</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Recipients</th>
            <th>Views</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = $conn->query("SELECT * FROM notifications ORDER BY sendDate DESC");
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              // Status logic
              $status = '';
              $statusIcon = '';
              if (strtotime($row['sendDate']) > time()) {
                $status = 'Scheduled';
                $statusIcon = '&#128337;';
              } else {
                $status = 'Sent';
                $statusIcon = '&#10003;';
              }
              // Priority tag class
              $priorityClass = strtolower($row['priority']);
              // Audience tag class
              $audienceClass = strtolower($row['audienceRole']);
        // Calculate recipients count
   $audienceRoles = array_map('trim', explode(',', strtolower($row['audienceRole'])));
$recipientsCount = 0;

if (in_array('all', $audienceRoles)) {
    // All users
    $sql = "SELECT COUNT(*) as cnt FROM users";
    $resultUsers = $conn->query($sql);
    if ($resultUsers && $resultUsers->num_rows > 0) {
        $recipientsCount = $resultUsers->fetch_assoc()['cnt'];
    }
} else {
    // Specific roles
    $placeholders = implode(',', array_fill(0, count($audienceRoles), '?'));
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE LOWER(usersRole) IN ($placeholders)");

    // bind params dynamically
    $types = str_repeat('s', count($audienceRoles));
    $stmt->bind_param($types, ...$audienceRoles);

    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $recipientsCount = $res->fetch_assoc()['cnt'];
    }
    $stmt->close();
}

        echo '<tr>';
        echo '<td><div>' . htmlspecialchars($row['title']) . '</div>';
        echo '<div class="history-date">' . htmlspecialchars(date('Y-m-d', strtotime($row['sendDate']))) . '</div></td>';
        echo '<td><span class="audience-tag ' . $audienceClass . '">' . htmlspecialchars(ucfirst($row['audienceRole'])) . '</span></td>';
        echo '<td><span class="priority-tag ' . $priorityClass . '">' . htmlspecialchars(ucfirst($row['priority'])) . '</span></td>';
        echo '<td><span class="status-tag ' . strtolower($status) . '">' . $statusIcon . ' ' . $status . '</span></td>';
        echo '<td>' . $recipientsCount . '</td>';
        echo '<td>' . (isset($row['views']) ? (int)$row['views'] : 0) . '</td>';
        echo '<td><span class="action-view">&#128065;</span> <span class="action-delete">&#128465;</span></td>';
        echo '</tr>';
            }
          } else {
            echo '<tr><td colspan="7">No notifications found.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
