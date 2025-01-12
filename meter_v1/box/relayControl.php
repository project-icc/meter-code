<?php
// Database connection settings
$servername = "localhost";
$username = "iticc_meter";
$password = "adminmeter";
$dbname = "iticc_meter";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Table and column names
$table = "relay_control";    // Name of the table where relaay state is stored
$column = "state";           // Name of the column that stores the relay state
$deviceIdColumn = "device_id"; // Name of the column that stores the device ID

// Get the device ID from the request
$deviceId = isset($_REQUEST['device_id']) ? $_REQUEST['device_id'] : '';

if (empty($deviceId)) {
    die("Error: Device ID is required.");
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Handle GET request - retrieve the current relay state for the specific device
    $sql = "SELECT $column FROM $table WHERE $deviceIdColumn='$deviceId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row[$column];
    } else {
        echo "0"; // Default to 0 if no data is found
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle POST request - update the relay state for the specific device
    $newState = $_POST['state'];

    // Check if the device ID already exists in the database
    $sql = "SELECT $deviceIdColumn FROM $table WHERE $deviceIdColumn='$deviceId'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Device exists, update the relay state
        $sql = "UPDATE $table SET $column='$newState' WHERE $deviceIdColumn='$deviceId'";
    } else {
        // Device does not exist, insert a new record
        $sql = "INSERT INTO $table ($deviceIdColumn, $column) VALUES ('$deviceId', '$newState')";
    }

    if ($conn->query($sql) === TRUE) {
        echo $newState; // Return the new state as confirmation
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>
