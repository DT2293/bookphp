<?php
include '../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    die("Bạn cần đăng nhập để thực hiện chức năng này.");
}

// Lấy dữ liệu từ form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['book_id'];
    $customerId = $_SESSION['CustomerID'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Kiểm tra dữ liệu hợp lệ
    if (empty($rating) || empty($comment)) {
        die("Vui lòng điền đầy đủ thông tin.");
    }

    try {
        // Thêm bình luận vào cơ sở dữ liệu
        $stmt = $conn->prepare("
            INSERT INTO Reviews (BookID, CustomerID, Rating, Comment)
            VALUES (:bookId, :customerId, :rating, :comment)
        ");
        $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
        $stmt->bindParam(':customerId', $customerId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: readmore.php?id=$bookId");
    } catch (PDOException $e) {
        die("Lỗi thêm bình luận: " . $e->getMessage());
    }
}
