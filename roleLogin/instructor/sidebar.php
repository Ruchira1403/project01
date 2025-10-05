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
  font-size: 1.5em;
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

<div class="sidebar">
    <div class="logo">GeoSurvey</div>
    <div class="role">Instructor</div>
    <nav>
        <a href="instructor.home.php" class="active">&#127968; Dashboard</a>
        <a href="instructor.attendance.php">&#128101; attendence</a>
        <a href="instructor.submissions.php">&#128196; Submissions</a>
        <a href="instructor.assignments.php">&#128197; Schedule</a>
        <a href="instructor.field_tasks.php">&#128218; Field Tasks</a>
        <a href="instructor.viva_sessions.php">&#128203; Viva Sessions</a>
        <a href="instructor.notifications.php">&#128276; Notifications</a>
    </nav>
    <a href="../../login.php" class="logout">&#8592; Logout</a>
</div>
