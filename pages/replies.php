<?php
include '../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    die("Bạn cần đăng nhập để thực hiện chức năng này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = $_POST['review_id'];
    $customerId = $_SESSION['CustomerID'];
    $replyComment = $_POST['reply_comment'];

    // Kiểm tra dữ liệu hợp lệ
    if (empty($replyComment)) {
        die("Vui lòng điền đầy đủ thông tin.");
    }

    try {
        // Thêm phản hồi vào cơ sở dữ liệu
        $stmt = $conn->prepare("
            INSERT INTO replies (ReviewID, CustomerID, Comment)
            VALUES (:reviewId, :customerId, :comment)
        ");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $replyComment, PDO::PARAM_STR);
        $stmt->execute();

        // Chuyển lại về trang chi tiết sách với ID sách
        $bookId = $_POST['book_id'];  // Giả sử book_id được gửi từ form bình luận
        header("Location: readmore.php?id=$bookId");
    } catch (PDOException $e) {
        die("Lỗi thêm phản hồi: " . $e->getMessage());
    }
}
?>
