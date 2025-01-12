<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_card = $_POST['id_card'];
    
    $stmt = $conn->prepare("SELECT id_card, username FROM regis WHERE id_card = ?");
    $stmt->bind_param("s", $id_card);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        if (isset($_POST['new_password'])) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE regis SET password = ? WHERE id_card = ?");
            $update_stmt->bind_param("ss", $new_password, $id_card);
            
            if ($update_stmt->execute()) {
                $success = "รหัสผ่านถูกเปลี่ยนเรียบร้อยแล้ว";
                header("Refresh: 2; url=login.php");
            } else {
                $error = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน";
            }
        } else {
            $_SESSION['reset_id_card'] = $id_card;
            $show_reset_form = true;
        }
    } else {
        $error = "ไม่พบบัญชีผู้ใช้นี้ในระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
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
        }

        h2 {
            color: #2c3e50;
            font-size: 2em;
            text-align: center;
            margin-bottom: 30px;
        }

        .success {
            background-color: #e5ffe5;
            color: #33aa33;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.95em;
            border: 1px solid #ccffcc;
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

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1em;
            margin-bottom: 15px;
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
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #2c3e50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ลืมรหัสผ่าน</h2>
        <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
        <?php if (isset($success)) { echo "<div class='success'>$success</div>"; } ?>
        
        <?php if (!isset($show_reset_form)): ?>
        <form method="POST" action="forgot_password.php">
            <input type="text" name="id_card" required placeholder="กรุณากรอกรหัสบัตรประชาชน" pattern="[0-9]{13}" title="กรุณากรอกเลขบัตรประชาชน 13 หลัก">
            <button type="submit">ตรวจสอบ</button>
        </form>
        <?php else: ?>
        <form method="POST" action="forgot_password.php">
            <input type="hidden" name="id_card" value="<?php echo htmlspecialchars($_SESSION['reset_id_card']); ?>">
            <input type="password" name="new_password" required placeholder="รหัสผ่านใหม่" minlength="6">
            <button type="submit">เปลี่ยนรหัสผ่าน</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php">กลับไปหน้าเข้าสู่ระบบ</a>
        </div>
    </div>
</body>
</html>