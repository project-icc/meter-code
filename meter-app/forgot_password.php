<?php
$host = 'localhost';
$db = 'meter_app';
$user = 'root'; // ตั้งค่าผู้ใช้ฐานข้อมูลของคุณ
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        $resetLink = "http://yourdomain.com/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the link to reset your password: $resetLink";
        $headers = "From: no-reply@yourdomain.com";

        mail($email, $subject, $message, $headers);
        echo "Password reset link has been sent to your email.";
    } else {
        echo "No user found with this email.";
    }
}
?>
