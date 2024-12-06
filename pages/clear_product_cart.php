<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookId'])) {
    $bookId = $_POST['bookId'];

    // Kiểm tra nếu sản phẩm tồn tại trong giỏ hàng
    if (isset($_SESSION['cart'][$bookId])) {
        unset($_SESSION['cart'][$bookId]); // Xóa sản phẩm khỏi giỏ hàng
    }

    // Tính lại tổng tiền
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $item) {
        $stmt = $conn->prepare("SELECT Price FROM books WHERE BookID = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            $total += $book['Price'] * $item['quantity'];
        }
    }

    // Kiểm tra nếu giỏ hàng trống
    $isEmpty = empty($_SESSION['cart']);

    // Trả về dữ liệu JSON
    echo json_encode([
        'total' => number_format($total, 0, ',', '.') . ' VNĐ',
        'empty' => $isEmpty,
    ]);
    exit;
}
?>
