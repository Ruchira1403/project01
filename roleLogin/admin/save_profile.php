<?php
session_start();
include_once '../../includes/dbh.inc.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit();
}

$viewerRole = $_SESSION['userrole'] ?? '';
$viewerId = $_SESSION['userid'] ?? 0;
$userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;

if (!$viewerId) {
    header('Location: ../../login.php');
    exit();
}

// Only allow admin to edit own profile, or admin to edit any (simple check)
if (strtolower($viewerRole) === 'admin' && $userId !== $viewerId) {
    echo 'Access denied.';
    exit();
}

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$title = trim($_POST['title'] ?? '');

// Handle avatar upload
$avatarFilename = '';
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        echo '<div style="color:red;">File upload error: ' . $_FILES['avatar']['error'] . '</div>';
    } else {
        $tmpName = $_FILES['avatar']['tmp_name'];
        $origName = basename($_FILES['avatar']['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            echo '<div style="color:red;">Invalid file type: ' . htmlspecialchars($ext) . '</div>';
        } else {
            $avatarFilename = 'profile_' . $userId . '_' . time() . '.' . $ext;
            $destDir = __DIR__ . '/uploads/avatar/';
            if (!is_dir($destDir)) mkdir($destDir, 0777, true);
            $destPath = $destDir . $avatarFilename;
            if (move_uploaded_file($tmpName, $destPath)) {
                echo '<div style="color:green;">File uploaded: ' . htmlspecialchars($avatarFilename) . '</div>';
            } else {
                echo '<div style="color:red;">Failed to move uploaded file.</div>';
                $avatarFilename = '';
            }
        }
    }
}

if ($userId <= 0) {
    header('Location: profile.php?error=invaliduser');
    exit();
}

// Update users table (email only)
$stmt = $conn->prepare('UPDATE users SET usersEmail = ? WHERE usersId = ?');
$stmt->bind_param('si', $email, $userId);
$stmt->execute();
$stmt->close();

// Upsert admin_profiles
if (!empty($avatarFilename)) {
    $prep = $conn->prepare('REPLACE INTO admin_profiles (userId, phone, address, title, avatar) VALUES (?, ?, ?, ?, ?)');
    $prep->bind_param('issss', $userId, $phone, $address, $title, $avatarFilename);
} else {
    $prep = $conn->prepare('REPLACE INTO admin_profiles (userId, phone, address, title, avatar) VALUES (?, ?, ?, ?, (SELECT avatar FROM admin_profiles WHERE userId = ?))');
    $prep->bind_param('isssi', $userId, $phone, $address, $title, $userId);
}
$prep->execute();
$prep->close();

header('Location: profile.php?success=1');
exit();
