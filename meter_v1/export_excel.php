<?php
    session_start();
    if (!isset($_SESSION['id_card']) || empty($_SESSION['id_card'])) {
        die("Unauthorized access");
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
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    $valid_view_types = ['daily', 'monthly', 'yearly'];
    $view_type = filter_input(INPUT_GET, 'view_type', FILTER_SANITIZE_STRING);
    $view_type = in_array($view_type, $valid_view_types) ? $view_type : 'daily';

    $date = filter_input(INPUT_GET, 'date');
    $month = filter_input(INPUT_GET, 'month');
    $year = filter_input(INPUT_GET, 'year');
    if (empty($date)) {
        $date = date('Y-m-d');
    }
    if (empty($month)) {
        $month = date('Y-m');
    }
    if (empty($year)) {
        $year = date('Y');
    }

    $device_id = filter_input(INPUT_GET, 'device_id', FILTER_SANITIZE_STRING);

    if (empty($device_id)) {
        die("Device ID is required");
    }

    try {
        $stmt = $pdo->prepare("SELECT Name FROM relay_control WHERE device_id = ?");
        $stmt->execute([$device_id]);
        $device_name = $stmt->fetchColumn() ?: 'Unknown Device';
    } catch (PDOException $e) {
        $device_name = 'Unknown Device';
    }

    $params = [$device_id];
    switch($view_type) {
        case 'daily':
            $sql = "SELECT SUM(energy) as energy,
                    DATE_FORMAT(Date, '%H:%i') as time_period,
                    AVG(voltage) as voltage,
                    AVG(current) as current,
                    AVG(power) as power,
                    AVG(frequency) as frequency
                    FROM PZEM_data 
                    WHERE Device_id = ? 
                    AND DATE(Date) = ? 
                    GROUP BY HOUR(Date)
                    ORDER BY Date ASC";
            $params[] = $date;
            $filename = "energy_data_daily_{$date}";
            $title = "รายงานการใช้พลังงานรายวัน";
            $subtitle = "วันที่: {$date}";
            $headers = ["เวลา", "Energy (kWh)", "Voltage (V)", "Current (A)", "Power (W)", "Frequency (Hz)"];
            break;

        case 'monthly':
            $sql = "SELECT SUM(energy) as energy,
                    DATE_FORMAT(Date, '%d') as time_period,
                    AVG(voltage) as voltage,
                    AVG(current) as current,
                    AVG(power) as power,
                    AVG(frequency) as frequency
                    FROM PZEM_data 
                    WHERE Device_id = ? 
                    AND DATE_FORMAT(Date, '%Y-%m') = ? 
                    GROUP BY DATE(Date)
                    ORDER BY Date ASC";
            $params[] = $month;
            $filename = "energy_data_monthly_{$month}";
            $title = "รายงานการใช้พลังงานรายเดือน";
            $subtitle = "เดือน: {$month}";
            $headers = ["วันที่", "Energy (kWh)", "Voltage (V)", "Current (A)", "Power (W)", "Frequency (Hz)"];
            break;

        case 'yearly':
            $sql = "SELECT SUM(energy) as energy,
                    DATE_FORMAT(Date, '%M') as time_period,
                    AVG(voltage) as voltage,
                    AVG(current) as current,
                    AVG(power) as power,
                    AVG(frequency) as frequency
                    FROM PZEM_data 
                    WHERE Device_id = ? 
                    AND YEAR(Date) = ? 
                    GROUP BY MONTH(Date)
                    ORDER BY Date ASC";
            $params[] = $year;
            $filename = "energy_data_yearly_{$year}";
            $title = "รายงานการใช้พลังงานรายปี";
            $subtitle = "ปี: {$year}";
            $headers = ["เดือน", "Energy (kWh)", "Voltage (V)", "Current (A)", "Power (W)", "Frequency (Hz)"];
            break;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching data: " . $e->getMessage());
    }

    $total_energy = 0;
    $avg_voltage = 0;
    $avg_current = 0;
    $avg_power = 0;
    $avg_frequency = 0;
    $count = count($data);

    foreach ($data as $row) {
        $total_energy += $row['energy'];
        $avg_voltage += $row['voltage'];
        $avg_current += $row['current'];
        $avg_power += $row['power'];
        $avg_frequency += $row['frequency'];
    }

    if ($count > 0) {
        $avg_voltage /= $count;
        $avg_current /= $count;
        $avg_power /= $count;
        $avg_frequency /= $count;
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');

    date_default_timezone_set('Asia/Bangkok');

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        body {
            font-family: 'KodchiangUPC', 'Angsana New', 'TH Sarabun New', sans-serif;
            margin: 40px;
            color: #2d3436;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0984e3;
            padding-bottom: 20px;
        }
        h2 {
            color: #0984e3;
            font-size: 32pt;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .info {
            margin: 20px 0;
            color: #2d3436;
            font-size: 16pt;
            line-height: 1.3;
        }
        .info p {
            margin: 5px 0;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 16pt;
        }
        .main-table th {
            background-color: #0984e3;
            color: white;
            padding: 12px;
            text-align: center;
            border: 1px solid #0984e3;
            font-weight: bold;
        }
        .main-table td {
            padding: 10px;
            border: 1px solid #dfe6e9;
            text-align: center;
        }
        .main-table tr:nth-child(even) {
            background-color: #f5f6fa;
        }
        .main-table tr:hover {
            background-color: #dfe6e9;
        }
        .summary {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .summary h3 {
            color: #0984e3;
            font-size: 24pt;
            margin-bottom: 20px;
            border-bottom: 1px solid #0984e3;
            padding-bottom: 5px;
            font-weight: bold;
            text-align: left !important;
        }
        .summary-table {
            width: 70%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 16pt;
        }
        .summary-table td {
            padding: 10px 15px;
            border: 1px solid #dfe6e9;
            text-align: left !important;
        }
        .summary-table td:first-child {
            background-color: #f5f6fa;
            font-weight: bold;
            width: 50%;
            text-align: left !important;
            padding-left: 20px !important;
        }
        .summary-table td:last-child {
            text-align: right !important;
            padding-right: 20px !important;
        }
        @media print {
            body {
                margin: 20px;
            }
            .header, .main-table, .summary-table {
                page-break-inside: avoid;
            }
            .main-table th {
                background-color: #0984e3 !important;
                color: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo $title; ?></h2>
        <div class="info">
            <p>วันที่จัดทำรายงาน: <?php echo $subtitle; ?></p>
            <p>รหัสอุปกรณ์: <?php echo htmlspecialchars($device_id); ?></p>
            <p>ชื่ออุปกรณ์: <?php echo htmlspecialchars($device_name); ?></p>
            <p>เวลาที่ออกรายงาน: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>

    <table class="main-table">
        <tr>
            <?php foreach ($headers as $header): ?>
                <th><?php echo htmlspecialchars($header); ?></th>
            <?php endforeach; ?>
        </tr>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['time_period']); ?></td>
                <td><?php echo number_format($row['energy'], 3); ?></td>
                <td><?php echo number_format($row['voltage'], 1); ?></td>
                <td><?php echo number_format($row['current'], 2); ?></td>
                <td><?php echo number_format($row['power'], 1); ?></td>
                <td><?php echo number_format($row['frequency'], 1); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="summary">
        <h3>สรุปข้อมูลการใช้พลังงาน</h3>
        <table class="summary-table">
            <tr>
                <td>พลังงานไฟฟ้าสะสมทั้งหมด</td>
                <td><?php echo number_format($total_energy, 3); ?> kWh</td>
            </tr>
            <tr>
                <td>ค่าเฉลี่ยของ Voltage</td>
                <td><?php echo number_format($avg_voltage, 1); ?> V</td>
            </tr>
            <tr>
                <td>ค่าเฉลี่ยของ Current</td>
                <td><?php echo number_format($avg_current, 2); ?> A</td>
            </tr>
            <tr>
                <td>ค่าเฉลี่ยของ Power</td>
                <td><?php echo number_format($avg_power, 1); ?> W</td>
            </tr>
            <tr>
                <td>ค่าเฉลี่ยของ Frequency</td>
                <td><?php echo number_format($avg_frequency, 2); ?> Hz</td>
            </tr>
        </table>
    </div>
</body>
</html>