<?php
    session_start();
    session_regenerate_id(true);

    if (!isset($_SESSION['id_card']) || empty($_SESSION['id_card'])) {
        header('Location: login.php');
        exit();
    }

    define('DB_HOST', 'localhost');
    define('DB_USER', 'iticc_meter');
    define('DB_PASS', 'adminmeter');
    define('DB_NAME', 'iticc_meter');

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    $userStmt = $pdo->prepare("SELECT id_card, username FROM regis WHERE id_card = ?");
    $userStmt->execute([$_SESSION['id_card']]);
    $userData = $userStmt->fetch();

    if (!$userData) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    $deviceStmt = $pdo->prepare("SELECT DISTINCT device_id FROM relay_control WHERE id_card = ?");
    $deviceStmt->execute([$_SESSION['id_card']]);
    $devices = $deviceStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($devices)) {
        $view_type = filter_input(INPUT_GET, 'view_type', FILTER_SANITIZE_STRING) ?? 'daily';
        $date = filter_input(INPUT_GET, 'date', FILTER_SANITIZE_STRING) ?? date('Y-m-d');
        $month = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING) ?? date('Y-m');
        $year = filter_input(INPUT_GET, 'year', FILTER_SANITIZE_STRING) ?? date('Y');
        
        // แก้ไขส่วนการจัดการ selected_device
        $selected_device = filter_input(INPUT_GET, 'device_id', FILTER_SANITIZE_STRING);
        if (empty($selected_device) && !empty($devices)) {
            $selected_device = str_pad($devices[0], 3, "0", STR_PAD_LEFT);
        } elseif (!empty($selected_device)) {
            $selected_device = str_pad($selected_device, 3, "0", STR_PAD_LEFT);
        }

        $params = [$selected_device];
        switch($view_type) {
            case 'realtime':
                $sql = "SELECT SUM(energy) as energy, 
                        DATE_FORMAT(Date, '%H:%i') as label
                        FROM PZEM_data 
                        WHERE Device_id = ? 
                        AND Date >= NOW() - INTERVAL 1 HOUR 
                        GROUP BY FLOOR(MINUTE(Date)/5)
                        ORDER BY Date ASC";
                break;
            case 'daily':
                $sql = "SELECT SUM(energy) as energy,
                        DATE_FORMAT(Date, '%H:%i') as label
                        FROM PZEM_data 
                        WHERE Device_id = ?
                        AND DATE(Date) = ?
                        GROUP BY HOUR(Date)
                        ORDER BY Date ASC";
                $params[] = $date;
                break;
            case 'monthly':
                $sql = "SELECT SUM(energy) as energy,
                        DATE_FORMAT(Date, '%d') as label
                        FROM PZEM_data 
                        WHERE Device_id = ?
                        AND DATE_FORMAT(Date, '%Y-%m') = ?
                        GROUP BY DATE(Date)
                        ORDER BY Date ASC";
                $params[] = $month;
                break;
            case 'yearly':
                $sql = "SELECT SUM(energy) as energy,
                        DATE_FORMAT(Date, '%M') as label
                        FROM PZEM_data 
                        WHERE Device_id = ?
                        AND YEAR(Date) = ?
                        GROUP BY MONTH(Date)
                        ORDER BY Date ASC";
                $params[] = $year;
                break;
            default:
                die("Invalid view type");
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $total_energy = array_sum(array_column($data, 'energy'));
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Total Forward Active Energy</title>
    <meta charset="utf-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="stylegrahp.css">
    <style>
        .image-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh;
            text-align: center;
        }
        .image-container img {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .image-container div {
            font-size: 1.2em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container-button">
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
    </div>

    <?php if (empty($devices)): ?>
        <div class="image-container">
            <img src="img/box.png" alt="Box Image">
            <div>ไม่มีอุปกรณ์</div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="total-energy">
                <strong>พลังงานไฟฟ้าสะสมรวม: </strong>
                <?php 
                $total = array_sum(array_column($data, 'energy'));
                echo number_format($total, 2); 
                ?> kWh
            </div>
            
            <div class="controls">
                <form id="viewForm" method="GET">
                    <!-- เพิ่ม hidden inputs -->
                    <input type="hidden" name="view_type" value="<?php echo htmlspecialchars($view_type); ?>">
                    <?php if($view_type == 'daily'): ?>
                        <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                    <?php endif; ?>
                    <?php if($view_type == 'monthly'): ?>
                        <input type="hidden" name="month" value="<?php echo htmlspecialchars($month); ?>">
                    <?php endif; ?>
                    <?php if($view_type == 'yearly'): ?>
                        <input type="hidden" name="year" value="<?php echo htmlspecialchars($year); ?>">
                    <?php endif; ?>

                    <select name="device_id" onchange="submitForm(this)">
                        <?php foreach($devices as $device): 
                            $padded_device = str_pad($device, 3, "0", STR_PAD_LEFT);
                        ?>
                            <option value="<?php echo $padded_device; ?>" 
                                    <?php echo $padded_device === $selected_device ? 'selected' : ''; ?>>
                                Device ID: <?php echo $padded_device; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="view_type" onchange="this.form.submit()">
                        <option value="daily" <?php echo $view_type == 'daily' ? 'selected' : ''; ?>>รายวัน</option>
                        <option value="monthly" <?php echo $view_type == 'monthly' ? 'selected' : ''; ?>>รายเดือน</option>
                        <option value="yearly" <?php echo $view_type == 'yearly' ? 'selected' : ''; ?>>รายปี</option>
                    </select>                
                    
                    <?php if($view_type == 'daily'): ?>
                        <input type="date" name="date" value="<?php echo $date; ?>" onchange="this.form.submit()">
                    <?php endif; ?>
                    
                    <?php if($view_type == 'monthly'): ?>
                        <input type="month" name="month" value="<?php echo $month; ?>" onchange="this.form.submit()">
                    <?php endif; ?>
                    
                    <?php if($view_type == 'yearly'): ?>
                        <input type="number" name="year" value="<?php echo $year; ?>" min="2000" max="2099" onchange="this.form.submit()">
                    <?php endif; ?>

                    <button type="button" onclick="exportToExcel()" class="export-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Export to Excel
                    </button>
                </form>
            </div>

            <div class="chart-container">
                <canvas id="energyChart"></canvas>
            </div>
        </div>

        <script>
             document.addEventListener('DOMContentLoaded', function() {
                const deviceSelect = document.querySelector('select[name="device_id"]');
                const lastSelected = localStorage.getItem('lastSelectedDevice');
                
                if (lastSelected) {
                    deviceSelect.value = lastSelected;
                }
                
                deviceSelect.addEventListener('change', function() {
                    localStorage.setItem('lastSelectedDevice', this.value);
                });
            });

            function submitForm(selectElement) {
                const form = selectElement.form;
                const selectedValue = selectElement.value;
                localStorage.setItem('lastSelectedDevice', selectedValue);
                form.submit();
            }

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
                })
                .catch(error => console.error('Error:', error));
            }

            window.onclick = function(event) {
                if (!event.target.matches('.menuButton')) {
                    var dropdown = document.getElementById("menuDropdown");
                    if (dropdown.style.display === "block") {
                        dropdown.style.display = "none";
                    }
                }
            }

            function exportToExcel() {
                const form = document.getElementById('viewForm');
                const device_id = form.device_id.value;
                const view_type = form.view_type.value;
                let params = `device_id=${device_id}&view_type=${view_type}`;
                
                if (view_type === 'daily') {
                    params += `&date=${form.date.value}`;
                } else if (view_type === 'monthly') {
                    params += `&month=${form.month.value}`;
                } else if (view_type === 'yearly') {
                    params += `&year=${form.year.value}`;
                }
                
                window.location.href = `export_excel.php?${params}`;
            }

            const chartData = <?php echo json_encode($data); ?>;
            
            new Chart(document.getElementById('energyChart'), {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.label),
                    datasets: [{
                        label: 'พลังงานไฟฟ้า (kWh)',
                        data: chartData.map(item => parseFloat(item.energy)),
                        backgroundColor: 'rgba(41, 95, 152, 0.5)',
                        borderColor: '#295F98',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'กราฟแสดงพลังงานไฟฟ้าสะสม - Device ID: ' + <?php echo json_encode($selected_device); ?>,
                            color: '#295F98'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'พลังงานไฟฟ้า (kWh)',
                                color: '#295F98'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: <?php 
                                    switch($view_type) {
                                        case 'realtime':
                                            echo '"เวลา (นาที)"';
                                            break;
                                        case 'daily':
                                            echo '"เวลา (ชั่วโมง)"';
                                            break;
                                        case 'monthly':
                                            echo '"วันที่"';
                                            break;
                                        case 'yearly':
                                            echo '"เดือน"';
                                            break;
                                    }
                                ?>,
                                color: '#295F98'
                            }
                        }
                    }
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>