<?php
session_start();
require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        var_dump($email, $password); // ตรวจสอบค่าจากฟอร์ม
        var_dump($user); // ตรวจสอบข้อมูลที่ดึงจากฐานข้อมูล

        if ($user && password_verify($password, $user['password'])) {
            echo "Login successful!"; // ข้อความสำหรับ Debug
        } else {
            echo "Invalid email or password!";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
