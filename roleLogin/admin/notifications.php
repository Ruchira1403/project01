<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
    header("location: ../../login.php");
    exit();
}
?>
<link rel="stylesheet" href="notifications.css">
<div class="sidebar">
    <div class="logo">GeoSurvey</div>
    <div class="role">Admin</div>
    <nav>
        <a href="admin.home.php" class="active">&#128200; User Management</a>
        <a href="notifications.php">&#128276; Notifications</a>
        <a href="#">&#128202; Analytics</a>
        <a href="#">&#9881; Settings</a>
    </nav>
    <a href="../logout.php" class="logout">&#8592; Logout</a>
</div>
<div class="topbar" style="margin-top:100px;">
  <div class="user">
    <style>html,body{overflow-y:hidden;}</style>
        <div class="avatar">IP</div>
        <div class="info">
            <div class="name"><?php echo htmlspecialchars($_SESSION['useruid']); ?></div>
            <div class="role">Admin</div>
        </div>
    </div>
</div>

<div class="main-content">
  <div class="notifications-header">
    <h1>Notifications</h1>
    <p>Send announcements and manage communications</p>
  <div class="notifications-container">
    <div class="notifications-tabs">
      <button>Compose</button>
      <button>History</button>
      <button>Analytics</button>
    </div>
    <div class="notifications-form-container">
      <h2>&#128227; Create New Notification</h2>
      <form class="notifications-form modern-form" method="post" action="#">
        <div class="modern-row">
          <div class="modern-field">
            <label for="audience-dropdown">Audience</label>
            <div class="modern-input-icon-group audience-dropdown-wrapper">
              <span class="modern-input-icon"></span>
              <div id="audience-dropdown" class="audience-dropdown" tabindex="0">Select audience...</div>
              <div class="audience-dropdown-list" style="display:none;">
                <label><input type="checkbox" value="all"> All Users</label><br>
                <label><input type="checkbox" value="students"> Students</label><br>
                <label><input type="checkbox" value="instructors"> Instructors</label><br>
                <label><input type="checkbox" value="admins"> Admins</label><br>
                <button type="button" id="audience-ok-btn" class="audience-ok-btn">OK</button>
              </div>
              <input type="hidden" name="audience" id="audience-hidden">
            </div>
          </div>
          <div class="modern-field">
            <label for="priority">Priority</label>
            <div class="modern-input-icon-group">
              <span class="modern-input-icon"></span>
              <select id="priority" name="priority" class="priority-select modern-input">
                <option value="info">Information</option>
                <option value="warning">Warning</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modern-field">
          <label for="title">Title</label>
          <input type="text" id="title" name="title" class="modern-input" placeholder="Enter notification title...">
        </div>
        <div class="modern-field">
          <label for="message">Message</label>
          <textarea id="message" name="message" class="modern-input" placeholder="Enter notification message..." rows="4"></textarea>
        </div>
        <div class="modern-actions">
          <button class="modern-btn send" type="submit">&#128227; Send Now</button>
          <button class="modern-btn schedule" type="button">&#128197; Schedule</button>
          <button class="modern-btn draft" type="button">Save as Draft</button>
        </div>
      </form>
      <script>
      // Custom multi-select dropdown for Audience
      const dropdown = document.getElementById('audience-dropdown');
      const list = dropdown.nextElementSibling;
      const okBtn = document.getElementById('audience-ok-btn');
      const hiddenInput = document.getElementById('audience-hidden');
      dropdown.addEventListener('click', function(e) {
        list.style.display = list.style.display === 'block' ? 'none' : 'block';
      });
      okBtn.addEventListener('click', function() {
        const checked = list.querySelectorAll('input[type=checkbox]:checked');
        const values = Array.from(checked).map(cb => cb.value);
        hiddenInput.value = values.join(',');
        dropdown.textContent = values.length ? values.map(v => v.charAt(0).toUpperCase() + v.slice(1)).join(', ') : 'Select audience...';
        list.style.display = 'none';
      });
      </script>
    </div>
  </div>
</div>
