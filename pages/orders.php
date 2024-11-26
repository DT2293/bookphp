    <?php
    include '../includes/db.php'; // Kết nối cơ sở dữ liệu
    session_start();

    // Kiểm tra nếu người dùng đã đăng nhập
    if (!isset($_SESSION['CustomerID'])) {
        echo "Bạn cần đăng nhập để xem thông tin đơn hàng.";
        exit;
    }

    $customerId = $_SESSION['CustomerID'];

    // Lấy danh sách đơn hàng của khách hàng
    $stmt = $conn->prepare("SELECT * FROM Orders WHERE CustomerID = ? ORDER BY OrderDate DESC");
    $stmt->execute([$customerId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Kiểm tra nếu không có đơn hàng nào
    if (empty($orders)) {
        echo "Bạn chưa có đơn hàng nào.";
        exit;
    }
    ?>

    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Danh sách Đơn Hàng</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>

    <h2>Danh sách Đơn Hàng</h2>

    <?php foreach ($orders as $order): ?>
        <div class="order">
            <h3>Mã Đơn Hàng: <?php echo $order['OrderID']; ?></h3>
            <p><strong>Ngày đặt:</strong> <?php echo $order['OrderDate']; ?></p>
            <p><strong>Tổng tiền:</strong> <?php echo number_format($order['TotalAmount'], 2); ?> VND</p>
            <p><strong>Trạng thái:</strong> <?php echo $order['Status']; ?></p>

            <!-- Chi tiết đơn hàng -->
            <h4>Chi Tiết Đơn Hàng:</h4>
            <table>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng</th>
                    <th>Đơn Giá</th>
                    <th>Tổng Tiền</th>
                </tr>
                <?php
                $orderId = $order['OrderID'];
                $stmt = $conn->prepare("SELECT oi.Quantity, oi.UnitPrice, b.Title 
                                        FROM OrderItems oi
                                        JOIN Books b ON oi.BookID = b.BookID
                                        WHERE oi.OrderID = ?");
                $stmt->execute([$orderId]);
                $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?php echo $item['Title']; ?></td>
                        <td><?php echo $item['Quantity']; ?></td>
                        <td><?php echo number_format($item['UnitPrice'], 2); ?> VND</td>
                        <td><?php echo number_format($item['Quantity'] * $item['UnitPrice'], 2); ?> VND</td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <hr>
        </div>
    <?php endforeach; ?>

    </body>
    </html>
