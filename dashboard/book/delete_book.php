<?php
include '../../includes/db.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu có ID được gửi qua URL
if (isset($_GET['id'])) {
    $bookId = intval($_GET['id']);

    // Thực hiện xóa sách
    $stmt = $conn->prepare("DELETE FROM Books WHERE BookID = ?");
    $stmt->execute([$bookId]);

    // Quay lại trang danh sách sách
    header("Location: showbook.php?success=1");
    exit;
} else {
    echo "Không tìm thấy ID sách để xóa.";
    exit;
}
?>
