<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    // ดึงการแจ้งเตือนสำหรับผู้ใช้
    public function getNotificationsByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เพิ่มการแจ้งเตือนใหม่
    public function addNotification($userId, $title, $message, $type) {
        $stmt = $this->pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (:user_id, :title, :message, :type)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
?>
