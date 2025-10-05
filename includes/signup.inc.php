<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $batch = isset($_POST["batch"]) ? $_POST["batch"] : null;
    $password = $_POST["password"];
    $passwordRepeat = $_POST["passwordRepeat"];

    require_once 'dbh.inc.php';
    require_once 'functions.inc.php';

    // Check for empty fields
    if (empty($name) || empty($username) || empty($email) || empty($role) || empty($password) || empty($passwordRepeat) || ($role === 'student' && empty($batch))) {
        header("location: ../signup.php?error=emptyinput");
        exit();
    }
    // Check if passwords match
    if ($password !== $passwordRepeat) {
        header("location: ../signup.php?error=passwordsdontmatch");
        exit();
    }
    // Check if username or email already exists
    if (uidExists($conn, $username) !== false) {
        header("location: ../signup.php?error=usernametaken");
        exit();
    }
    // Hash password
    $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
    // Insert user into database (add batch for students)
    if ($role === 'student') {
        $sql = "INSERT INTO users (usersName, usersUid, usersEmail, usersPwd, usersRole, batch) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("location: ../signup.php?error=stmtfailed");
            exit();
        }
        mysqli_stmt_bind_param($stmt, "ssssss", $name, $username, $email, $hashedPwd, $role, $batch);
        if (!mysqli_stmt_execute($stmt)) {
            header("location: ../signup.php?error=stmtexecute");
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        $sql = "INSERT INTO users (usersName, usersUid, usersEmail, usersPwd, usersRole) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("location: ../signup.php?error=stmtfailed");
            exit();
        }
        mysqli_stmt_bind_param($stmt, "sssss", $name, $username, $email, $hashedPwd, $role);
        if (!mysqli_stmt_execute($stmt)) {
            header("location: ../signup.php?error=stmtexecute");
            exit();
        }
        mysqli_stmt_close($stmt);
    }
    // Redirect to login with success message
    header("location: ../login.php?signup=success");
    exit();
} else {
    header("location: ../signup.php");
    exit();
}
