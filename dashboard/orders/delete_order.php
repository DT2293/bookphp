<?php
include '../../includes/db.php'; // Kết nối với cơ sở dữ liệu PDO
session_start();

// Kiểm tra xem có nhận được OrderID từ URL không
if (isset($_GET['OrderID'])) {
    $orderID = intval($_GET['OrderID']); // Lấy OrderID từ URL

    // Bắt đầu giao dịch (transaction)
    $conn->beginTransaction();

    try {
        // Xóa các bản ghi trong bảng orderitems (vì có ràng buộc khóa ngoại)
        $sql = "DELETE FROM orderitems WHERE OrderID = :orderID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':orderID', $orderID, PDO::PARAM_INT);
        $stmt->execute();

        // Xóa đơn hàng trong bảng orders
        $sql = "DELETE FROM orders WHERE OrderID = :orderID";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':orderID', $orderID, PDO::PARAM_INT);
        $stmt->execute();

        // Cam kết giao dịch nếu mọi thứ thành công
        $conn->commit();

        // Chuyển hướng về trang showorder.php sau khi xóa thành công
        header("Location: ../orders/showorders.php");
        exit();  // Dừng script lại sau khi redirect

    } catch (Exception $e) {
        // Nếu có lỗi, hoàn tác giao dịch
        $conn->rollBack();
        echo "Có lỗi xảy ra: " . $e->getMessage();
    }
} else {
    echo "Không có ID đơn hàng để xóa.";
}
?>
