<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["passwordRepeat"];

    require_once 'dbh.inc.php';
    require_once 'functions.inc.php';

    // Check for empty fields
    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
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
    // Insert user into database
    $sql = "INSERT INTO users (usersUid, usersEmail, usersPwd) VALUES (?, ?, ?)";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("location: ../signup.php?error=stmtfailed");
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPwd);
    if (!mysqli_stmt_execute($stmt)) {
        header("location: ../signup.php?error=stmtexecute");
        exit();
    }
    mysqli_stmt_close($stmt);
    // Redirect to login with success message
    header("location: ../login.php?signup=success");
    exit();
} else {
    header("location: ../signup.php");
    exit();
}
