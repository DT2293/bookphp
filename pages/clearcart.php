<?php
// Bắt đầu session
session_start();

// Kiểm tra xem giỏ hàng có tồn tại trong session không
if (isset($_SESSION['cart'])) {
    // Xóa giỏ hàng
    unset($_SESSION['cart']);
    // Chuyển hướng người dùng về trang giỏ hàng hoặc trang chính sau khi xóa giỏ
    header("Location: cart.php"); // Hoặc header("Location: index.php");
    exit();
} else {
    // Nếu giỏ hàng trống, bạn có thể chuyển hướng hoặc thông báo
    echo 'Giỏ hàng trống!';
    // Chuyển hướng người dùng về trang giỏ hàng hoặc trang chính
    header("Location: cart.php");
    exit();
}
?>
