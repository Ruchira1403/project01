<?php
if (isset($_POST["submit"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    require_once 'dbh.inc.php';
    require_once 'functions.inc.php';

    if (emptyInputs($username, $password) !== false) {
        header("location: ../login.php?error=emptyinput");
        exit();
    }

    $uidExists = uidExists($conn, $username);
    if ($uidExists === false) {
        header("location: ../login.php?error=wronglogin");
        exit();
    }


    // Check role match
    if (strtolower($role) !== strtolower($uidExists["usersRole"])) {
        header("location: ../login.php?error=rolemismatch");
        exit();
    }

    // Check if user is deactive
    if (isset($uidExists["usersStatus"]) && $uidExists["usersStatus"] == 0) {
        header("location: ../login.php?error=deactive");
        exit();
    }

    $pwdHashed = $uidExists["usersPwd"];
    if (password_verify($password, $pwdHashed) || $password === $pwdHashed) {
        // Update lastLogin
        $userId = $uidExists["usersId"];
        $updateLoginSql = "UPDATE users SET lastLogin = NOW() WHERE usersId = ?";
        $stmt = mysqli_stmt_init($conn);
        if (mysqli_stmt_prepare($stmt, $updateLoginSql)) {
            mysqli_stmt_bind_param($stmt, "i", $userId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        session_start();
        $_SESSION["userid"] = $uidExists["usersId"];
        $_SESSION["useruid"] = $uidExists["usersUid"];
        $_SESSION["useremail"] = $uidExists["usersEmail"];
        $_SESSION["userrole"] = $uidExists["usersRole"];
        $role = strtolower($uidExists["usersRole"]);
        if ($role === "admin") {
            header("location: ../roleLogin/admin/admin.dashboard.php");
        } elseif ($role === "instructor") {
            header("location: ../roleLogin/instructor/instructor.home.php");
        } else {
            header("location: ../roleLogin/student/student.home.php");
        }
        exit();
    } else {
        header("location: ../login.php?error=wronglogin");
        exit();
    }
} else {
    header("location: ../login.php");
    exit();
}
