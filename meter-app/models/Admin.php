<?php
require_once __DIR__ . '/../config/database.php';

class Admin {
    private $pdo;

    public function __construct() {
        $database = new Database();
        $this->pdo = $database->getConnection();
    }

    // ดึงข้อมูล Admin ทั้งหมด
    public function getAllAdmins() {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'admin'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ดึงข้อมูล Admin ตาม ID
    public function getAdminById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id AND role = 'admin'");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
