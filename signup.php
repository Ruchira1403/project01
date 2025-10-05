<?php include_once 'header.php'; ?>
<link rel="stylesheet" href="signup.css">
<div class="signup-main-bg">
    <div class="signup-container">
        <div class="signup-form-col">
            <h1><img src="images/logo.jpg" alt="Surveyor">Welcome to GeoSurvey Academic Field Portal</h1>
            <form action="includes/signup.inc.php" method="post">
                <label for="name">Name with Initials</label>
                <input type="text" id="name" name="name" required autocomplete="name">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
                <label for="role">Select your Role</label>
                <select id="role" name="role" required onchange="toggleBatchField()">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="admin">Admin</option>
                </select>
                <div id="batch-field" style="display:block;">
                    <label for="batch">Batch</label>
                    <input type="text" id="batch" name="batch" autocomplete="off" placeholder="e.g. 2021/2022">
                </div>
                <script>
                function toggleBatchField() {
                    var role = document.getElementById('role').value;
                    var batchDiv = document.getElementById('batch-field');
                    if (role === 'student') {
                        batchDiv.style.display = 'block';
                        document.getElementById('batch').required = true;
                    } else {
                        batchDiv.style.display = 'none';
                        document.getElementById('batch').required = false;
                    }
                }
                window.onload = toggleBatchField;
                </script>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <label for="passwordRepeat">Re Enter Password</label>
                <input type="password" id="passwordRepeat" name="passwordRepeat" required autocomplete="new-password">
                <button type="submit">Sign Up</button>
            </form>
            <div class="login-link">Already have an account? <a href="login.php">Login</a></div>
        </div>
        <div class="signup-img-col">
            <img src="images/im01.jpg" alt="Surveyor Illustration">
        </div>
    </div>
</div>
<?php include_once 'footer.php'; ?>


