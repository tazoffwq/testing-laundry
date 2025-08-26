<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        header("Location: index.php"); // kembali ke login setelah daftar
        exit;
    } else {
        $error = "Gagal membuat akun!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Laundry App</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff22;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            width: 800px;
            max-width: 90%;
            display: flex;
            overflow: hidden;
        }
        .left, .right {
            padding: 40px;
            flex: 1;
        }
        .left {
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .left h2 {
            margin-bottom: 20px;
            color: #fff;
        }
        form input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #4e54c8;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn:hover {
            background: #3b41a1;
        }
        .link {
            margin-top: 15px;
            color: #eee;
            font-size: 14px;
        }
        .link a {
            color: #ffd369;
            text-decoration: none;
        }
        .right {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .right h3 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .right p {
            font-size: 16px;
            opacity: 0.9;
        }
        @media (max-width: 700px) {
            .container {
                flex-direction: column;
                width: 100%;
                min-width: unset;
                border-radius: 0;
                box-shadow: none;
            }
            .left, .right {
                padding: 24px;
            }
            .right {
                align-items: flex-start;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <h2>Daftar Akun</h2>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="btn">Daftar</button>
            </form>
            <p class="link">Sudah punya akun? <a href="index.php">Login di sini</a></p>
        </div>
        <div class="right">
            <h3>Fresh by Shaa</h3>
            <p>"Shaa Laundry, Bersih Tanpa Drama"</p>
        </div>
    </div>
</body>
</html>
