<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #e2ecf8;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: #ffffff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 30px;
            max-width: 400px;
            text-align: center;
        }

        .login-container img {
            width: 50px;
            margin-bottom: 20px;
        }

        .login-container h2 {
            margin: 10px 0;
            font-size: 1.5rem;
            color: #333;
        }

        .login-container p {
            color: #777;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-actions {
            margin: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-actions div {
            display: flex;
            align-items: center;
        }

        .form-actions input {
            margin-right: 5px;
        }

        .form-actions a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn {
            background: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .btn:hover {
            background: #0056b3;
        }

        .signup-link {
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .signup-link a {
            color: #007bff;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="logo-placeholder.png" alt="Logo">
        <h2>Spike Admin</h2>
        <p>Your Social Campaigns</p>
        <form>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" placeholder="Enter your password">
            </div>
            <div class="form-actions">
                <div>
                    <input type="checkbox" id="remember">
                    <label for="remember">Remember this Device</label>
                </div>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
        <div class="signup-link">
            New to Spike? <a href="#">Create an account</a>
        </div>
    </div>
</body>

</html>
