<?php
session_start();
if (!isset($_SESSION["userid"])) {
  header("location: ../../login.php");
  exit();
}
include_once '../../includes/dbh.inc.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings</title>
  <link rel="stylesheet" href="settings.css">
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h2 class="logo">GeoSurvey</h2>
      <ul class="menu">
        <li><a href="../dashboard.php">Dashboard</a></li>
        <li><a href="../user-management.php">User Management</a></li>
        <li><a href="../notifications.php">Notifications</a></li>
        <li><a href="../analytics.php">Analytics</a></li>
        <li class="active"><a href="settings.php">Settings</a></li>
      </ul>
      <a href="../../logout.php" class="logout">Logout</a>
    </aside>

    <!-- Main Content -->
    <main class="main">
      <header class="topbar">
        <h1>System Settings</h1>
        <div class="user-info">
          <span class="user-name">John Doe</span>
          <span class="user-role">Student</span>
        </div>
      </header>

      <section class="settings">
        <div class="tabs">
          <button class="tab active">General</button>
          <button class="tab">Security</button>
          <button class="tab">Notifications</button>
          <button class="tab">Backup</button>
          <button class="tab">System</button>
        </div>

        <div class="settings-content">
          <h2>General Configuration</h2>

          <form>
            <div class="form-group">
              <label for="siteName">Site Name</label>
              <input type="text" id="siteName" value="GeoSurvey Academic Portal">
            </div>

            <div class="form-group">
              <label for="siteUrl">Site URL</label>
              <input type="text" id="siteUrl" value="https://geosurvey.university.edu">
            </div>

            <div class="form-group">
              <label for="siteDesc">Site Description</label>
              <textarea id="siteDesc">Academic field portal for surveying and geospatial studies.</textarea>
            </div>

            <div class="form-row">
              <div class="form-group half">
                <label for="adminEmail">Administrator Email</label>
                <input type="email" id="adminEmail" value="admin@university.edu">
              </div>
              <div class="form-group half">
                <label for="timezone">Timezone</label>
                <select id="timezone">
                  <option>Eastern Standard Time</option>
                  <option>Central Standard Time</option>
                  <option>Pacific Standard Time</option>
                </select>
              </div>
            </div>

            <div class="form-group toggle">
              <label>Allow Public Registration</label>
              <input type="checkbox" id="publicReg">
              <span class="toggle-slider"></span>
            </div>

            <div class="form-group">
              <label for="defaultRole">Default User Role</label>
              <select id="defaultRole">
                <option>Student</option>
                <option>Instructor</option>
                <option>Admin</option>
              </select>
            </div>

            <div class="actions">
              <button type="reset" class="reset">Reset to Default</button>
              <button type="submit" class="save">Save Changes</button>
            </div>
          </form>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
