<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Băm mật khẩu
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role']; // Vai trò, admin hoặc customer

    // Kiểm tra xem email đã tồn tại trong cơ sở dữ liệu chưa
    $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingUser) {
        $error = "Email này đã được sử dụng.";
    } else {
        // Thêm người dùng vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO customers (FullName, Email, Password, Phone, Address, Role) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $password, $phone, $address, $role]);
        $success = "Người dùng đã được thêm thành công!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hover-link:hover {
            color: #6a11cb;
            text-decoration: underline;
            transition: color 0.3s ease, text-decoration 0.3s ease;
        }
        .container-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .nav-item {
            margin-right: 20px;
        }
        .btn-logout {
            margin-left: 20px;
        }
    </style>
</head>

<body>
<div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Phần chào mừng -->
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
         
        <style>
            .hover-link:hover {
                color: #6a11cb; /* Màu khi hover */
                text-decoration: underline; /* Gạch chân khi hover */
                transition: color 0.3s ease, text-decoration 0.3s ease; /* Hiệu ứng mượt */
            }

        </style>
        <!-- Các liên kết quản lý -->
        <div class="d-flex gap-4" >
            <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
            <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
            <a href="../user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../book/showbook.php" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
            <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản đơn hàng</a>
            <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản kho hàng</a>
        </div>

        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
        </div>
    </div>
</div>
<div class="container my-5">
        <h2>Thêm Người Dùng Mới</h2>

        <!-- Hiển thị thông báo thành công hoặc lỗi -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Form nhập thông tin người dùng mới -->
        <form action="" method="POST">
            <div class="mb-3">
                <label for="fullName" class="form-label">Tên đầy đủ</label>
                <input type="text" class="form-control" id="fullName" name="fullName" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Địa chỉ</label>
                <textarea class="form-control" id="address" name="address"></textarea>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="customer">Khách hàng</option>
                    <option value="admin">Quản trị viên</option>
                </select>
            </div>
            <a href="../user/showuser.php" class="btn btn-danger">Huỷ</a>
            <button type="submit" class="btn btn-primary">Thêm người dùng</button>
        </form>
    </div>
      
</body>

</html>
