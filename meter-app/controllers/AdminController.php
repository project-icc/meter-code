<?php
class AdminController {
    // แสดง Dashboard สำหรับ Admin
    public function dashboard() {
        echo "<h1>Admin Dashboard</h1>";
        echo "<p>Welcome, Admin!</p>";
    }

    // จัดการผู้ใช้ (เพิ่ม, ลบ, หรืออัปเดตข้อมูล)
    public function manageUsers() {
        echo "<h2>Manage Users</h2>";
        // ตัวอย่างโค้ดจัดการผู้ใช้
        // ดึงข้อมูลผู้ใช้ทั้งหมดจากฐานข้อมูล
        // แสดงข้อมูลผู้ใช้ในรูปแบบ HTML หรือ JSON
    }
}
?>
