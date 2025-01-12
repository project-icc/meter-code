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
    <title>Login & Register</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <div class="login-wrap">
        <div class="login-html">
            <!-- Radio Tabs -->
            <input id="tab-1" type="radio" name="tab" class="sign-in" checked>
            <label for="tab-1" class="tab">Sign In</label>
            <input id="tab-2" type="radio" name="tab" class="sign-up">
            <label for="tab-2" class="tab">Sign Up</label>

            <div class="login-form">
                <!-- Sign In Form -->
                <div class="sign-in-htm">
                    <?php if ($error): ?>
                        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                    <form action="auth.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="group">
                            <label for="email-login" class="label">Email</label>
                            <input id="email-login" name="email" type="email" class="input" required>
                        </div>
                        <div class="group">
                            <label for="password-login" class="label">Password</label>
                            <input id="password-login" name="password" type="password" class="input" required>
                        </div>
                        <div class="group">
                            <input type="submit" class="button" value="Sign In">
                        </div>
                        <div class="hr"></div>
                        <div class="foot-lnk">
                            <a href="#forgot">Forgot Password?</a>
                        </div>
                    </form>
                </div>

                <!-- Sign Up Form -->
                <div class="sign-up-htm">
                    <?php if ($success): ?>
                        <p style="color: green;"><?= htmlspecialchars($success) ?></p>
                    <?php endif; ?>
                    <form action="auth.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="group">
                            <label for="username-register" class="label">Username</label>
                            <input id="username-register" name="username" type="text" class="input" required>
                        </div>
                        <div class="group">
                            <label for="email-register" class="label">Email</label>
                            <input id="email-register" name="email" type="email" class="input" required>
                        </div>
                        <div class="group">
                            <label for="password-register" class="label">Password</label>
                            <input id="password-register" name="password" type="password" class="input" required>
                        </div>
                        <div class="group">
                            <label for="confirm-password-register" class="label">Confirm Password</label>
                            <input id="confirm-password-register" name="confirm_password" type="password" class="input" required>
                        </div>
                        <div class="group">
                            <input type="submit" class="button" value="Sign Up">
                        </div>
                        <div class="hr"></div>
                        <div class="foot-lnk">
                            <label for="tab-1">Already Member?</label>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>