<?php
include '../includes/db.php'; // Kết nối cơ sở dữ liệu
session_start();

// Kiểm tra nếu giỏ hàng trống
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Giỏ hàng của bạn đang trống. Không thể thanh toán.'); window.location.href = '../pages/cart.php';</script>";
    exit;
}

// Kiểm tra và lấy thông tin thanh toán
$email = $_POST['email'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// Kiểm tra xem người dùng có đăng nhập không
if (!isset($_SESSION['CustomerID'])) {
    echo "<script>alert('Bạn cần đăng nhập trước khi thanh toán.'); window.location.href = '../pages/login.php';</script>";
    exit;
}

$customerId = $_SESSION['CustomerID']; // Lấy ID người dùng từ session

// Lấy tên người dùng từ bảng Customers
$stmt = $conn->prepare("SELECT FullName FROM Customers WHERE CustomerID = ?");
$stmt->execute([$customerId]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    echo "<script>alert('Không tìm thấy thông tin người dùng.'); window.location.href = '../pages/cart.php';</script>";
    exit;
}

// Tính tổng tiền từ giỏ hàng
$totalAmount = 0;
foreach ($_SESSION['cart'] as $bookId => $item) {
    $stmt = $conn->prepare("SELECT Price FROM Books WHERE BookID = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo "<script>alert('Sách không tồn tại trong giỏ hàng.'); window.location.href = '../pages/cart.php';</script>";
        exit;
    }

    $quantity = $item['quantity'];
    $price = $book['Price'];
    $subtotal = $price * $quantity;
    $totalAmount += $subtotal;
}

try {
    // Bắt đầu giao dịch
    $conn->beginTransaction();

    // Thêm đơn hàng vào bảng Orders
    $stmt = $conn->prepare("
        INSERT INTO Orders (CustomerID, TotalAmount, OrderDate, Status)
        VALUES (?, ?, NOW(), 'Pending')
    ");
    $stmt->execute([$customerId, $totalAmount]);
    $orderId = $conn->lastInsertId(); // Lấy ID đơn hàng vừa tạo

    // Thêm chi tiết đơn hàng vào bảng OrderItems
    foreach ($_SESSION['cart'] as $bookId => $item) {
        $stmt = $conn->prepare("SELECT Price FROM Books WHERE BookID = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            $unitPrice = $book['Price']; // Lấy giá sách

            // Thêm chi tiết vào OrderItems
            $stmt = $conn->prepare("
                INSERT INTO OrderItems (OrderID, BookID, Quantity, UnitPrice)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$orderId, $bookId, $item['quantity'], $unitPrice]);
        }
    }

    // Xóa giỏ hàng sau khi hoàn tất thanh toán
    unset($_SESSION['cart']);

    // Commit giao dịch
    $conn->commit();

    // Hiển thị thông báo và chuyển hướng
    echo "<script>
        alert('Thanh toán thành công! Mã đơn hàng của bạn là: $orderId');
        window.location.href = '../pages/index.php';
    </script>";
    exit;

} catch (Exception $e) {
    // Rollback giao dịch nếu có lỗi
    $conn->rollBack();
    echo "<script>
        alert('Đã xảy ra lỗi khi xử lý đơn hàng: " . $e->getMessage() . "');
        window.location.href = '../pages/cart.php';
    </script>";
}
?>
