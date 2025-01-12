<?php
session_start();
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/register.css">
</head>

<body>
    <div class="register-wrap">
        <h2>Register</h2>
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color: green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>
        <form action="auth.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="group">
                <label for="username" class="label">Username</label>
                <input id="username" name="username" type="text" class="input" required>
            </div>
            <div class="group">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" class="input" required>
            </div>
            <div class="group">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" class="input" required>
            </div>
            <div class="group">
                <label for="confirm_password" class="label">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" class="input" required>
            </div>
            <div class="group">
                <input type="submit" value="Sign Up" class="button">
            </div>
        </form>


    </div>
</body>

</html>