<div class="topbar">
  <div class="user">
    <style>html,body{overflow-y:hidden;}</style>
        <div class="avatar">
            <?php
            // Display initials from usersName (useruid)
            if (isset($_SESSION['useruid'])) {
                $name = $_SESSION['useruid'];
                $initials = '';
                $parts = explode(' ', $name);
                foreach ($parts as $part) {
                    if (strlen($part) > 0) {
                        $initials .= strtoupper($part[0]);
                    }
                }
                echo $initials;
            } else {
                echo 'NA';
            }
            ?>
        </div>
        <div class="info">
            <div class="name"><?php echo htmlspecialchars($_SESSION['useruid']); ?></div>
            <div class="role">Admin</div>
        </div>
    </div>
</div>