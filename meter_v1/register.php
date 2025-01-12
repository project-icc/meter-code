<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_card = $_POST['id_card'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT id_card FROM regis WHERE id_card = ?");
    $check_stmt->bind_param("s", $id_card);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว";
    } else {
        $stmt = $conn->prepare("INSERT INTO regis (id_card, username, password) VALUES (?, ?, ?)");
        if ($stmt === false) {
            die("เตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
        }

        $stmt->bind_param("sss", $id_card, $username, $password);

        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            header("Location: login.php");
            exit();
        } else {
            $error = "เกิดข้อผิดพลาด: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
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

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #666;
            font-size: 0.95em;
        }

        .login-link a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
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
        <h2>สมัครสมาชิก</h2>
        <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
        <form method="POST" action="register.php">
            <div class="input-group">
                <input type="text" name="username" required placeholder="ชื่อผู้ใช้">
            </div>
            <div class="input-group">
                <input type="text" name="id_card" required placeholder="รหัสบัตรประชาชน">
            </div>
            <div class="input-group">
                <input type="password" name="password" required placeholder="รหัสผ่าน">
            </div>
            <button type="submit">สมัครสมาชิก</button>
        </form>
        <p class="login-link">มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
    </div>
</body>
</html>