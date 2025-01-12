<?php
// ข้อมูลเดือน
$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// ข้อมูลการใช้พลังงานปัจจุบัน (ตัวอย่าง)
$currentUsage = [110, 140, 160, 190, 200, 185, 210, 240, 225, 190, 170, 150];

// ข้อมูลการพยากรณ์พลังงาน (ตัวอย่าง)
$forecastedUsage = [120, 150, 170, 200, 210, 190, 220, 250, 230, 200, 180, 160];

// แปลงข้อมูลเป็น JSON
$monthsJson = json_encode($months);
$currentUsageJson = json_encode($currentUsage);
$forecastedUsageJson = json_encode($forecastedUsage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energy Consumption Forecast</title>
    <link rel="stylesheet" href="styleprophec.css"> <!-- ลิงก์ไปยัง CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- ลิงก์ไปยัง Chart.js -->
</head>
<body>
    <div class="container">
        <!-- <h1>Energy Consumption Forecast</h1> -->
        <h1>การพยากรณ์</h1>
        <canvas id="energyChart" width="800" height="400"></canvas> <!-- พื้นที่กราฟ -->
    </div>

    <script>
    // ข้อมูลสำหรับกราฟ
    const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    
    // ข้อมูลการใช้พลังงานปัจจุบัน (ตัวอย่าง)
    const currentUsage = [110, 140, 160, 190, 200, 185, 210, 240, 225, 190, 170, 150];
    
    // ข้อมูลการพยากรณ์พลังงาน (ตัวอย่าง)
    const forecastedUsage = [120, 150, 170, 200, 210, 190, 220, 250, 230, 200, 180, 160];

    // สร้างกราฟด้วย Chart.js
    const ctx = document.getElementById('energyChart').getContext('2d');
    const energyChart = new Chart(ctx, {
        type: 'line', // ประเภทของกราฟ: line
        data: {
            labels: months, // ข้อมูลแกน X: เดือน
            datasets: [
                {
                    label: 'การใช้พลังงานปัจจุบัน (kWh)', // ป้ายชื่อข้อมูลปัจจุบัน
                    data: currentUsage, // ข้อมูลการใช้พลังงานปัจจุบัน
                    borderColor: 'rgba(255, 99, 132, 1)', // สีเส้น
                    backgroundColor: 'rgba(255, 99, 132, 0.2)', // สีพื้นที่ใต้เส้น
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'การใช้พลังงานที่คาดการณ์ (kWh)', // ป้ายชื่อข้อมูลพยากรณ์
                    data: forecastedUsage, // ข้อมูลการพยากรณ์
                    borderColor: 'rgba(54, 162, 235, 1)', // สีเส้น
                    backgroundColor: 'rgba(54, 162, 235, 0.2)', // สีพื้นที่ใต้เส้น
                    borderWidth: 2,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'เดือน'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'การใช้พลังงาน (kWh)'
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
