<?php
$servername = "localhost";
$username = "iticc_meter";
$password = "adminmeter";
$dbname = "iticc_meter";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}
?>
