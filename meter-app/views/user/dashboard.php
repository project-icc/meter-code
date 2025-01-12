<?php
session_start();
include __DIR__ . '/../../config/database.php'; // โหลดไฟล์ฐานข้อมูล

// สร้างการเชื่อมต่อฐานข้อมูล
$database = new Database();
$conn = $database->getConnection();

// ตรวจสอบว่า id มีใน Session
$user_id = $_SESSION['user_id'] ?? 1; // ใช้ user_id สมมติถ้าไม่มี session

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$query = "SELECT id, name, profile_picture FROM users WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// หากไม่มีข้อมูลผู้ใช้
if (!$user) {
    die("User not found.");
}

// ตั้งค่ารูปภาพเริ่มต้นหากไม่มีรูปภาพในฐานข้อมูล
$profile_picture = $user['profile_picture'] ?: 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <!-- MDB CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    <style>
        .navbar-avatar img {
            width: 32px;
            height: 32px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <!-- Avatar Dropdown -->
                <li class="nav-item dropdown navbar-avatar">
                    <a
                        class="nav-link dropdown-toggle d-flex align-items-center"
                        href="#"
                        id="navbarDropdownMenuLink"
                        role="button"
                        aria-expanded="false"
                        data-mdb-toggle="dropdown"
                    >
                        <img
                            src="public/assets/images/<?= htmlspecialchars($profile_picture) ?>"
                            class="rounded-circle"
                            alt="<?= htmlspecialchars($user['name']) ?>"
                        />
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <li><a class="dropdown-item" href="#">My profile</a></li>
                        <li><a class="dropdown-item" href="#">Settings</a></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
</body>
</html>

















<?php
// session_start();

// ตรวจสอบการเข้าสู่ระบบและบทบาท (user)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
//     header("Location: ../../login.php");
//     exit;
// }

// echo "<h1>User Dashboard</h1>";
// echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";
?>
