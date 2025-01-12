<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

require_once '../layouts/header.php';
require_once '../layouts/sidebar.php';
?>
<div class="content">
    <h1>Manage Devices</h1>
    <p>Here you can manage all devices.</p>
</div>
<?php require_once '../layouts/footer.php'; ?>
