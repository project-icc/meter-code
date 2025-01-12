<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "iticc_meter";
    $password = "adminmeter"; 
    $dbname = "iticc_meter";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $ID_custumer = $_POST['id_card'];
    $Name = $_POST['Name'];
    $Location = $_POST['Location'];
    $state = 0;

    $sql = "INSERT INTO relay_control (id_card,Name, Location, state) VALUES ('$ID_custumer','$Name', '$Location', $state)";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
