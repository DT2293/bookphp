<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact_info = $_POST['contact_info'];

    // Thêm nhà cung cấp mới
    $query = "INSERT INTO suppliers (Name, Address, ContactInfo) VALUES (:name, :address, :contact_info)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':address', $address, PDO::PARAM_STR);
    $stmt->bindParam(':contact_info', $contact_info, PDO::PARAM_STR);
    $stmt->execute();

    // Chuyển hướng về trang quản lý nhà cung cấp
    header("Location: stock.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhà Cung Cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
<header>
 <div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <div class="d-flex gap-4">
        <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
                <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
                <a href="../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
                <a href="../book/showbook.php   " class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
                <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản đơn hàng</a>
                <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản Kho hàng</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
</header>      
<div class="container mt-5">
    <h2 class="mb-4">Thêm Nhà Cung Cấp</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Tên Nhà Cung Cấp</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa Chỉ</label>
            <textarea name="address" id="address" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="contact_info" class="form-label">Thông Tin Liên Hệ</label>
            <input type="text" name="contact_info" id="contact_info" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Thêm Nhà Cung Cấp</button>
        <a href="manage_suppliers.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
