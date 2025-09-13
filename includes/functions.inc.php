<?php 

function emptyInputs($username, $password) {
    return (empty($username) || empty($password));
}

function loginUser($conn, $username, $password) {
    $uidExists = uidExists($conn, $username);

    if ($uidExists === false) {
        header("location: ../login.php?error=wronglogin");
        exit();
    }

    $pwdHashed = $uidExists["usersPwd"];
    // If the password in DB is not hashed, allow plain text match (for legacy users only)
    if (password_verify($password, $pwdHashed) || $password === $pwdHashed) {
        session_start();
        $_SESSION["userid"] = $uidExists["usersId"];
        $_SESSION["useruid"] = $uidExists["usersUid"];
        $_SESSION["useremail"] = $uidExists["usersEmail"];
        $_SESSION["userrole"] = $uidExists["usersRole"];
        // Redirect based on role
        $role = strtolower($uidExists["usersRole"]);
        if ($role === "admin") {
            header("location: ../roleLogin/admin.home.php");
        } elseif ($role === "instructor") {
            header("location: ../roleLogin/instructor.home.php");
        } else {
            header("location: ../roleLogin/student.home.php");
        }
        exit();
    } else {
        header("location: ../login.php?error=wronglogin");
        exit();
    }
}

function uidExists($conn, $username) {
    $sql = "SELECT * FROM users WHERE usersUid = ? OR usersEmail = ? LIMIT 1";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        header("location: ../login.php?error=stmtfailed");
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $resultData = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($resultData)) {
        return $row;
    } else {
        return false;
    }

    mysqli_stmt_close($stmt);
}
