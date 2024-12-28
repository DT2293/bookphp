<?php
include '../../includes/db.php';
session_start();

// Kiểm tra xem có tồn tại OrderID hay không trong URL
if (isset($_GET['OrderID']) && is_numeric($_GET['OrderID'])) {
    $orderID = $_GET['OrderID'];

    // Truy vấn cơ sở dữ liệu để lấy thông tin chi tiết đơn hàng
    $sql = "SELECT 
                orders.OrderID, 
                customers.FullName AS CustomerName, 
                orders.OrderDate, 
                orders.TotalAmount, 
                orders.Status, 
                orderitems.BookID, 
                books.Title AS BookTitle, 
                orderitems.Quantity, 
                orderitems.unitprice
            FROM orders
            LEFT JOIN customers ON orders.CustomerID = customers.CustomerID
            LEFT JOIN orderitems ON orders.OrderID = orderitems.OrderID
            LEFT JOIN books ON orderitems.BookID = books.BookID
            WHERE orders.OrderID = :orderID";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['orderID' => $orderID]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra xem đơn hàng có tồn tại không
    if (!$order) {
        echo "Đơn hàng không tồn tại.";
        exit;
    }

    // Lấy các chi tiết sách trong đơn hàng
    $sqlDetails = "SELECT 
                        books.Title, 
                        orderitems.Quantity, 
                        orderitems.unitprice
                   FROM orderitems
                   LEFT JOIN books ON orderitems.BookID = books.BookID
                   WHERE orderitems.OrderID = :orderID";
    $stmtDetails = $conn->prepare($sqlDetails);
    $stmtDetails->execute(['orderID' => $orderID]);
    $orderDetails = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Mã đơn hàng không hợp lệ.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản lý hóa đơn</a>
            <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản lý kho hàng</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>   
<div class="container mt-5">
    <h2 class="mb-4">Chi tiết đơn hàng : <?= htmlspecialchars($order['OrderID']) ?></h2>

    <div class="mb-3">
        <strong>Khách hàng:</strong> <?= htmlspecialchars($order['CustomerName']) ?>
    </div>
    <div class="mb-3">
        <strong>Ngày đặt:</strong> <?= htmlspecialchars($order['OrderDate']) ?>
    </div>
    <div class="mb-3">
        <strong>Tổng tiền:</strong> <?= number_format($order['TotalAmount'], 2) ?> VND
    </div>
    <div class="mb-3">
        <strong>Trạng thái:</strong> <?= htmlspecialchars($order['Status']) ?>
    </div>

    <h3 class="mt-4">Chi tiết sản phẩm trong đơn hàng:</h3>
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Tên sách</th>
                <th>Số lượng</th>
                <th>Giá</th>
                <th>Tổng giá</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderDetails as $detail): ?>
                <tr>
                    <td><?= htmlspecialchars($detail['Title']) ?></td>
                    <td><?= $detail['Quantity'] ?></td>
                    <td><?= number_format($detail['unitprice'], 2) ?> VND</td>
                    <td><?= number_format($detail['Quantity'] * $detail['unitprice'], 2) ?> VND</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="../orders/showorders.php" class="btn btn-secondary">Quay lại</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
