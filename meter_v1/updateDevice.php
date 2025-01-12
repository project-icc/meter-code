<?php
session_start();

// ตรวจสอบว่ามีการ login หรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// การเชื่อมต่อฐานข้อมูล
$host = 'localhost';
$dbname = 'iticc_meter';
$username = 'iticc_meter';
$password = 'adminmeter';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // ตรวจสอบว่ามีข้อมูลที่จำเป็นครบหรือไม่
        if (!isset($_POST['device_id'], $_POST['name'], $_POST['location'], $_POST['id_card'])) {
            throw new Exception('Missing required fields');
        }
        
        // ทำความสะอาดและตรวจสอบข้อมูล
        $deviceId = filter_var($_POST['device_id'], FILTER_SANITIZE_NUMBER_INT);
        $name = trim(filter_var($_POST['name'], FILTER_SANITIZE_STRING));
        $location = trim(filter_var($_POST['location'], FILTER_SANITIZE_STRING));
        $idCard = filter_var($_POST['id_card'], FILTER_SANITIZE_STRING);
        
        // ตรวจสอบว่า user มีสิทธิ์แก้ไขอุปกรณ์นี้หรือไม่
        $stmt = $pdo->prepare("SELECT device_id FROM relay_control WHERE device_id = ? AND id_card = ?");
        $stmt->execute([$deviceId, $idCard]);
        
        if (!$stmt->fetch()) {
            throw new Exception('Unauthorized to modify this device');
        }
        
        // อัพเดทข้อมูล
        $updateStmt = $pdo->prepare("
            UPDATE relay_control 
            SET Name = ?, Location = ? 
            WHERE device_id = ? AND id_card = ?
        ");
        
        $success = $updateStmt->execute([$name, $location, $deviceId, $idCard]);
        
        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Device updated successfully'
            ]);
        } else {
            throw new Exception('Failed to update device');
        }
    } else {
        throw new Exception('Invalid request method');
    }
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}