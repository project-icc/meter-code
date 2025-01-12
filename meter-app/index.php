<?php
session_start(); // เริ่มต้น Session

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือยัง
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // เปลี่ยนเส้นทางไปยังหน้าเข้าสู่ระบบ
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - ICC Smart Meter</title>
</head>
<body>
    <h1>Welcome to ICC Smart Meter</h1>
    <p>You are logged in!</p>
    <a href="logout.php">Logout</a>
</body>
</html>
