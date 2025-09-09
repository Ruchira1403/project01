<?php
$serverName = "localhost";
$dbUsername = "user01";
$dbPassword = "Mgxb9ya(zcPUYjn-";
$dbName = "project01_login";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Database connected successfully.";
}