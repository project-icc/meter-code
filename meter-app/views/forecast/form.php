<?php
require_once '../layouts/header.php';
?>
<div class="content">
    <h1>Forecast Form</h1>
    <form action="result.php" method="POST">
        <label for="date">Select Date:</label>
        <input type="date" id="date" name="date" required>
        <button type="submit">Submit</button>
    </form>
</div>
<?php require_once '../layouts/footer.php'; ?>
