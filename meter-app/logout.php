<?php
session_start();
session_destroy(); // ล้างข้อมูลใน Session
header('Location: login.php'); // เปลี่ยนเส้นทางไปที่หน้า login.php
exit;
?>
