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

// If viewer is student and trying to edit another student's profile, block
if (strtolower($viewerRole) === 'student' && $userId !== $viewerId) {
    echo 'Access denied.';
    exit();
}


$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$degree = trim($_POST['degree'] ?? '');
$advisor = trim($_POST['advisor'] ?? '');
$expected = trim($_POST['expected_graduation'] ?? '');

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
            $destPath = __DIR__ . '/uploads/avatar/' . $avatarFilename;
            if (move_uploaded_file($tmpName, $destPath)) {
                echo '<div style="color:green;">File uploaded: ' . htmlspecialchars($avatarFilename) . '</div>';
            } else {
                echo '<div style="color:red;">Failed to move uploaded file.</div>';
                $avatarFilename = '';
            }
        }
    }
}

// Basic validation
if ($userId <= 0) {
    header('Location: profile.php?error=invaliduser');
    exit();
}

// Update users.email if changed
if ($email !== '') {
    $uStmt = $conn->prepare('UPDATE users SET usersEmail = ? WHERE usersId = ?');
    $uStmt->bind_param('si', $email, $userId);
    $uStmt->execute();
    $uStmt->close();
}

// Upsert profile record
$check = $conn->prepare('SELECT id FROM student_profiles WHERE userId = ? LIMIT 1');
$check->bind_param('i', $userId);
$check->execute();
$checkRes = $check->get_result();
$exists = ($checkRes && $checkRes->num_rows > 0);
$check->close();

if ($exists) {
    if ($avatarFilename !== '') {
        $upd = $conn->prepare('UPDATE student_profiles SET phone = ?, address = ?, degree = ?, advisor = ?, expected_graduation = ?, avatar = ? WHERE userId = ?');
        $upd->bind_param('ssssssi', $phone, $address, $degree, $advisor, $expected, $avatarFilename, $userId);
        $upd->execute();
        $upd->close();
    } else {
        $upd = $conn->prepare('UPDATE student_profiles SET phone = ?, address = ?, degree = ?, advisor = ?, expected_graduation = ? WHERE userId = ?');
        $upd->bind_param('sssssi', $phone, $address, $degree, $advisor, $expected, $userId);
        $upd->execute();
        $upd->close();
    }
} else {
    $insSql = 'INSERT INTO student_profiles (userId, phone, address, degree, advisor, expected_graduation' . ($avatarFilename ? ', avatar' : '') . ') VALUES (?, ?, ?, ?, ?, ?' . ($avatarFilename ? ', ?' : '') . ')';
    if ($avatarFilename) {
        $ins = $conn->prepare($insSql);
        $ins->bind_param('issssss', $userId, $phone, $address, $degree, $advisor, $expected, $avatarFilename);
    } else {
        $ins = $conn->prepare($insSql);
        $ins->bind_param('isssss', $userId, $phone, $address, $degree, $advisor, $expected);
    }
    $ins->execute();
    $ins->close();
}

header('Location: profile.php?id=' . intval($userId) . '&saved=1');
exit();
?>