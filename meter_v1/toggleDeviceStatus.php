<?php
$conn = mysqli_connect("localhost", "iticc_meter", "adminmeter", "iticc_meter");

if (isset($_GET['device_id'])) {
    $deviceId = $_GET['device_id'];
    $sql = "SELECT state FROM relay_control WHERE device_id = $deviceId";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);

    $newStatus = $row['state'] == '1' ? '0' : '1';
    $updateSql = "UPDATE relay_control SET state = $newStatus WHERE device_id = $deviceId";

    if (mysqli_query($conn, $updateSql)) {
        echo json_encode(['success' => true, 'newStatus' => $newStatus]);
    } else {
        echo json_encode(['success' => false]);
    }
}

mysqli_close($conn);
?>
