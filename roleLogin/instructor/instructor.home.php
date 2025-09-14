<?php
session_start();
include_once '../header.php';
?>
<div class="login-container">
    <h2>Welcome to Instructor panel!</h2>
    <?php if(isset($_SESSION['useruid']) && isset($_SESSION['useremail'])): ?>
        <p style="text-align:center; font-size:1.2em;">You are logged in as <strong><?php echo htmlspecialchars($_SESSION['useruid']); ?></strong><br>Email: <strong><?php echo htmlspecialchars($_SESSION['useremail']); ?></strong></p>
    <?php else: ?>
        <p style="text-align:center; color:red;">You are not logged in.</p>
    <?php endif; ?>
</div>
<?php
include_once '../footer.php';
?>

