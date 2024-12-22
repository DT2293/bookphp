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
</head>
<body>
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
