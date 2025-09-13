<?php
include_once 'header.php';
?>

<div class="login-container">
    <h2>Sign Up</h2>
    <form action="includes/signup.inc.php" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required autocomplete="username"><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required autocomplete="email"><br><br>
        <label for="role">Select your Role:</label>
        <select id="role" name="role" required>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Admin</option>
        </select><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required autocomplete="new-password"><br><br>
        <label for="passwordRepeat">Re Enter Password:</label>
        <input type="password" id="passwordRepeat" name="passwordRepeat" required autocomplete="new-password"><br><br>
        <button type="submit">Sign Up</button>
    </form>
    <p style="text-align:center;">Already have an account? <a href="login.php">Login</a></p>
    
</div>
<?php
include_once 'footer.php';
?>


