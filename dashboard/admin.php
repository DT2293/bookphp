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
<body>
<div class="container-header">
    <div class="container d-flex justify-content-between align-items-center">
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <nav>
            <ul class="nav">
                <li class="nav-item">
                    <a href="" class="nav-link text-dark hover-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../dashboard/statistical/statistical.php" class="nav-link text-dark hover-link">Quản lý Thống kê</a>
                </li>
                <li class="nav-item">
                    <a href="../dashboard/user/showuser.php" class="nav-link text-dark hover-link">Quản lý người dùng</a>
                </li>
                <li class="nav-item">
                    <a href="../dashboard/book/showbook.php" class="nav-link text-dark hover-link">Quản lý Sách</a>
                </li>
            </ul>
        </nav>
        <a href="../logout.php" class="btn btn-danger btn-sm btn-logout">Đăng xuất</a>
    </div>
</div>
     
    </div>
 
</body>
</html>
