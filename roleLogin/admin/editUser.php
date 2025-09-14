<?php
session_start();
if (!isset($_SESSION["userid"]) || strtolower($_SESSION["userrole"]) !== "admin") {
    header("location: ../login.php");
    exit();
}
require_once '../includes/dbh.inc.php';

// Get user id from POST or GET
$userId = $_POST['userid'] ?? $_GET['userid'] ?? null;
if (!$userId) {
    echo '<p style="color:red;">No user selected.</p>';
    exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $sql = "UPDATE users SET usersName=?, usersRole=?, usersStatus=? WHERE usersId=?";
    $stmt = mysqli_stmt_init($conn);
    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssii", $name, $role, $status, $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header("location: admin.home.php?edit=success");
        exit();
    } else {
        echo '<p style="color:red;">Update failed.</p>';
    }
}

// Fetch user data
$sql = "SELECT usersId, usersName, usersRole, usersStatus FROM users WHERE usersId=?";
$stmt = mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo '<p style="color:red;">User not found.</p>';
    exit();
}
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$user) {
    echo '<p style="color:red;">User not found.</p>';
    exit();
}
?>
<link rel="stylesheet" href="admin.home.css">
<div class="main-content" style="max-width:500px;margin:40px auto;background:#fff;padding:32px 28px;border-radius:12px;box-shadow:0 2px 8px #e0e7ef;">
  <h2 style="color:#1a3c6c;">Edit User</h2>
  <form method="post">
    <input type="hidden" name="userid" value="<?php echo htmlspecialchars($user['usersId']); ?>">
    <label for="name">Name with Initials</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['usersName']); ?>" required>
    <label for="role">Role</label>
    <select id="role" name="role" required>
      <option value="student" <?php if($user['usersRole']==='student') echo 'selected'; ?>>Student</option>
      <option value="instructor" <?php if($user['usersRole']==='instructor') echo 'selected'; ?>>Instructor</option>
      <option value="admin" <?php if($user['usersRole']==='admin') echo 'selected'; ?>>Admin</option>
    </select>
    <label for="status">Status</label>
    <select id="status" name="status" required>
      <option value="1" <?php if($user['usersStatus']==1) echo 'selected'; ?>>Active</option>
      <option value="0" <?php if($user['usersStatus']==0) echo 'selected'; ?>>Deactive</option>
    </select>
    <button type="submit" name="update" style="margin-top:18px;">Update User</button>
    <a href="admin.home.php" style="margin-left:18px;">Cancel</a>
  </form>
</div>
