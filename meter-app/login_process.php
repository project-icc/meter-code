<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e2ecf8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .login-container img {
            width: 50px;
            margin-bottom: 20px;
        }

        .login-container h2 {
            margin-bottom: 10px;
            font-size: 1.5rem;
            color: #333;
        }

        .login-container p {
            color: #777;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .forgot-password,
        .signup-link {
            font-size: 0.9rem;
        }

        .forgot-password a,
        .signup-link a {
            color: #007bff;
            text-decoration: none;
        }

        .forgot-password a:hover,
        .signup-link a:hover {
            text-decoration: underline;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="logo-placeholder.png" alt="Logo">
        <h2>Spike Admin</h2>
        <p>Your Social Campaigns</p>
        <form action="login_process.php" method="POST">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" class="form-check-label">Remember this Device</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
        <div class="signup-link mt-3">
            New to Spike? <a href="register.php">Create an account</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
