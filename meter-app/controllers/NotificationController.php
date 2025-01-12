<?php
class NotificationController {
    // แสดงการแจ้งเตือนสำหรับผู้ใช้
    public function listNotifications($userId) {
        echo "<h2>Notifications for User ID: $userId</h2>";
        // ตัวอย่างโค้ดดึงข้อมูลการแจ้งเตือน
        // SELECT * FROM notifications WHERE user_id = $userId
    }

    // เพิ่มการแจ้งเตือนใหม่
    public function addNotification($userId, $title, $message, $type) {
        echo "<h3>Adding Notification</h3>";
        // ตัวอย่างโค้ดเพิ่มข้อมูลการแจ้งเตือน
        // INSERT INTO notifications (user_id, title, message, type) VALUES ($userId, $title, $message, $type)
    }
}
?>
