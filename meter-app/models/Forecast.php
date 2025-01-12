<?php
require_once __DIR__ . '/../config/database.php';

class Forecast {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    // ดึงผลการพยากรณ์สำหรับผู้ใช้
    public function getForecastByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM forecasts WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // เพิ่มข้อมูลการพยากรณ์ใหม่
    public function addForecast($userId, $energyPredicted) {
        $stmt = $this->pdo->prepare("INSERT INTO forecasts (user_id, energy_predicted) VALUES (:user_id, :energy_predicted)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':energy_predicted', $energyPredicted);
        return $stmt->execute();
    }
}
?>
