<?php
include '../../includes/db.php';
session_start();

// Xử lý tìm kiếm và sắp xếp
$search = '';
$orderBy = 'orders.OrderDate DESC'; // Mặc định sắp xếp theo ngày đặt giảm dần

// Lấy giá trị tìm kiếm nếu có
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
}

// Lấy giá trị sắp xếp nếu có
if (isset($_GET['order_by'])) {
    // Kiểm tra và đảm bảo thứ tự sắp xếp hợp lệ
    $validSortColumns = [
        'orders.OrderID',
        'customers.FullName',
        'orders.OrderDate',
        'orders.TotalAmount'
    ];

    $validSortOrders = ['ASC', 'DESC'];

    // Phân tách cột và hướng sắp xếp
    $orderParts = explode(' ', $_GET['order_by']);
    $column = $orderParts[0];
    $direction = isset($orderParts[1]) ? strtoupper($orderParts[1]) : 'DESC';

    // Kiểm tra nếu cột và hướng sắp xếp hợp lệ
    if (in_array($column, $validSortColumns) && in_array($direction, $validSortOrders)) {
        $orderBy = "$column $direction";
    }
}

// SQL để lấy danh sách đơn hàng với tìm kiếm
$sql = "SELECT 
            orders.OrderID, 
            customers.FullName AS CustomerName, 
            orders.OrderDate, 
            orders.TotalAmount, 
            orders.Status 
        FROM orders
        LEFT JOIN customers ON orders.CustomerID = customers.CustomerID
        WHERE customers.FullName LIKE :search OR orders.OrderID LIKE :search
        ORDER BY $orderBy";

$stmt = $conn->prepare($sql);
$stmt->execute(['search' => '%' . $search . '%']);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Phần chào mừng -->
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <!-- Các liên kết quản lý -->
        <div class="d-flex gap-4" >
        <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
                <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
                <a href="../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
                <a href="../book/showbook.php   " class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
                <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản đơn hàng</a>
                <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản Kho hàng</a>
        </div>
        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>

    <div class="container mt-5">
        <h1 class="mb-4">Quản lý đơn hàng</h1>
<!-- Tiêu đề bảng với nút sắp xếp -->


        <!-- Form tìm kiếm -->
        <form action="" method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm theo tên khách hàng hoặc mã đơn hàng">
                <button class="btn btn-primary" type="submit">Tìm kiếm</button>
            </div>
        </form>

        <!-- Hiển thị thông báo -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message']; ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message']; ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th><a href="?order_by=orders.OrderID ASC" class="text-decoration-none text-dark hover-link">Mã đơn ↑</a> | <a href="?order_by=orders.OrderID DESC" class="text-decoration-none text-dark hover-link">↓</a></th>
                    <th><a href="?order_by=customers.FullName ASC" class="text-decoration-none text-dark hover-link">Khách hàng ↑</a> | <a href="?order_by=customers.FullName DESC" class="text-decoration-none text-dark hover-link">↓</a></th>
                    <th><a href="?order_by=orders.OrderDate ASC" class="text-decoration-none text-dark hover-link">Ngày đặt ↑</a> | <a href="?order_by=orders.OrderDate DESC" class="text-decoration-none text-dark hover-link">↓</a></th>
                    <th><a href="?order_by=orders.TotalAmount ASC" class="text-decoration-none text-dark hover-link">Tổng tiền ↑</a> | <a href="?order_by=orders.TotalAmount DESC" class="text-decoration-none text-dark hover-link">↓</a></th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['OrderID']) ?></td>
                        <td><?= htmlspecialchars($order['CustomerName']) ?></td>
                        <td><?= htmlspecialchars($order['OrderDate']) ?></td>
                        <td><?= number_format($order['TotalAmount'], 2) ?> VND</td>
                        <td>
                            <span class="badge 
                                <?= $order['Status'] === 'Pending' ? 'bg-warning' : '' ?>
                                <?= $order['Status'] === 'Shipped' ? 'bg-primary' : '' ?>
                                <?= $order['Status'] === 'Delivered' ? 'bg-success' : '' ?>
                                <?= $order['Status'] === 'Cancelled' ? 'bg-danger' : '' ?>">
                                <?= htmlspecialchars($order['Status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="order_details.php?OrderID=<?= $order['OrderID'] ?>" class="btn btn-info btn-sm">Xem</a>
                            <?php if ($order['Status'] === 'Pending'): ?>
                                <a href="delete_order.php?OrderID=<?= $order['OrderID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">Xóa</a>
                                <button class="btn btn-success btn-sm">Xác nhận</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
   <script>
    document.addEventListener('DOMContentLoaded', function () {
        const confirmButtons = document.querySelectorAll('.confirm-order-btn');

        confirmButtons.forEach(button => {
            button.addEventListener('click', function () {
                const orderID = this.getAttribute('data-order-id');

                if (confirm('Bạn có chắc muốn xác nhận đơn hàng này?')) {
                    fetch(`confirm_order.php?OrderID=${orderID}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);

                                // Cập nhật trạng thái trên giao diện
                                const row = this.closest('tr');
                                row.querySelector('td:nth-child(5)').innerHTML = `
                                    <span class="badge bg-primary">Shipped</span>
                                `;
                                
                                // Ẩn nút Xóa
                                const deleteButton = row.querySelector('.btn-danger');
                                if (deleteButton) {
                                    deleteButton.style.display = 'none';
                                }

                                // Ẩn nút Xác nhận
                                this.style.display = 'none';
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Lỗi khi xác nhận:', error);
                            alert('Có lỗi xảy ra. Vui lòng thử lại sau.');
                        });
                }
            });
        });
    });
   </script>
</body>
</html>
