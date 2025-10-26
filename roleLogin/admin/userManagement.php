<?php
// If this file is included by a controller that already prepared $users and counts,
// keep using them. If not (e.g. accessed directly), prepare defaults by querying DB.
if (!isset($users)) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../../includes/dbh.inc.php';
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
            if (isset($row['usersStatus']) && ($row['usersStatus'] == 1 || strtolower($row['usersStatus']) === 'active')) $active++;
        }
    }
}

?>
<div style="margin-top: 80px; padding: 20px;">

<div class="page-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1560px;">
    <h1 style="margin:0; font-size:2.5em; font-weight:300;color: white;">&#128101; User Management</h1>
    <p style="margin:10px 0 0 0; opacity:0.9; font-size:1.1em; color: white;">Manage students, instructors, and administrators.</p>
  </div>

<div class="stats-cards">
    <div class="stats-card"><h2><?php echo $totalUsers; ?></h2><p>Total Users</p></div>
    <div class="stats-card"><h2><?php echo $students; ?></h2><p>Students</p></div>
    <div class="stats-card"><h2><?php echo $instructors; ?></h2><p>Instructors</p></div>
    <div class="stats-card"><h2><?php echo $admins; ?></h2><p>Admins</p></div>
    <div class="stats-card"><h2 style="color:#1ca97c;"><?php echo $active; ?></h2><p>Active</p></div>
</div>
<form class="user-search-row" method="get" style="margin-bottom:0;">
    <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
    <select name="role">
        <option value="">All Roles</option>
        <option value="student" <?php if(($_GET['role'] ?? '')==='student') echo 'selected'; ?>>Student</option>
        <option value="instructor" <?php if(($_GET['role'] ?? '')==='instructor') echo 'selected'; ?>>Instructor</option>
        <option value="admin" <?php if(($_GET['role'] ?? '')==='admin') echo 'selected'; ?>>Admin</option>
    </select>
    <button type="submit">Filter</button>
    <a href="../../signup.php" style="margin-left:auto;"><button type="button" style="background:#0a3871; color:#fff;">&#128100; Add User</button></a>
</form>
<!-- CSV import form -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['import_message'])) {
    echo '<div class="import-message">' . htmlspecialchars($_SESSION['import_message']) . '</div>';
    unset($_SESSION['import_message']);
}
?>
<form action="import_users.php" method="post" enctype="multipart/form-data" style="margin-top:12px; display:flex; gap:8px; align-items:center;">
    <input type="file" name="userfile" accept=".csv" required>
    <select name="default_role">
        <option value="student">Student</option>
        <option value="instructor">Instructor</option>
        <option value="admin">Admin</option>
    </select>
    <button type="submit" name="import">Import CSV</button>
    <a href="user_import_template.csv" download style="margin-left:8px;">Download template</a>
</form>
<div class="user-directory">
    <h2 style="margin:18px 0 10px 0; font-size:1.3em; color:#1a3c6c;">User Directory</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $search = strtolower($_GET['search'] ?? '');
        $roleFilter = strtolower($_GET['role'] ?? '');
                foreach ($users as $user) {
                    $show = true;
                    if ($search && strpos(strtolower($user['usersUid']), $search) === false && strpos(strtolower($user['usersEmail']), $search) === false && strpos(strtolower($user['usersName']), $search) === false) $show = false;
                    if ($roleFilter && strtolower($user['usersRole']) !== $roleFilter) $show = false;
                    if (!$show) continue;
                    $roleClass = strtolower($user['usersRole']);
                    $statusClass = (isset($user['usersStatus']) && $user['usersStatus'] == 1) ? 'active' : 'inactive';
                    $statusText = (isset($user['usersStatus']) && $user['usersStatus'] == 1) ? 'Active' : 'Deactive';
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($user['usersName']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['usersUid']) . '</td>';
                    echo '<td>' . htmlspecialchars($user['usersEmail']) . '</td>';
                    echo '<td><span class="role-badge ' . $roleClass . '">' . ucfirst($user['usersRole']) . '</span></td>';
                    echo '<td><span class="status-badge ' . $statusClass . '">' . $statusText . '</span></td>';
                    // Format last login as 'x hours ago' or 'x days ago'
                    $lastLoginDisplay = '-';
                    if (!empty($user['lastLogin']) && $user['lastLogin'] !== 'NULL') {
                        $lastLogin = strtotime($user['lastLogin']);
                        $now = time();
                        $diff = $now - $lastLogin;
                        if ($diff < 60*60*24) {
                            $hours = floor($diff / 3600);
                            $lastLoginDisplay = $hours . ' hour' . ($hours == 1 ? '' : 's') . ' ago';
                            if ($hours < 1) $lastLoginDisplay = 'Just now';
                        } else {
                            $days = floor($diff / (60*60*24));
                            $lastLoginDisplay = $days . ' day' . ($days == 1 ? '' : 's') . ' ago';
                        }
                    }
                    echo '<td>' . $lastLoginDisplay . '</td>';
                    echo '<td>';
                    // If the user is a student, show a "View Profile" link to the student profile page
                    if (strtolower($user['usersRole']) === 'student') {
                        echo '<a class="action-btn view-profile" href="../student/profile.php?id=' . intval($user['usersId']) . '" title="View Profile" style="margin-right:6px; text-decoration:none;">View Profile</a>';
                    }
                    echo '<form method="post" action="editUser.php" style="display:inline;">';
                    echo '<input type="hidden" name="userid" value="' . htmlspecialchars($user['usersId']) . '">' ;
                    echo '<button class="action-btn edit" title="Edit"><span>&#9998;</span></button>';
                    echo '</form>';
                    echo '<button class="action-btn email" title="Email"><span>&#9993;</span></button>';
                    echo '</td>';
                    echo '</tr>';
                }
        ?>
        </tbody>
    </table>
</div>
</div>
