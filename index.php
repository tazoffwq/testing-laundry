<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard_admin.php");
    } else {
        header("Location: user/dashboard_user.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard_admin.php");
        } else {
            header("Location: user/dashboard_user.php");
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Laundry</title>
    <link rel="stylesheet" href="css\style.css">
</head>
<body>
    <div class="container">
        <div class="login-section">
            <div class="logo"></div>
            <h2>Log in</h2>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <input type="text" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your email or username" 
                           required>
                    <span class="input-icon">📧</span>
                </div>

                <div class="form-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password" 
                           required>
                    <span class="input-icon">🔒</span>
                </div>

                <button type="submit" class="login-btn">Log in</button>
            </form>
            
            <button class="google-btn" type="button">
                <div class="google-icon">G</div>
                Continue with Google
            </button>
            
            <a href="#" class="forgot-password">Forgot your password? Reset password</a>
            
            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar di sini</a>
            </div>
        </div>

        <div class="info-section">
            <div class="info-card">
                <div class="info-title">Fresh by Shaa</div>
                <div class="info-description">
                    "Shaa Laundry, Bersih Tanpa Drama"
                </div>
            </div>
            
            <div class="social-icons">
                <a href="#" class="social-icon">📘</a>
                <a href="#" class="social-icon">🐦</a>
                <a href="#" class="social-icon">📷</a>
                <a href="#" class="social-icon">💼</a>
            </div>
        </div>
    </div>

    <script src="js\script.js"></script>
</body>
</html>
