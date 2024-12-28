<?php
include '../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Lấy ID khách hàng
$customerID = $_SESSION['CustomerID'];

try {
    // Kiểm tra nếu giỏ hàng trống
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng của bạn đang trống.']);
        exit;
    }

    // Kiểm tra nếu có thông tin thanh toán từ form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lấy thông tin từ form và bảo vệ chống XSS
        $name = htmlspecialchars($_POST['name'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $phone = htmlspecialchars($_POST['phone'] ?? '');
        $address = htmlspecialchars($_POST['address'] ?? '');

        // Kiểm tra tính hợp lệ của các trường nhập
        if (empty($name) || empty($email) || empty($phone) || empty($address)) {
            echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.']);
            exit;
        }

        // Kiểm tra định dạng email và số điện thoại (ví dụ đơn giản)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Email không hợp lệ.']);
            exit;
        }

        if (!preg_match('/^\d{10,11}$/', $phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Số điện thoại không hợp lệ.']);
            exit;
        }

        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        $conn->beginTransaction();

        // Lấy thông tin giỏ hàng và tính tổng
        $cartItems = $_SESSION['cart'];
        $total = 0;
        $totalQuantity = 0; // Tổng số lượng sách trong giỏ hàng
        foreach ($cartItems as $bookId => $item) {
            $stmt = $conn->prepare("SELECT * FROM books WHERE BookID = ?");
            $stmt->execute([$bookId]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($book) {
                $quantity = $item['quantity'];
                $price = $book['Price'];
                $total += $price * $quantity;
                $totalQuantity += $quantity;
            }
        }

        // Kiểm tra và áp dụng chương trình giảm giá
        $stmt = $conn->prepare("SELECT * FROM sale WHERE MinQuantity <= ? AND (MaxQuantity IS NULL OR MaxQuantity >= ?)");
        $stmt->execute([$totalQuantity, $totalQuantity]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        $discountAmount = 0;

        if ($sale) {
            $discountAmount = $sale['DiscountAmount'];
            $total -= $discountAmount; // Trừ giảm giá vào tổng tiền
            $saleId = $sale['SaleID']; // Lưu SaleID để sử dụng khi cập nhật đơn hàng
        }

        // Thực hiện thêm đơn hàng vào bảng `orders`
        $stmt = $conn->prepare("INSERT INTO orders (CustomerID, OrderDate, TotalAmount, Status, SaleID) VALUES (?, NOW(), ?, 'Pending', ?)");
        $stmt->execute([$customerID, $total, $saleId ?? NULL]);

        // Lấy ID của đơn hàng vừa tạo
        $orderId = $conn->lastInsertId();

        // Cập nhật lại TotalAmount nếu có giảm giá
        if ($sale) {
            $stmt = $conn->prepare("UPDATE orders SET TotalAmount = ? WHERE OrderID = ?");
            $stmt->execute([$total, $orderId]);
        }

        // Cập nhật kho và ghi giao dịch xuất kho
        foreach ($cartItems as $bookId => $item) {
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

        // Sau khi tạo đơn hàng và chi tiết, xóa giỏ hàng
        unset($_SESSION['cart']);

        // Commit transaction
        $conn->commit();

        // Gửi phản hồi về mã đơn hàng và thông báo thành công
        echo json_encode([
            'status' => 'success',
            'message' => 'Thanh toán thành công!',
            'orderId' => $orderId
        ]);
    }
} catch (Exception $e) {
    // Rollback transaction nếu có lỗi xảy ra
    $conn->rollBack();

    // Gửi thông báo lỗi
    echo json_encode([
        'status' => 'error',
        'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
    ]);
}
?>
