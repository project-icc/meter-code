<?php
// ข้อมูลการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "iticc_meter";
$password = "adminmeter";
$dbname = "iticc_meter";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Fail to Connect: " . $conn->connect_error);
}

// รับค่าที่ส่งมาจาก ESP8266
$device_id = $_POST['device_id']; // รับ device_id
$voltage = $_POST['voltage'];
$current = $_POST['current'];
$power = $_POST['power'];
$energy = $_POST['energy'];
$frequency = $_POST['frequency'];
$power_factor = $_POST['power_factor'];

// สร้างคำสั่ง SQL เพื่อบันทึกข้อมูลลงในฐานข้อมูล
$sql = "INSERT INTO PZEM_data (device_id, voltage, current, power, energy, frequency, power_factor)
        VALUES ('$device_id', '$voltage', '$current', '$power', '$energy', '$frequency', '$power_factor')";

// ตรวจสอบว่าการบันทึกข้อมูลสำเร็จหรือไม่
if ($conn->query($sql) === TRUE) {
    echo "Success!!";
} else {
    echo "Fail: " . $sql . "<br>" . $conn->error;
}

// ปิดการเชื่อมต่อ
$conn->close();
?>
