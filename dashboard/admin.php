<?php
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

include '../includes/db.php'; // Kết nối cơ sở dữ liệu

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
    <title>Thêm Người Dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="d-flex gap-3" >
            <a href="" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
            <a href="../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../dashboard/book/showbook.php" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
        </div>

        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../logout.php">Đăng xuất</a>
    </div>
</div>
     
    </div>
 
</body>
</html>
