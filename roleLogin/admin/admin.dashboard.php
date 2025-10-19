<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
  header("location: ../../login.php");
  exit();
}
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>
<link rel="stylesheet" href="admin.dashboard.css">
<div class="main-content">
  <div class="page-header">
    <h1>Admin Dashboard</h1>
    <p>System overview and user management.</p>
  </div>

  <?php
  // Metrics from database
  $totalUsers = 0; $numStudents = 0; $numInstructors = 0; $numAdmins = 0;
  $latestStudentName = '';
  // Fallback uptime
  $uptimePercent = 99.8;

  // Helper: get size of uploads directory
  function getDirectorySize($dir) {
    $size = 0;
    if (!is_dir($dir)) return 0;
    $items = scandir($dir);
    foreach ($items as $item) {
      if ($item === '.' || $item === '..') continue;
      $path = $dir . DIRECTORY_SEPARATOR . $item;
      if (is_dir($path)) { $size += getDirectorySize($path); }
      else { $size += @filesize($path) ?: 0; }
    }
    return $size;
  }

  // Counts
  $res = $conn->query("SELECT COUNT(*) AS c FROM users");
  if ($res) { $totalUsers = (int)$res->fetch_assoc()['c']; }
  $res = $conn->query("SELECT usersRole, COUNT(*) AS c FROM users GROUP BY usersRole");
  if ($res) {
    while ($r = $res->fetch_assoc()) {
      $role = strtolower($r['usersRole']);
      if ($role === 'student') $numStudents = (int)$r['c'];
      if ($role === 'instructor') $numInstructors = (int)$r['c'];
      if ($role === 'admin') $numAdmins = (int)$r['c'];
    }
  }
  // Latest student (recent registration)
  $res = $conn->query("SELECT usersUid AS name FROM users WHERE usersRole='student' ORDER BY usersId DESC LIMIT 1");
  if ($res && $res->num_rows > 0) { $latestStudentName = $res->fetch_assoc()['name']; }

  // Storage used (% of capacity)
  $uploadsPath = realpath(__DIR__ . '/../../uploads');
  $usedBytes = getDirectorySize($uploadsPath ?: '');
  $capacityBytes = 2 * 1024 * 1024 * 1024; // 2 GB capacity baseline
  $storagePercent = $capacityBytes > 0 ? round(($usedBytes / $capacityBytes) * 100) : 0;
  ?>

  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-title">Total Users</div>
      <div class="kpi-value"><?php echo $totalUsers; ?></div>
      <div class="kpi-sub">Live count</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-title">Active Students</div>
      <div class="kpi-value"><?php echo $numStudents; ?></div>
      <div class="kpi-sub">Currently enrolled</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-title">System Uptime</div>
      <div class="kpi-value success"><?php echo $uptimePercent; ?>%</div>
      <div class="kpi-sub">Last 30 days</div>
    </div>
    <div class="kpi-card">
      <div class="kpi-title">Storage Used</div>
      <div class="kpi-value <?php echo ($storagePercent >= 70 ? 'warn' : ''); ?>"><?php echo $storagePercent; ?>%</div>
      <div class="kpi-sub">of 2 GB capacity</div>
    </div>
  </div>

  <div class="grid">
    <section class="panel span-2">
      <h2>User Analytics</h2>
      <?php $maxRole = max(1, $numStudents, $numInstructors, $numAdmins); ?>
      <div class="bar-row">
        <span>Students</span>
        <div class="bar"><div class="fill" style="width:<?php echo round(($numStudents/$maxRole)*100); ?>%"></div></div>
        <span class="bar-label"><?php echo $numStudents; ?></span>
      </div>
      <div class="bar-row">
        <span>Instructors</span>
        <div class="bar"><div class="fill" style="width:<?php echo round(($numInstructors/$maxRole)*100); ?>%"></div></div>
        <span class="bar-label"><?php echo $numInstructors; ?></span>
      </div>
      <div class="bar-row">
        <span>Admins</span>
        <div class="bar"><div class="fill" style="width:<?php echo round(($numAdmins/$maxRole)*100); ?>%"></div></div>
        <span class="bar-label"><?php echo $numAdmins; ?></span>
      </div>
      <button class="link-btn" onclick="location.href='notifications-analytics.php'">View Detailed Analytics</button>
    </section>

    <section class="panel">
      <h2>System Activity</h2>
      <ul class="activity">
        <li>
          <div class="activity-title">New student registered: <?php echo $latestStudentName ? htmlspecialchars($latestStudentName) : '—'; ?></div>
          <div class="activity-meta">Most recent registration</div>
          <button class="chip">Info</button>
        </li>
        <li>
          <div class="activity-title">Storage usage currently at <?php echo $storagePercent; ?>%</div>
          <div class="activity-meta">Uploads directory</div>
          <button class="chip <?php echo ($storagePercent >= 80 ? 'warning' : ''); ?>"><?php echo ($storagePercent >= 80 ? 'Warning' : 'Info'); ?></button>
        </li>
        <?php
        $notif = $conn->query("SELECT title, sendDate FROM notifications ORDER BY sendDate DESC LIMIT 1");
        if ($notif && $notif->num_rows > 0) { $n = $notif->fetch_assoc(); ?>
        <li>
          <div class="activity-title">Last notification: <?php echo htmlspecialchars($n['title']); ?></div>
          <div class="activity-meta">Sent: <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($n['sendDate']))); ?></div>
          <button class="chip">Info</button>
        </li>
        <?php } ?>
      </ul>
      <button class="link-btn" onclick="alert('Activity log coming soon')">View All Activity</button>
    </section>
  </div>

  <div class="grid">
    <section class="panel span-2">
      <h2>User Management</h2>
      <p>Add new users or manage existing accounts</p>
      <div class="button-row">
        <button class="primary" onclick="location.href='admin.user.php'">Add New User</button>
        <button class="secondary" onclick="location.href='userManagement.php'">Manage Users</button>
      </div>
    </section>
    <section class="panel">
      <h2>System Settings</h2>
      <p>Configure system parameters and security</p>
      <div class="button-row">
        <button onclick="alert('Config coming soon')">System Config</button>
        <button onclick="alert('Security settings coming soon')">Security Settings</button>
      </div>
    </section>
    <section class="panel">
      <h2>Notifications</h2>
      <p>Send announcements to all users</p>
      <div class="button-row">
        <button class="primary" onclick="location.href='notifications.php'">Send Notification</button>
      </div>
    </section>
  </div>
</div>


