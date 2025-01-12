<?php
require_once __DIR__ . '/config/database.php';

// Create database instance and get connection
$database = new Database();
$pdo = $database->getConnection();

if ($pdo) {
    echo "Database connection successful!";
} else {
    echo "Database connection failed!";
}
?>
