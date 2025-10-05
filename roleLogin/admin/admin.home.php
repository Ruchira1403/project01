
<?php
// Modern Admin Panel Layout with Sidebar and Topbar
// Only for admin users
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
        header("location: ../../login.php");
        exit();
}
require_once '../../includes/dbh.inc.php';

$totalUsers = $students = $instructors = $admins = $active = 0;
$users = [];
$sql = "SELECT usersId, usersName, usersUid, usersEmail, usersRole, usersStatus, lastLogin FROM users";
$result = mysqli_query($conn, $sql);
if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
            $totalUsers++;
            if (strtolower($row['usersRole']) === 'student') $students++;
            if (strtolower($row['usersRole']) === 'instructor') $instructors++;
            if (strtolower($row['usersRole']) === 'admin') $admins++;
            if (isset($row['usersStatus']) && strtolower($row['usersStatus']) === 'active') $active++;
        }
}
?>
<link rel="stylesheet" href="admin.home.css">
<?php include 'sidebar.php'; ?>
<?php include 'topbar.php'; ?>
<div class="main-content">
    <?php include 'userManagement.php'; ?>
</div>

