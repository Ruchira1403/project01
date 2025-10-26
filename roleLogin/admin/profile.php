<?php
session_start();
include_once 'sidebar.php';
include_once 'topbar.php';
include_once '../../includes/dbh.inc.php';

$viewerRole = $_SESSION['userrole'] ?? '';
$viewerId = $_SESSION['userid'] ?? 0;

// Determine target admin ID
$adminId = isset($_GET['id']) ? intval($_GET['id']) : $viewerId;

if (!$viewerId) {
    header('Location: ../../login.php');
    exit();
}

// Only allow admin to view/edit own profile, or super-admin to view/edit any (simple check: allow same role)
if (strtolower($viewerRole) === 'admin' && $adminId !== $viewerId) {
    echo 'Access denied.';
    exit();
}

// Fetch base user data
$stmt = $conn->prepare('SELECT usersId, usersName, usersUid, usersEmail, usersRole FROM users WHERE usersId = ? LIMIT 1');
$stmt->bind_param('i', $adminId);
$stmt->execute();
$uRes = $stmt->get_result();
$user = $uRes->fetch_assoc();
$stmt->close();

// Fetch profile data from admin_profiles table (nullable)
$profile = [
    'phone' => '',
    'address' => '',
    'title' => '',
    'avatar' => ''
];

$prep = $conn->prepare('SELECT phone, address, title, avatar FROM admin_profiles WHERE userId = ? LIMIT 1');
if ($prep) {
    $prep->bind_param('i', $adminId);
    $prep->execute();
    $pRes = $prep->get_result();
    if ($pRes && $pRes->num_rows > 0) {
        $profile = $pRes->fetch_assoc();
    }
    $prep->close();
} else {
    // admin_profiles table may not exist or prepare failed; leave $profile as defaults
    // You can create the table with:
    // CREATE TABLE admin_profiles (
    //   userId INT PRIMARY KEY,
    //   phone VARCHAR(32),
    //   address VARCHAR(255),
    //   title VARCHAR(128),
    //   avatar VARCHAR(128)
    // );
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Profile</title>
    <link rel="stylesheet" href="profile.css">
    <style>
        body { font-family: Arial, sans-serif; color:#111; }
        .container { max-width:1000px; margin-top:150px; margin-left:350px;  }
        .card { border:1px solid #ccc; padding:20px; display:flex; gap:20px; align-items:flex-start; }
        .avatar { width:120px; height:120px; border-radius:60px; background:#3b82f6; color:#fff; display:flex; align-items:center; justify-content:center; font-size:36px; }
        .left { width:240px; }
        .right { flex:1; display:flex; justify-content:space-between; }
        .section { width:48%; }
        .label { font-weight:bold; margin-bottom:8px; }
        .field { margin-bottom:12px; }
        .edit-btn { display:inline-block; padding:8px 12px; background:#ef4444; color:#fff; border-radius:6px; text-decoration:none; }
        .save-btn { background:#059669; color:white; padding:8px 12px; border:none; border-radius:6px; cursor:pointer; }
        .input { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Profile</h1>
        <p>Manage your personal information</p>
        <div class="card">
            <div class="left">
                <div class="avatar">
                <?php
                    if (!empty($profile['avatar']) && file_exists(__DIR__ . '/uploads/avatar/' . $profile['avatar'])) {
                        echo '<img src="uploads/avatar/' . htmlspecialchars($profile['avatar']) . '" alt="Profile Photo" style="width:100%;height:100%;object-fit:cover;border-radius:60px;">';
                    } else {
                        $initials = '';
                        if (!empty($user['usersName'])) {
                            $parts = explode(' ', $user['usersName']);
                            foreach ($parts as $part) if ($part) $initials .= strtoupper($part[0]);
                        } else {
                            $initials = 'NA';
                        }
                        echo htmlspecialchars(substr($initials,0,2));
                    }
                ?>
                </div>
                <div style="margin-top:12px; font-weight:bold; font-size:1.05em;"><?php echo htmlspecialchars($user['usersName']); ?></div>
                <div style="color:#666;">ID : <?php echo htmlspecialchars($user['usersUid']); ?></div>
                <div style="margin-top:8px;">
                    <?php if ($viewerId === $adminId || strtolower($viewerRole) === 'admin'): ?>
                        <a class="edit-btn" href="#edit" onclick="document.getElementById('edit-form').style.display='block';return false;">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="right">
                <div class="section">
                    <div class="label">Contact Information</div>
                    <div class="field">📧 <?php echo htmlspecialchars($user['usersEmail'] ?? ''); ?></div>
                    <div class="field">📞 <?php echo htmlspecialchars($profile['phone']); ?></div>
                    <div class="field">📍 <?php echo htmlspecialchars($profile['address']); ?></div>
                </div>
                <div class="section">
                    <div class="label">Position</div>
                    <div class="field">🏷️ <?php echo htmlspecialchars($profile['title']); ?></div>
                </div>
            </div>
        </div>
        <div id="edit-form" style="display:none; margin-top:20px;">
            <form method="post" action="save_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="userId" value="<?php echo intval($adminId); ?>">
                <div style="display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="label">Email</label>
                        <input class="input" name="email" value="<?php echo htmlspecialchars($user['usersEmail']); ?>">
                    </div>
                    <div style="flex:1;">
                        <label class="label">Phone</label>
                        <input class="input" name="phone" value="<?php echo htmlspecialchars($profile['phone']); ?>">
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="label">Address</label>
                        <input class="input" name="address" value="<?php echo htmlspecialchars($profile['address']); ?>">
                    </div>
                    <div style="flex:1;">
                        <label class="label">Title/Position</label>
                        <input class="input" name="title" value="<?php echo htmlspecialchars($profile['title']); ?>">
                    </div>
                </div>
                <div style="margin-top:12px; display:flex; gap:12px;">
                    <div style="flex:1;">
                        <label class="label">Profile Photo</label>
                        <input type="file" name="avatar" accept="image/*" class="input">
                        <?php if (!empty($profile['avatar']) && file_exists(__DIR__ . '/uploads/' . $profile['avatar'])): ?>
                            <div style="margin-top:8px;"><img src="uploads/<?php echo htmlspecialchars($profile['avatar']); ?>" alt="Current Photo" style="width:60px;height:60px;border-radius:30px;object-fit:cover;"></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="margin-top:12px;">
                    <button class="save-btn" type="submit">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
