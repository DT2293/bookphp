<?php
include '../../includes/db.php'; // Kết nối cơ sở dữ liệu
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $customerId = intval($_GET['id']);

    // Chuẩn bị câu lệnh SQL để xóa người dùng
    $stmt = $conn->prepare("DELETE FROM customers WHERE CustomerID = ?");
    $stmt->execute([$customerId]);

    // Kiểm tra xem việc xóa có thành công không
    if ($stmt->rowCount() > 0) {
        echo "Người dùng đã được xóa thành công.";
        // Chuyển hướng về trang quản lý người dùng sau khi xóa
        header("Location: showuser.php");
        exit;
    } else {
        echo "Không tìm thấy người dùng với ID: $customerId";
    }
} else {
    echo "ID không hợp lệ.";
}
?>
