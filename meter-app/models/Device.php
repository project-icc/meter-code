<?php
require_once __DIR__ . '/../config/database.php';

class Device {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    // ดึงข้อมูลอุปกรณ์ทั้งหมดของผู้ใช้
    public function getDevicesByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM devices WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เพิ่มอุปกรณ์ใหม่
    public function addDevice($userId, $name, $type) {
        $stmt = $this->pdo->prepare("INSERT INTO devices (user_id, name, type) VALUES (:user_id, :name, :type)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
?>
