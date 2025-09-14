<?php
$serverName = "localhost";
$dbUsername = "ruchira01";
$dbPassword = "Cvp/jrnn)XQnFOnp";
//$dbPassword = "Mgxb9ya(zcPUYjn-";
$dbName = "ruchira01";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Database connected successfully.";
}