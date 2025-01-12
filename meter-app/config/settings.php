<?php
$servername = "localhost";
$username = "iticc_meter";
$password = "adminmeter";
$dbname = "iticc_meter";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Fail to Connect: " . $conn->connect_error);
}
$device_id = $_POST['device_id'];
$voltage = $_POST['voltage'];
$current = $_POST['current'];
$power = $_POST['power'];
$energy = $_POST['energy'];
$frequency = $_POST['frequency'];
$power_factor = $_POST['power_factor'];
$sql = "INSERT INTO PZEM_data (device_id, voltage, current, power, energy, frequency, power_factor)
        VALUES ('$device_id', '$voltage', '$current', '$power', '$energy', '$frequency', '$power_factor')";
if ($conn->query($sql) === TRUE) {
    echo "Success!!";
} else {
    echo "Fail: " . $sql . "<br>" . $conn->error;
}
$conn->close();
?>
<?php
$servername = "localhost";
$username = "iticc_meter";
$password = "adminmeter";
$dbname = "iticc_meter";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$table = "relay_control";
$column = "state";
$deviceIdColumn = "device_id";
$deviceId = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';
if (empty($deviceId)) {
    die("Error: Device ID is required.");
}
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT $column FROM $table WHERE $deviceIdColumn='$deviceId'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row[$column];
    } else {
        echo "0";
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newState = $_POST['state'];
    $sql = "SELECT $deviceIdColumn FROM $table WHERE $deviceIdColumn='$deviceId'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $sql = "UPDATE $table SET $column='$newState' WHERE $deviceIdColumn='$deviceId'";
    } else {
        $sql = "INSERT INTO $table ($deviceIdColumn, $column) VALUES ('$deviceId', '$newState')";
    }
    if ($conn->query($sql) === TRUE) {
        echo $newState;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>