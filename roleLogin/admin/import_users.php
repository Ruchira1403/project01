<?php
// import_users.php
// Handles CSV import for users. Expects CSV with headers: name,username,email,password,role(optional),batch(optional)

session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['import_message'] = 'Invalid request method.';
    header('Location: userManagement.php');
    exit();
}

if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_message'] = 'File upload failed. Please try again.';
    header('Location: userManagement.php');
    exit();
}

// Basic file validation
$allowed = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['userfile']['tmp_name']);
finfo_close($finfo);
$ext = strtolower(pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    $_SESSION['import_message'] = 'Only CSV files are allowed.';
    header('Location: userManagement.php');
    exit();
}

require_once __DIR__ . '/../../includes/dbh.inc.php';
require_once __DIR__ . '/../../includes/functions.inc.php';

$defaultRole = in_array($_POST['default_role'] ?? '', ['student','instructor','admin']) ? $_POST['default_role'] : 'student';

$handle = fopen($_FILES['userfile']['tmp_name'], 'r');
if ($handle === false) {
    $_SESSION['import_message'] = 'Unable to read uploaded file.';
    header('Location: userManagement.php');
    exit();
}

$header = fgetcsv($handle);
if ($header === false) {
    $_SESSION['import_message'] = 'CSV file is empty.';
    fclose($handle);
    header('Location: userManagement.php');
    exit();
}

// Normalize header
$columns = array_map(function($h){ return strtolower(trim($h)); }, $header);

$requiredCols = ['name','username','email','password'];
foreach ($requiredCols as $col) {
    if (!in_array($col, $columns)) {
        $_SESSION['import_message'] = "Missing required column: $col";
        fclose($handle);
        header('Location: userManagement.php');
        exit();
    }
}

$colIndex = array_flip($columns);
$inserted = 0;
$skipped = 0;
$errors = [];

// Prepared statements for student and non-student
$sqlStudent = "INSERT INTO users (usersName, usersUid, usersEmail, usersPwd, usersRole, batch) VALUES (?, ?, ?, ?, ?, ?)";
$sqlOther = "INSERT INTO users (usersName, usersUid, usersEmail, usersPwd, usersRole) VALUES (?, ?, ?, ?, ?)";

while (($row = fgetcsv($handle)) !== false) {
    // Skip empty rows
    if (count($row) === 1 && trim($row[0]) === '') continue;

    $name = $row[$colIndex['name']] ?? '';
    $username = $row[$colIndex['username']] ?? '';
    $email = $row[$colIndex['email']] ?? '';
    $password = $row[$colIndex['password']] ?? '';
    $role = $row[$colIndex['role']] ?? $defaultRole;
    $batch = $row[$colIndex['batch']] ?? null;

    $name = trim($name);
    $username = trim($username);
    $email = trim($email);
    $password = trim($password);
    $role = strtolower(trim($role));

    if (empty($name) || empty($username) || empty($email) || empty($password)) {
        $skipped++;
        $errors[] = "Missing required fields for username: $username";
        continue;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $skipped++;
        $errors[] = "Invalid email for username: $username";
        continue;
    }

    // Check if user exists by username or email
    if (uidExists($conn, $username) !== false) {
        $skipped++;
        $errors[] = "User already exists: $username";
        continue;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sqlStudent)) {
            $skipped++;
            $errors[] = "Prepare failed for student insert: $username";
            continue;
        }
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $username, $email, $hashed, $role, $batch);
        if (!mysqli_stmt_execute($stmt)) {
            $skipped++;
            $errors[] = "Insert failed for student: $username";
        } else {
            $inserted++;
        }
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sqlOther)) {
            $skipped++;
            $errors[] = "Prepare failed for insert: $username";
            continue;
        }
        mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $email, $hashed, $role);
        if (!mysqli_stmt_execute($stmt)) {
            $skipped++;
            $errors[] = "Insert failed for: $username";
        } else {
            $inserted++;
        }
        mysqli_stmt_close($stmt);
    }
}

fclose($handle);

$message = "Import finished. Inserted: $inserted. Skipped: $skipped.";
if (!empty($errors)) {
    $message .= ' Errors: ' . implode(' | ', array_slice($errors, 0, 10));
}

$_SESSION['import_message'] = $message;
header('Location: userManagement.php');
exit();
