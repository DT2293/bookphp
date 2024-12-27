<?php
include '../../includes/db.php';
session_start();

// Lấy danh sách đơn hàng
$sql = "SELECT 
            orders.OrderID, 
            customers.FullName AS CustomerName, 
            orders.OrderDate, 
            orders.TotalAmount, 
            orders.Status 
        FROM orders
        LEFT JOIN customers ON orders.CustomerID = customers.CustomerID
        ORDER BY orders.OrderDate DESC";
$stmt = $conn->query($sql);
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
         
        <style>
            .hover-link:hover {
                color: #6a11cb; /* Màu khi hover */
                text-decoration: underline; /* Gạch chân khi hover */
                transition: color 0.3s ease, text-decoration 0.3s ease; /* Hiệu ứng mượt */
            }

        </style>
        <!-- Các liên kết quản lý -->
        <div class="d-flex gap-4" >
            <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
            <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
            <a href="../user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../book/showbook.php" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
        </div>

        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
    <div class="container mt-5">
        <h1 class="mb-4">Quản lý đơn hàng</h1>

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
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
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
        <!-- Nút Xóa chỉ hiển thị khi trạng thái là 'Pending' -->
        <a href="delete_order.php?OrderID=<?= $order['OrderID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?');">Xóa</a>
        
        <!-- Nút Xác nhận chỉ hiển thị khi trạng thái là 'Pending' -->
        <button class="btn btn-success btn-sm confirm-order-btn" data-order-id="<?= $order['OrderID'] ?>">Xác nhận</button>
    <?php elseif ($order['Status'] === 'Shipped' || $order['Status'] === 'Delivered'): ?>
        <!-- Nếu trạng thái là 'Shipped' hoặc 'Delivered', không hiển thị nút Xóa -->
        <!-- Nút Xóa không hiển thị -->
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
