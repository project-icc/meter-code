<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบและบทบาท
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// แสดงหน้า Dashboard สำหรับ Admin
require_once '../layouts/header.php';
require_once '../layouts/sidebar.php';
?>
<div class="content">
    <h1>Admin Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
</div>
<?php require_once '../layouts/footer.php'; ?>

