<?php
include '../../includes/db.php';

// Kiểm tra nếu có OrderID được gửi đến
if (isset($_GET['OrderID'])) {
    $orderID = $_GET['OrderID'];

    // Cập nhật trạng thái của đơn hàng từ 'Pending' sang 'Shipped'
    $sql = "UPDATE orders SET Status = 'Shipped' WHERE OrderID = :OrderID AND Status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':OrderID', $orderID);
    
    if ($stmt->execute()) {
        // Trả về phản hồi thành công
        echo json_encode(['success' => true, 'message' => 'Đơn hàng đã được xác nhận!']);
    } else {
        // Trả về phản hồi lỗi
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.']);
    }
}
?>
