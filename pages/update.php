<?php
session_start();
include '../includes/db.php';

// Kiểm tra dữ liệu gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookId'], $_POST['quantity'])) {
    $bookId = $_POST['bookId'];
    $newQuantity = $_POST['quantity'];

    // Lấy số lượng hàng trong kho
    $stmt = $conn->prepare("SELECT SUM(Quantity) AS TotalStock FROM stock WHERE BookID = :bookId");
    $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$stock || $stock['TotalStock'] < $newQuantity) {
        // Nếu số lượng vượt quá số lượng trong kho
        echo json_encode([
            'success' => false,
            'message' => 'Số lượng mua vượt quá số lượng hàng trong kho.',
        ]);
        exit;
    }

    // Cập nhật số lượng trong giỏ hàng
    $_SESSION['cart'][$bookId]['quantity'] = $newQuantity;

    // Tính toán tổng tiền
    $total = 0;
    $subtotals = [];
    foreach ($_SESSION['cart'] as $id => $item) {
        $stmt = $conn->prepare("SELECT Price FROM books WHERE BookID = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        $price = $book['Price'];
        $subtotal = $price * $item['quantity'];
        $subtotals[$id] = number_format($subtotal, 0, ',', '.') . ' VNĐ';
        $total += $subtotal;
    }

    echo json_encode([
        'success' => true,
        'subtotals' => $subtotals,
        'total' => number_format($total, 0, ',', '.') . ' VNĐ',
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
exit;
