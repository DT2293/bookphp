<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

$search = $_GET['search'] ?? '';

// Lấy danh sách nhà cung cấp
$query = "SELECT * FROM suppliers";
if ($search) {
    $query .= " WHERE Name LIKE :search OR Address LIKE :search OR ContactInfo LIKE :search";
    $stmt = $conn->prepare($query);
    $stmt->execute(['search' => '%' . $search . '%']);
} else {
    $stmt = $conn->prepare($query);
    $stmt->execute();
}
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý xóa nhà cung cấp
if (isset($_GET['delete_id'])) {
    $supplier_id = intval($_GET['delete_id']);

    // Kiểm tra nhà cung cấp có tồn tại không
    $checkQuery = "SELECT * FROM suppliers WHERE SupplierID = :supplier_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute(['supplier_id' => $supplier_id]);

    if ($checkStmt->rowCount() > 0) {
        $query = "DELETE FROM suppliers WHERE SupplierID = :supplier_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: stock.php");
        exit;
    } else {
        echo "<script>alert('Nhà cung cấp không tồn tại!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà Cung Cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hover-link:hover {
            color: #6a11cb;
            text-decoration: underline;
            transition: color 0.3s ease, text-decoration 0.3s ease;
        }
        .input-group {
            max-width: 400px;
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .btn-sm {
            margin-right: 5px;
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
    </div>
</header>
<div class="container mt-5">
    <h2 class="mb-4">Quản lý Nhà Cung Cấp</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
    <!-- Thanh tìm kiếm -->
    <form method="GET" action="" class="d-flex flex-grow-3 me-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Tìm kiếm nhà cung cấp..." 
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button class="btn btn-primary" type="submit">Tìm kiếm</button>
        </div>
    </form>
    <!-- Nút Thêm Nhà Cung Cấp -->
    <a href="add_supplier.php" class="btn btn-success">Thêm Nhà Cung Cấp</a>
</div>

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
        <?php if (count($result) > 0): ?>
            <?php foreach ($result as $row): ?>
                <tr>
                    <td><?= $row['SupplierID'] ?></td>
                    <td><?= htmlspecialchars($row['Name']) ?></td>
                    <td><?= htmlspecialchars($row['Address']) ?></td>
                    <td><?= htmlspecialchars($row['ContactInfo']) ?></td>
                    <td>
                        <a href="edit_supplier.php?id=<?= $row['SupplierID'] ?>" class="btn btn-primary btn-sm">Sửa</a>
                        <a href="?delete_id=<?= $row['SupplierID'] ?>" class="btn btn-danger btn-sm" 
                           onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Không tìm thấy nhà cung cấp nào.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
