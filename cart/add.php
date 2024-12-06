<?php
session_start();
header('Content-Type: application/json');  // Đảm bảo trả về dữ liệu dưới dạng JSON

// Kiểm tra xem có dữ liệu từ AJAX gửi đến không
if (isset($_POST['book_id']) && isset($_POST['book_title']) && isset($_POST['book_price'])) {
    // Lấy dữ liệu từ POST
    $bookId = $_POST['book_id'];
    $bookTitle = $_POST['book_title'];
    $bookPrice = $_POST['book_price'];

    // Giả sử bạn có một giỏ hàng trong session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Thêm sản phẩm vào giỏ hàng (hoặc cập nhật số lượng)
    if (isset($_SESSION['cart'][$bookId])) {
        $_SESSION['cart'][$bookId]['quantity'] += 1;  // Nếu sách đã có, tăng số lượng
    } else {
        $_SESSION['cart'][$bookId] = [
            'title' => $bookTitle,
            'price' => $bookPrice,
            'quantity' => 1
        ];
    }

    // Tính tổng số lượng sản phẩm trong giỏ hàng
    $totalItems = array_sum(array_column($_SESSION['cart'], 'quantity'));

    // Trả về phản hồi JSON với success và số lượng giỏ hàng
    echo json_encode([
        'success' => true,
        'totalItems' => $totalItems
    ]);
} else {
    // Trả về lỗi nếu thiếu dữ liệu
    echo json_encode(['success' => false]);
}
?>

