

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 26px;
            color: #444;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #6a11cb;
            box-shadow: 0px 0px 8px rgba(106, 17, 203, 0.6);
        }

        button {
            background: #6a11cb;
            border: none;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        button:hover {
            background: #2575fc;
            box-shadow: 0px 8px 15px rgba(37, 117, 252, 0.4);
        }

        p {
            margin-top: 10px;
            font-size: 14px;
        }

        p a {
            color: #6a11cb;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        p a:hover {
            color: #2575fc;
        }
    </style>
    <?php
    $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
    ?>
    <script>
        const errorMessage = "<?php echo $error; ?>";
        if (errorMessage) {
            alert(errorMessage);
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h2>Đăng ký tài khoản</h2>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="fullname">Họ và tên:</label>
                <input type="text" name="fullname" id="fullname" placeholder="Nhập họ và tên" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" placeholder="Nhập email của bạn" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
            </div>
            <div class="form-group">
                <label for="phone">Số điện thoại:</label>
                <input type="text" name="phone" id="phone" placeholder="Nhập số điện thoại (không bắt buộc)">
            </div>
            <div class="form-group">
                <label for="address">Địa chỉ:</label>
                <input type="text" name="address" id="address" placeholder="Nhập địa chỉ của bạn (không bắt buộc)">
            </div>
            <button type="submit">Đăng ký</button>
            <p>Đã có tài khoản? <a href="../auth/login_page.php">Đăng nhập</a></p>
        </form>
    </div>
</body>
</html>
