<h1 style="font-size:2em; color:#1a3c6c; margin-bottom:8px;">User Management</h1>
<p style="color:#6b7a90; margin-bottom:24px;">Manage students, instructors, and administrators</p>
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
