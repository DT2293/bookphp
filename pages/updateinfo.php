<?php
include '../includes/header.php';
include '../includes/db.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Lấy thông tin người dùng từ cơ sở dữ liệu bằng CustomerID
$customerId = $_SESSION['CustomerID'];
$stmt = $conn->prepare("SELECT * FROM customers WHERE CustomerID = :customerId");
$stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Xử lý khi form được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['FullName']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);

    // Kiểm tra email có trùng với email của người dùng khác không
    $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $existingEmail = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingEmail) {
        // Nếu email đã tồn tại trong cơ sở dữ liệu, hiển thị lỗi
        $error = "Email này đã được sử dụng bởi tài khoản khác!";
    } else {
        // Kiểm tra dữ liệu hợp lệ
        if (empty($fullname) || empty($email) || empty($phone) || empty($address)) {
            $error = "Vui lòng điền đầy đủ thông tin!";
        } else {
            try {
                // Cập nhật thông tin người dùng trong cơ sở dữ liệu
                $stmt = $conn->prepare("
                    UPDATE customers 
                    SET FullName = :fullname, Email = :email, Phone = :phone, Address = :address
                    WHERE CustomerID = :customerId
                ");
                $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
                $stmt->bindParam(':address', $address, PDO::PARAM_STR);
                $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    // Cập nhật lại thông tin trong session
                    $_SESSION['user']['FullName'] = $fullname;
                    $_SESSION['user']['Email'] = $email;
                    $_SESSION['user']['Phone'] = $phone;
                    $_SESSION['user']['Address'] = $address;

                    $success = "Cập nhật thông tin thành công!";
                } else {
                    $error = "Đã xảy ra lỗi khi cập nhật thông tin!";
                }
            } catch (PDOException $e) {
                $error = "Lỗi hệ thống: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Cập nhật thông tin cá nhân</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
   

    <form method="POST" action="updateinfo.php">
    <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="Email" 
           value="<?= htmlspecialchars($user['Email']) ?>" readonly required>
</div>
        <div class="mb-3">
            <label for="fullname" class="form-label">Họ và tên</label>
            <input type="text" class="form-control" id="fullname" name="FullName" 
                   value="<?= htmlspecialchars($user['FullName']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="Phone" 
                   value="<?= htmlspecialchars($user['Phone']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <textarea class="form-control" id="address" name="Address" rows="3" required><?= htmlspecialchars($user['Address']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="../pages/index.php" class="btn btn-secondary">Quay lại trang cá nhân</a>
        <a href="../pages/update_password.php" class="btn btn-secondary">Thay đổi mật khẩu</a>
    </form>
</div>
<!-- Optional JavaScript and Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
