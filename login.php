<?php
include_once 'header.php';
?>
<link rel="stylesheet" href="login.css">

<?php
$errorMsg = '';
if (isset($_GET['error'])) {
  if ($_GET['error'] === 'emptyinput') {
    $errorMsg = 'Please fill in all fields.';
  } elseif ($_GET['error'] === 'wronglogin') {
    $errorMsg = 'Incorrect username, password, or account does not exist.';
  } elseif ($_GET['error'] === 'rolemismatch') {
    $errorMsg = 'Role is mismatch. Please select the correct role for your account.';
  } elseif ($_GET['error'] === 'deactive') {
    $errorMsg = 'Your account is deactivated. Please contact the administrator.';
  }
}
?>

<div class="page-frame">
  <div class="container">
    <div class="login-card">
      <div class="title-row">
        <img class="title-icon" src="images/logo.jpg" alt="icon">
        <h1 class="page-title">
          Welcome to GeoSurvey<br>
          Academic Field Portal
        </h1>
      </div>
      <?php if ($errorMsg): ?>
        <div style="color: #b30000; background: #ffeaea; border: 1px solid #ffb3b3; padding: 10px 18px; border-radius: 6px; margin-bottom: 18px; text-align: center; font-size: 1.1em;">
          <?php echo $errorMsg; ?>
        </div>
      <?php endif; ?>
      <form action="includes/login.inc.php" method="post" class="login-form" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="username">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <label for="role">Select your Role</label>
        <select id="role" name="role" required>
          <option value="student">Student</option>
          <option value="instructor">Instructor</option>
          <option value="admin">Admin</option>
        </select>

        <button name="submit" type="submit" class="login-btn">
          Login to GeoSurvey Academic Field Portal
        </button>
      </form>

      <div class="forgot">
        <a href="#">Forgotten your username or password?</a>
      </div>
    </div>

    <!-- RIGHT: image card -->
    <div class="image-card">
      <img class="image" src="images/im01.jpg" alt="Surveyor illustration">
    </div>

  </div>
</div>

<?php
include_once 'footer.php';
?>