<style>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 260px;
  height: 100vh;
  background: #ecedef;
  color: #201490;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  padding: 32px 0 0 0;
  z-index: 100;
}
.logo {
  font-size: 2.2em;
  font-weight: bold;
  margin-bottom: 18px;
}
.role {
  font-size: 1.1em;
  margin-left: 32px;
  margin-bottom: 24px;
  color: #dbeafe;
}
nav {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: auto;
  border: none;
}
nav a {
  color: #131212;
  text-decoration: none;
  padding: 12px 32px;
  font-size: 1.08em;
  border-left: 4px solid transparent;
  transition: all 0.3s ease;
  border-bottom: none;
  border-top: none;
  position: relative;
  overflow: hidden;
}

/* Click animation effect */
nav a:active {
  transform: scale(0.98);
  background: #1e3a8a;
  border-left: 4px solid #3b82f6;
}

/* Ripple effect on click */
nav a::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

nav a:active::before {
  width: 300px;
  height: 300px;
}

/* User Management blue styling when active */
nav a[href="admin.home.php"].active {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: #ffffff;
  border-left: 4px solid #60b3e6;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

nav a[href="admin.home.php"]:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  transform: translateX(5px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

nav a[href="admin.home.php"]:active {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
  color: #ffffff;
  transform: scale(0.98);
}

/* Notifications blue styling when active */
nav a[href="notifications.php"].active {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  color: #ffffff;
  border-left: 4px solid #60b3e6;
  box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

nav a[href="notifications.php"]:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  transform: translateX(5px);
  box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
}

nav a[href="notifications.php"]:active {
  background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
  color: #ffffff;
  transform: scale(0.98);
}
.logout {
  color: red;
  text-decoration: none;
  font-size: 1.08em;
  width: 100%;
  display: block;
  margin-top: auto;
  transition: background-color 0.2s;
  text-align: left;
  font-weight: bold;
  margin-bottom: 50px;
  border: none;
  border-top: none;
  border-bottom: none;
  padding: 15px 32px;
}
</style>

<?php
// Get current page name for active highlighting
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="logo">GeoSurvey</div>
    <div class="role" style="color: #201490; margin-top: -12px; margin-bottom: 32px;">
        <?php 
        // Get user role from session, fallback to 'Admin' if not set
        $userRole = isset($_SESSION['userrole']) ? ucfirst($_SESSION['userrole']) : 'Admin';
        echo $userRole;
        ?>
    </div>
    <nav>
        <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">&#127968; Dashboard</a>
        <a href="admin.home.php" class="<?php echo ($currentPage == 'admin.home.php') ? 'active' : ''; ?>">&#128101; User Management</a>
        <a href="notifications.php" class="<?php echo ($currentPage == 'notifications.php') ? 'active' : ''; ?>">&#128276; Notifications</a>
        <a href="#" class="<?php echo ($currentPage == 'analytics.php') ? 'active' : ''; ?>">&#128202; Analytics</a>
        <a href="#" class="<?php echo ($currentPage == 'settings.php') ? 'active' : ''; ?>">&#9881; Settings</a>
    </nav>
    <a href="../../login.php" class="logout">&#8592; Logout</a>
</div>