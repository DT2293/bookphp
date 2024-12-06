<?php
session_start();
include '../includes/db.php';

// Kiểm tra yêu cầu từ AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = isset($_POST['bookId']) ? intval($_POST['bookId']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    // Nếu giỏ hàng không tồn tại hoặc sách không có trong giỏ hàng
    if (!isset($_SESSION['cart'][$bookId])) {
        echo json_encode(['error' => 'Sách không tồn tại trong giỏ hàng.']);
        exit;
    }

    // Cập nhật số lượng
    $_SESSION['cart'][$bookId]['quantity'] = $quantity;

    // Tính lại subtotal và tổng tiền
    $subtotal = 0;
    $total = 0;
    $subtotals = [];

    foreach ($_SESSION['cart'] as $id => $item) {
        $stmt = $conn->prepare("SELECT Price FROM Books WHERE BookID = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($book) {
            $price = $book['Price'];
            $currentSubtotal = $price * $item['quantity'];
            $subtotals[$id] = number_format($currentSubtotal, 0, ',', '.') . ' VNĐ';
            $total += $currentSubtotal;

            if ($id == $bookId) {
                $subtotal = $currentSubtotal;
            }
        }
    }

    echo json_encode([
        'total' => number_format($total, 0, ',', '.') . ' VNĐ',
        'subtotals' => $subtotals,
    ]);
    exit;
}
