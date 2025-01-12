<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_card = $_POST['id_card'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_card, username, password FROM regis WHERE id_card = ?");
    $stmt->bind_param("s", $id_card);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id_card'];  
        $_SESSION['user'] = $user['username'];
        $_SESSION['id_card'] = $user['id_card'];
        header("Location: index.php");
        exit();
    } else {
        $error = "รหัสบัตรประชาชนหรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 450px;
            backdrop-filter: blur(10px);
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        h2 {
            color: #2c3e50;
            font-size: 2em;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, #84fab0, #8fd3f4);
            border-radius: 2px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.95em;
            transition: all 0.3s ease;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1em;
            color: #2c3e50;
            transition: all 0.3s ease;
            background: white;
        }

        input:focus {
            border-color: #84fab0;
            box-shadow: 0 0 0 4px rgba(132, 250, 176, 0.1);
            outline: none;
        }

        button {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(132, 250, 176, 0.3);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            background-color: #ffe5e5;
            color: #ff3333;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.95em;
            border: 1px solid #ffcccc;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 0.95em;
        }

        .register-link a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            color: #84fab0;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
                width: 100%;
            }

            h2 {
                font-size: 1.7em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>เข้าสู่ระบบ</h2>
        <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
        <form method="POST" action="login.php">
            <div class="input-group">
                <input type="text" name="id_card" required placeholder="รหัสบัตรประชาชน">
            </div>
            <div class="input-group">
                <input type="password" name="password" required placeholder="รหัสผ่าน">
            </div>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
        <p class="register-link">
            ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a> | 
            <a href="forgot_password.php">ลืมรหัสผ่าน</a>
        </p>
    </div>
</body>
</html>