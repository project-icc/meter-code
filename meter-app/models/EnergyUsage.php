<?php
require_once __DIR__ . '/../config/database.php';

class EnergyUsage {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    // ดึงข้อมูลการใช้พลังงานของผู้ใช้
    public function getEnergyUsageByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM energy_usages WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เพิ่มข้อมูลการใช้พลังงาน
    public function addEnergyUsage($deviceId, $energyConsumed, $cost) {
        $stmt = $this->pdo->prepare("INSERT INTO energy_usages (device_id, energy_consumed, cost) VALUES (:device_id, :energy_consumed, :cost)");
        $stmt->bindParam(':device_id', $deviceId, PDO::PARAM_INT);
        $stmt->bindParam(':energy_consumed', $energyConsumed);
        $stmt->bindParam(':cost', $cost);
        return $stmt->execute();
    }
}
?>
