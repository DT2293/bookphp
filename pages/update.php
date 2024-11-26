<?php
session_start();
include '../includes/db.php';

// Kiểm tra nếu có yêu cầu cập nhật giỏ hàng
if (isset($_POST['bookId']) && isset($_POST['quantity'])) {
    $bookId = (int)$_POST['bookId'];
    $quantity = (int)$_POST['quantity'];

    // Kiểm tra nếu sản phẩm tồn tại trong giỏ hàng
    if (isset($_SESSION['cart'][$bookId])) {
        // Cập nhật số lượng sản phẩm trong giỏ hàng
        $_SESSION['cart'][$bookId]['quantity'] = $quantity;

        // Lấy lại thông tin sách từ cơ sở dữ liệu
        $stmt = $conn->prepare("SELECT * FROM books WHERE BookID = ?");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Tính lại tổng tiền cho sách
        $subtotal = $book['Price'] * $quantity;

        // Tính tổng cộng cho giỏ hàng
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("SELECT Price FROM books WHERE BookID = ?");
            $stmt->execute([$item['bookId']]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);
            $total += $book['Price'] * $item['quantity'];
        }

        // Trả về kết quả dưới dạng JSON
        echo json_encode([
            'bookId' => $bookId,
            'subtotal' => number_format($subtotal, 0, ',', '.') . ' VNĐ',
            'total' => number_format($total, 0, ',', '.') . ' VNĐ'
        ]);
    } else {
        echo json_encode(['error' => 'Sản phẩm không tồn tại trong giỏ hàng.']);
    }
}
?>
