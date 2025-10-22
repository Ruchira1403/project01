<?php
$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "";
//$dbPassword = "Mgxb9ya(zcPUYjn-";
$dbName = "ishu01";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "";
}