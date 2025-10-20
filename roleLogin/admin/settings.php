<?php
session_start();
if (!isset($_SESSION["userid"])) {
  header("location: ../../login.php");
  exit();
}
include_once '../../includes/dbh.inc.php';
include_once 'sidebar.php';
include_once 'topbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>System Settings</title>
  <link rel="stylesheet" href="settingsk.css">
</head>
<body>
  <div class="container">
    <!-- Main Content -->
    <main class="main" style="margin-left: 320px; padding: 20px; margin-top: 120px; max-width: 1560px; margin-right: 50px;">
      
    <div class="page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1560px;">
    <h1 style="margin:0; font-size:2.5em; font-weight:300;color: white;">&#9881; Settings</h1>
    <p style="margin:10px 0 0 0; opacity:0.9; font-size:1.1em; color: white;">Manage security and system settings.</p>
  </div>



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
