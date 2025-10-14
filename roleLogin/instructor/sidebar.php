<style>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 220px;
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
  margin-left: 32px;
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
  transition: background 0.2s, border-color 0.2s;
  border-bottom: none;
  border-top: none;
}
nav a.active, nav a:hover {
  background: #1e40af;
  border-left: 4px solid #60b3e6;
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
    <div class="role">Instructor</div>
    <nav>
        <a href="instructor.home.php" class="<?php echo ($currentPage == 'instructor.home.php') ? 'active' : ''; ?>">&#127968; Dashboard</a>
        <a href="instructor.attendance.php" class="<?php echo ($currentPage == 'instructor.attendance.php') ? 'active' : ''; ?>">&#128101; Students</a>
        <a href="instructor.submissions.php" class="<?php echo ($currentPage == 'instructor.submissions.php') ? 'active' : ''; ?>">&#128196; Submissions</a>
        <a href="instructor.assignments.php" class="<?php echo ($currentPage == 'instructor.assignments.php') ? 'active' : ''; ?>">&#128197; Schedule assignments</a>
        <a href="instructor.field_tasks.php" class="<?php echo ($currentPage == 'instructor.field_tasks.php') ? 'active' : ''; ?>">&#128218; Resources</a>
        <a href="instructor.viva_sessions.php" class="<?php echo ($currentPage == 'instructor.viva_sessions.php') ? 'active' : ''; ?>">&#128203; Viva Sessions</a>
        <a href="instructor.notifications.php" class="<?php echo ($currentPage == 'instructor.notifications.php') ? 'active' : ''; ?>">&#128276; notifications</a>
    </nav>
    <a href="../../login.php" class="logout">&#8592; Logout</a>
</div>
