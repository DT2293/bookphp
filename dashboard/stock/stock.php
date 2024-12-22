<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Xử lý xóa nhà cung cấp
if (isset($_GET['delete_id'])) {
    $supplier_id = intval($_GET['delete_id']);
    $query = "DELETE FROM suppliers WHERE SupplierID = :supplier_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: stock.php");
    exit;
}

// Lấy danh sách nhà cung cấp
$query = "SELECT * FROM suppliers";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà Cung Cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Quản lý Nhà Cung Cấp</h2>
    <a href="add_supplier.php" class="btn btn-success mb-3">Thêm Nhà Cung Cấp</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên Nhà Cung Cấp</th>
                <th>Địa Chỉ</th>
                <th>Thông Tin Liên Hệ</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?= $row['SupplierID'] ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Address']) ?></td>
                    <td><?= htmlspecialchars($row['ContactInfo']) ?></td>
                    <td>
                        <a href="edit_supplier.php?id=<?= $row['SupplierID'] ?>" class="btn btn-primary btn-sm">Sửa</a>
                        <a href="?delete_id=<?= $row['SupplierID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                        <a href="order_supplier.php?id=<?= $row['SupplierID'] ?>" class="btn btn-primary btn-sm">Đặt hàng</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
