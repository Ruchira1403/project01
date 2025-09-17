<?php
$serverName = "localhost";
$dbUsername = "ruchira01";
$dbPassword = "qUKDGHAXrM)/2nk@";
//$dbPassword = "Mgxb9ya(zcPUYjn-";
$dbName = "ruchira01";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "";
}