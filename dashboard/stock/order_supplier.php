<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $books = $_POST['books'];
    $quantities = $_POST['quantities'];

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Thêm giao dịch nhập kho vào bảng `stock_transactions`
        $query = "INSERT INTO stock_transactions (BookID, TransactionType, Quantity, SupplierID, Remarks, PricePerUnit) 
                  VALUES (:book_id, 'Import', :quantity, :supplier_id, :remarks, :price_per_unit)";
        $stmt = $conn->prepare($query);

        foreach ($books as $index => $book_id) {
            $quantity = $quantities[$index];
            $remarks = "Import from supplier ID $supplier_id";

            // Lấy giá nhập kho cho sách từ bảng books
            $price_query = "SELECT ImportPrice FROM books WHERE BookID = :book_id";
            $price_stmt = $conn->prepare($price_query);
            $price_stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $price_stmt->execute();
            $price_data = $price_stmt->fetch(PDO::FETCH_ASSOC);
            $price_per_unit = $price_data ? $price_data['ImportPrice'] : 0;

            // Thêm thông tin giao dịch nhập kho
            $stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
            $stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
            $stmt->bindParam(':price_per_unit', $price_per_unit, PDO::PARAM_STR);
            $stmt->execute();

            // Cập nhật kho hàng - cộng dồn số lượng sách trong bảng stock
            $stock_query = "INSERT INTO stock (BookID, Quantity) 
                            VALUES (:book_id, :quantity) 
                            ON DUPLICATE KEY UPDATE Quantity = Quantity + :quantity";
            $stock_stmt = $conn->prepare($stock_query);
            $stock_stmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
            $stock_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stock_stmt->execute();
        }

        // Commit transaction
        $conn->commit();
        echo "<script>alert('Nhập kho thành công!'); window.location.href='order_supplier.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Có lỗi xảy ra: {$e->getMessage()}');</script>";
    }
}


// Lấy danh sách nhà cung cấp và sách
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
$books = $conn->query("SELECT * FROM books")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập Sách từ Nhà Cung Cấp</title>
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
<div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <div class="d-flex gap-4">
            <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
            <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
            <a href="../../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
<div class="container mt-5">
    <h2 class="mb-4">Nhập Sách từ Nhà Cung Cấp</h2>
    <form method="POST">
        <!-- Chọn nhà cung cấp -->
        <div class="mb-3">
            <label for="supplier_id" class="form-label">Chọn Nhà Cung Cấp</label>
            <select class="form-select" name="supplier_id" id="supplier_id" required>
                <option value="" disabled selected>-- Chọn Nhà Cung Cấp --</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['SupplierID'] ?>"><?= htmlspecialchars($supplier['Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Chọn sách -->
        <div class="mb-3">
            <label for="books" class="form-label">Chọn Sách</label>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sách</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="books[]" value="<?= $book['BookID'] ?>">
                                <?= htmlspecialchars($book['Title']) ?>
                            </td>
                            <td>
                                <input type="number" name="quantities[]" class="form-control" min="1" value="1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Nút lưu -->
        <button type="submit" class="btn btn-primary">Lưu Nhập Kho</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
