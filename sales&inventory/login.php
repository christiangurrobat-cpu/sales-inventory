<?php
require_once 'config/auth.php';
redirect_if_authenticated();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fast Food POS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
        }
        .login-card h1 {
            margin-top: 0;
            color: #0f172a;
            text-align: center;
        }
        .login-card label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #334155;
        }
        .login-card input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #cbd5f5;
            margin-bottom: 16px;
            font-size: 16px;
        }
        .login-card button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }
        .login-card button:hover {
            background: #1d4ed8;
        }
        .login-message {
            margin-bottom: 16px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }
        .login-message.error {
            background: #fee2e2;
            color: #b91c1c;
        }
        .login-message.success {
            background: #dcfce7;
            color: #15803d;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Welcome Back</h1>
        <p style="text-align: center; color: #64748b; margin-top: -10px; margin-bottom: 20px;">
            Enter your credentials to access the system.
        </p>
        <?php if ($error): ?>
            <div class="login-message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="login-message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form action="auth/login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autofocus>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Sign In</button>
        </form>
    </div>
</body>
</html>

