<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Kiểm tra nếu có lựa chọn nhà cung cấp
$supplierID = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';

// Câu lệnh SQL lấy sách của nhà cung cấp đã chọn
$sql = "SELECT b.BookID, b.Title, b.ImportPrice, b.Price 
        FROM books b
        WHERE b.SupplierID = :supplier_id";  // Lọc theo SupplierID

// Thực thi câu lệnh
$stmt = $conn->prepare($sql);
$stmt->bindParam(':supplier_id', $supplierID, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách nhà cung cấp
$suppliers = $conn->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Xử lý khi người dùng gửi form để đặt sách
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy thông tin sách và số lượng từ form
    $bookIDs = $_POST['books'];
    $quantities = $_POST['quantities'];

    // Kiểm tra nếu có sách nào được chọn
    if (!empty($bookIDs) && !empty($quantities)) {
        try {
            // Bắt đầu transaction
            $conn->beginTransaction();

            // Lặp qua từng sách được chọn và thực hiện nhập kho
            foreach ($bookIDs as $index => $bookID) {
                $quantity = $quantities[$index];
                $remarks = "Import from supplier ID $supplierID";

                // Lấy giá nhập kho của sách từ bảng books
                $price_query = "SELECT ImportPrice FROM books WHERE BookID = :book_id";
                $price_stmt = $conn->prepare($price_query);
                $price_stmt->bindParam(':book_id', $bookID, PDO::PARAM_INT);
                $price_stmt->execute();
                $price_data = $price_stmt->fetch(PDO::FETCH_ASSOC);
                $price_per_unit = $price_data ? $price_data['ImportPrice'] : 0;

                // Thêm thông tin giao dịch nhập kho vào bảng stock_transactions
                $transaction_query = "INSERT INTO stock_transactions (BookID, TransactionType, Quantity, SupplierID, Remarks, PricePerUnit) 
                                      VALUES (:book_id, 'Import', :quantity, :supplier_id, :remarks, :price_per_unit)";
                $transaction_stmt = $conn->prepare($transaction_query);
                $transaction_stmt->bindParam(':book_id', $bookID, PDO::PARAM_INT);
                $transaction_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $transaction_stmt->bindParam(':supplier_id', $supplierID, PDO::PARAM_INT);
                $transaction_stmt->bindParam(':remarks', $remarks, PDO::PARAM_STR);
                $transaction_stmt->bindParam(':price_per_unit', $price_per_unit, PDO::PARAM_STR);
                $transaction_stmt->execute();

                // Cập nhật số lượng trong bảng stock
                $stock_query = "INSERT INTO stock (BookID, Quantity) 
                                VALUES (:book_id, :quantity) 
                                ON DUPLICATE KEY UPDATE Quantity = Quantity + :quantity";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bindParam(':book_id', $bookID, PDO::PARAM_INT);
                $stock_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                $stock_stmt->execute();
            }

            // Commit transaction
            $conn->commit();
            echo "<script>alert('Đặt sách và nhập kho thành công!'); window.location.href='order_supplier.php';</script>";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<script>alert('Có lỗi xảy ra: {$e->getMessage()}');</script>";
        }
    }
}
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
    <h2 class="mb-4">Chọn Nhà Cung Cấp để Xem Sách</h2>

    <form method="GET" action="" class="d-flex align-items-center mb-3">
    <div class="me-3 flex-grow-1">
        <label for="supplier_id" class="form-label">Chọn Nhà Cung Cấp</label>
        <select class="form-select" name="supplier_id" id="supplier_id" required>
            <option value="" disabled selected>-- Chọn Nhà Cung Cấp --</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= $supplier['SupplierID'] ?>" <?= ($supplier['SupplierID'] == $supplierID) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($supplier['Name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary align-self-end">Lọc</button>
</form>

    <?php if ($supplierID && count($books) > 0): ?>
        <h3 class="mt-4">Sách từ Nhà Cung Cấp: <?= htmlspecialchars($suppliers[array_search($supplierID, array_column($suppliers, 'SupplierID'))]['Name']) ?></h3>
        <form method="POST" action="">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Chọn</th>
                        <th>Tên Sách</th>
                        <th>Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="books[]" value="<?= $book['BookID'] ?>">
                            </td>
                            <td><?= htmlspecialchars($book['Title']) ?></td>
                            <td>
                                <input type="number" name="quantities[]" class="form-control" min="1" value="1">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-success">Đặt Sách và Nhập Kho</button>
        </form>
    <?php elseif ($supplierID): ?>
        <p class="text-danger">Không có sách nào từ nhà cung cấp này.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
