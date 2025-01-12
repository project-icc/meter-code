<?php
require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        die("Passwords do not match!");
    }

    try {
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW() LIMIT 1");
        $stmt->bindParam(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resetRequest) {
            $email = $resetRequest['email'];
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            echo "Password reset successful!";
        } else {
            echo "Invalid or expired token.";
        }
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
    }
} else if (isset($_GET['token'])) {
    $token = $_GET['token'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Password</title>
    </head>
    <body>
        <h2>Reset Password</h2>
        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div>
                <label for="password">New Password</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </body>
    </html>
    <?php
}
