<?php
session_start();
session_destroy();
header("Location: http://localhost/meter-app/login.php");
exit;
?>
