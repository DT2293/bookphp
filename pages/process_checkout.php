<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Giỏ hàng của bạn đang trống. Không thể thanh toán.'); window.location.href = '../pages/cart.php';</script>";
    exit;
}

// Kiểm tra thông tin người dùng
if (!isset($_SESSION['CustomerID'])) {
    echo "<script>alert('Bạn cần đăng nhập trước khi thanh toán.'); window.location.href = '../auth/login_page.php';</script>";
    exit;
}

// Nhận thông tin từ POST
$customerId = $_SESSION['CustomerID'];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        // Thêm đơn hàng
        $stmt = $conn->prepare("INSERT INTO orders (CustomerID, TotalAmount, Status) VALUES (?, ?, 'Pending')");
        $totalAmount = array_sum(array_map(function ($item) {
            return $item['quantity'] * $item['price'];
        }, $_SESSION['cart']));
        $stmt->execute([$customerId, $totalAmount]);
        $orderId = $conn->lastInsertId();

        // Xử lý các sản phẩm trong giỏ hàng
        foreach ($_SESSION['cart'] as $bookId => $item) {
            $quantity = $item['quantity'];
            $unitPrice = $item['price'];

            // Kiểm tra tồn kho
            $stmt = $conn->prepare("SELECT Quantity FROM stock WHERE BookID = ?");
            $stmt->execute([$bookId]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock || $stock['Quantity'] < $quantity) {
                throw new Exception("Sản phẩm ID {$bookId} không đủ hàng trong kho.");
            }

            // Cập nhật tồn kho
            $stmt = $conn->prepare("UPDATE stock SET Quantity = Quantity - ? WHERE BookID = ?");
            $stmt->execute([$quantity, $bookId]);

            // Ghi giao dịch xuất kho
            $stmt = $conn->prepare("INSERT INTO stock_transactions (BookID, TransactionType, Quantity, Remarks) VALUES (?, 'Export', ?, ?)");
            $stmt->execute([$bookId, $quantity, "Xuất kho cho đơn hàng #{$orderId}"]);

            // Lưu thông tin sản phẩm vào bảng orderitems
            $stmt = $conn->prepare("INSERT INTO orderitems (OrderID, BookID, Quantity, UnitPrice) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $bookId, $quantity, $unitPrice]);
        }

        // Hoàn tất giao dịch
        $conn->commit();
        unset($_SESSION['cart']); // Xóa giỏ hàng sau khi thanh toán

        echo "<script>
            alert('Thanh toán thành công! Mã đơn hàng của bạn là: $orderId');
            window.location.href = '../pages/index.php';
        </script>";
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>
            alert('Đã xảy ra lỗi: " . $e->getMessage() . "');
            window.location.href = '../pages/cart.php';
        </script>";
    }
} else {
    echo "<script>
        alert('Yêu cầu không hợp lệ.');
        window.location.href = '../pages/cart.php';
    </script>";
}
?>
