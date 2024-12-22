<?php
include '../includes/header.php';
include '../includes/db.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Lấy CustomerID từ session
$customerId = $_SESSION['CustomerID'];

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Kiểm tra mật khẩu mới và xác nhận mật khẩu
    if ($newPassword !== $confirmPassword) {
        $error = "Mật khẩu mới và xác nhận mật khẩu không khớp!";
    } else {
        try {
            // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
            $stmt = $conn->prepare("SELECT Password FROM customers WHERE CustomerID = :customerId");
            $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($currentPassword, $user['Password'])) {
                // Mã hóa mật khẩu mới
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Cập nhật mật khẩu trong cơ sở dữ liệu
                $stmt = $conn->prepare("
                    UPDATE customers 
                    SET Password = :hashedPassword 
                    WHERE CustomerID = :customerId
                ");
                $stmt->bindParam(':hashedPassword', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $success = "Mật khẩu đã được cập nhật thành công!";
                } else {
                    $error = "Đã xảy ra lỗi khi cập nhật mật khẩu!";
                }
            } else {
                $error = "Mật khẩu hiện tại không đúng!";
            }
        } catch (PDOException $e) {
            $error = "Lỗi hệ thống: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="../styles/style.css"> <!-- Đường dẫn CSS -->
</head>
<body>
    <div class="container">
        <h2>Đổi Mật Khẩu</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Mật khẩu mới</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
        </form>
    </div>
</body>
</html>
