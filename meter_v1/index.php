<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';
$dbname = 'iticc_meter';
$username = 'iticc_meter';
$password = 'adminmeter';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("
        SELECT 
            rc.device_id,
            rc.state,
            rc.location,
            rc.Name,
            COALESCE(p1.voltage, 0) as voltage,
            COALESCE(p1.current, 0) as current,
            COALESCE(p1.power, 0) as power,
            COALESCE(p1.energy, 0) as energy,
            COALESCE(p1.frequency, 0) as frequency
        FROM relay_control rc
        LEFT JOIN (
            SELECT p1.* 
            FROM PZEM_data p1
            INNER JOIN (
                SELECT device_id, MAX(Date) as max_date
                FROM PZEM_data
                GROUP BY device_id
            ) p2 ON p1.device_id = p2.device_id AND p1.Date = p2.max_date
        ) p1 ON rc.device_id = p1.device_id
        WHERE rc.id_card = :user_id
        ORDER BY rc.device_id ASC;
    ");
    
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $deviceData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $userStmt = $pdo->prepare("SELECT id_card, username FROM regis WHERE id_card = :user_id");
    $userStmt->execute(['user_id' => $_SESSION['user_id']]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styleindex.css">
    <title>Real-Time Data Display</title>
</head>
<body>
    <div class="container">
        <button class="menuButton" onclick="toggleMenu()">&#9776;</button>
        <div id="menuDropdown" class="dropdownMenu">
        <div class="profile-menu-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="profile-icon">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="profile-name"><?php echo htmlspecialchars($userData['username']); ?></span>
                </div>
                <a href="index.php">หน้าหลัก</a>
                <a href="#" onclick="openModel('addDeviceModel')">เพิ่มอุปกรณ์</a>
                <a href="Grahp.php">กราฟ</a>
                <a href="logout.php">ออกจากระบบ</a>
            </div>
            <div id="addDeviceModel" class="model">
                <div class="model-content">
                    <span class="close" onclick="closeModel('addDeviceModel')">&times;</span>
                    <h2>เพิ่มอุปกรณ์</h2>
                    <form id="deviceForm">
                        <input type="hidden" id="id_card" name="id_card" value="<?php echo $userData['id_card']; ?>">
                        <input type="text" id="deviceName" name="Name" placeholder="ชื่ออุปกรณ์">
                        <input type="text" id="deviceLocation" name="Location" placeholder="สถานที่ติดตั้งอุปกรณ์">
                        <div style="text-align: center;">
                            <button type="button" onclick="addDevice()">บันทึก</button>
                            <button type="button" onclick="closeModel('editDeviceModel')" style="background-color: #6c757d;">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        
        <?php if (empty($deviceData)): ?>
            <div class="image-container">
                <img src="img/box.png" alt="Box Image">
                <div>ไม่มีอุปกรณ์</div>
            </div>
        <?php else: ?>
            <?php foreach ($deviceData as $data): ?>
                <div class="card" onclick="openEditModel('editDeviceModel', event, <?php echo htmlspecialchars($data['device_id']); ?>, '<?php echo htmlspecialchars($data['location']); ?>', '<?php echo htmlspecialchars($data['Name']); ?>')">
                    <div class="card-header">
                        <h2>
                            Device <?php echo htmlspecialchars($data['device_id']); ?>
                            <?php if (!empty($data['location'])): ?>
                                <span class="device-location">(<?php echo htmlspecialchars($data['location']); ?>)</span>
                            <?php endif; ?>
                        </h2>
                        <div class="card-controls" onclick="event.stopPropagation();">
                            <label class="switch">
                                <input type="checkbox" 
                                    <?php echo $data['state'] == '1' ? 'checked' : ''; ?>
                                    onchange="toggleStatus(event, <?php echo htmlspecialchars($data['device_id']); ?>)">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="card-content">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="font-medium">Voltage (V)</td>
                                    <td class="text-right"><?php echo number_format($data['voltage'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Current (A)</td>
                                    <td class="text-right"><?php echo number_format($data['current'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Power (W)</td>
                                    <td class="text-right"><?php echo number_format($data['power'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Energy (kWh)</td>
                                    <td class="text-right"><?php echo number_format($data['energy'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Frequency (Hz)</td>
                                    <td class="text-right"><?php echo number_format($data['frequency'], 2); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
            <div id="editDeviceModel" class="model">
                <div class="model-content">
                    <span class="close" onclick="closeModel('editDeviceModel')">&times;</span>
                    <h2>ข้อมูลอุปกรณ์</h2>
                    <form id="editDeviceForm">
                        <div class="form-group">
                            <input type="text" id="editDeviceId" name="device_id" readonly style="background-color: #f0f0f0;">
                        </div>
                        <div class="form-group">
                            <input type="text" id="editDeviceName" name="name" placeholder="ชื่ออุปกรณ์">
                        </div>
                        <div class="form-group">
                            <input type="text" id="editDeviceLocation" name="location" placeholder="สถานที่ติดตั้งอุปกรณ์">
                        </div>
                        <input type="hidden" id="editIdCard" name="id_card" value="<?php echo $userData['id_card']; ?>">
                        <div style="text-align: center;">
                            <button type="button" onclick="updateDevice()">บันทึก</button>
                            <button type="button" onclick="closeModel('editDeviceModel')" style="background-color: #6c757d;">ยกเลิก</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function toggleMenu() {
            var menu = document.getElementById("menuDropdown");
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        }

        function openModel(modelId) {
            document.getElementById(modelId).style.display = "block";
        }

        function closeModel(modelId) {
            document.getElementById(modelId).style.display = "none";
        }

        function addDevice() {
            var form = document.getElementById("deviceForm");
            var formData = new FormData(form);

            fetch('Index_db.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                closeModel('addDeviceModel');
                location.reload();
            })
            .catch(error => console.error('Error:', error));
        }

        function openEditModel(modelId, event, deviceId, location, name) {
            if (event.target.closest('.switch')) {
                return;
            }
            
            const formattedDeviceId = deviceId.toString().padStart(3, '0');
            
            document.getElementById(modelId).style.display = "block";
            document.getElementById('editDeviceId').value = formattedDeviceId;
            document.getElementById('editDeviceLocation').value = location;
            document.getElementById('editDeviceName').value = name || '';
        }

        function updateDevice() {
            var form = document.getElementById("editDeviceForm");
            var formData = new FormData(form);

            fetch('updateDevice.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModel('editDeviceModel');
                    location.reload();
                } else {
                    alert('ไม่สามารถอัพเดทข้อมูลได้');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการอัพเดทข้อมูล');
            });
        }

        function toggleStatus(event, deviceId) {
            fetch('toggleDeviceStatus.php?device_id=' + deviceId + '&id_card=' + <?php echo $_SESSION['user_id']; ?>)
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        event.target.checked = !event.target.checked;
                        console.error('Failed to toggle status');
                    }
                })
                .catch(error => {
                    event.target.checked = !event.target.checked;
                    console.error('Error:', error);
                });
        }
    </script>
</body>
</html>