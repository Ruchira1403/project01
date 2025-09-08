<?php
include_once 'header.php';
?>
<link rel="stylesheet" href="login.css">

<div class="page-frame"> <!-- blue page border like your screenshot -->
  <div class="container">

    <!-- LEFT: login card -->
    <div class="login-card">
      <div class="title-row">
        <img class="title-icon" src="images/logo.jpg" alt="icon"> <!-- optional small icon -->
        <h1 class="page-title">
          Welcome to GeoSurvey<br>
          Academic Field Portal
        </h1>
      </div>

      <form action="includes/login.inc.php" method="post" class="login-form" autocomplete="off">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

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