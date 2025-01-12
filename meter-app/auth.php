<?php
session_start();
require_once __DIR__ . '/config/database.php';

$database = new Database();
$pdo = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: views/admin/dashboard.php");
                } else {
                    header("Location: views/user/dashboard.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Invalid email or password!";
                header("Location: index.php");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: index.php");
            exit;
        }
    } elseif ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match!";
            header("Location: index.php");
            exit;
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Email already exists!";
                header("Location: index.php");
                exit;
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (:name, :email, :password, 'user', NOW())");
            $stmt->bindParam(':name', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful! Please log in.";
                header("Location: index.php");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: index.php");
            exit;
        }
    } elseif ($action === 'forgot_password') {
        $email = trim($_POST['email']);

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :expires)");
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':token', $token, PDO::PARAM_STR);
                $stmt->bindParam(':expires', $expires, PDO::PARAM_STR);
                $stmt->execute();

                $resetLink = "http://yourdomain.com/reset_password.php?token=$token";
                $subject = "Password Reset Request";
                $message = "Click the link below to reset your password:\n\n$resetLink";
                $headers = "From: no-reply@yourdomain.com";

                mail($email, $subject, $message, $headers);

                $_SESSION['success'] = "Password reset link has been sent to your email.";
            } else {
                $_SESSION['error'] = "No account found with that email.";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }

        header("Location: index.php");
        exit;
    } else {
        $_SESSION['error'] = "Invalid action!";
        header("Location: index.php");
        exit;
    }
}
?>
