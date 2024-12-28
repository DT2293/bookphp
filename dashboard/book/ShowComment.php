<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Lấy BookID từ URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Kiểm tra nếu có BookID
if ($bookId === 0) {
    echo "Sách không tồn tại!";
    exit;
}

// Lấy thông tin sách từ bảng books
$stmt = $conn->prepare("SELECT * FROM books WHERE BookID = :bookId");
$stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
$stmt->execute();
$book = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy sách
if (!$book) {
    echo "Không tìm thấy sách!";
    exit;
}

// Lấy các bình luận cho sách từ bảng reviews
$stmt = $conn->prepare("SELECT r.*, c.FullName FROM reviews r 
                        JOIN customers c ON r.CustomerID = c.CustomerID 
                        WHERE r.BookID = :bookId
                        ORDER BY r.ReviewDate DESC");
$stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý khi form gửi trả lời
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['replyContent'], $_POST['reviewId'])) {
    $replyContent = trim($_POST['replyContent']);
    $reviewId = (int)$_POST['reviewId'];
    $adminId = $_SESSION['CustomerID'];

    if (empty($replyContent)) {
        echo "Nội dung trả lời không được để trống!";
    } else {
        // Thêm câu trả lời vào bảng replies
        $stmt = $conn->prepare("INSERT INTO replies (ReviewID, AdminID, ReplyContent) 
                                VALUES (:reviewId, :adminId, :replyContent)");
        $stmt->bindParam(':reviewId', $reviewId, PDO::PARAM_INT);
        $stmt->bindParam(':adminId', $adminId, PDO::PARAM_INT);
        $stmt->bindParam(':replyContent', $replyContent, PDO::PARAM_STR);
        $stmt->execute();

        echo "Trả lời đã được gửi!";
        header("Location: ShowComment.php?id=" . $bookId); // Reload lại trang sau khi gửi
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý bình luận</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hover-link:hover {
            color: #6a11cb;
            text-decoration: underline;
            transition: color 0.3s ease, text-decoration 0.3s ease;
        }
        .container-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .nav-item {
            margin-right: 20px;
        }
        .btn-logout {
            margin-left: 20px;
        }
    </style>
</head>
<body>
<div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Phần chào mừng -->
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
         
        <style>
            .hover-link:hover {
                color: #6a11cb; /* Màu khi hover */
                text-decoration: underline; /* Gạch chân khi hover */
                transition: color 0.3s ease, text-decoration 0.3s ease; /* Hiệu ứng mượt */
            }

        </style>
        <!-- Các liên kết quản lý -->
        <div class="d-flex gap-4" >
        <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
                <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
                <a href="../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
                <a href="../book/showbook.php   " class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
                <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản đơn hàng</a>
                <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản Kho hàng</a>
        </div>

        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
<div class="bg-light py-2 px-3">
    <div class="container py-2 bg-light rounded shadow-sm">
        <h2><?php echo htmlspecialchars($book['Title']); ?> - Bình luận</h2>

        <!-- Hiển thị các bình luận -->
        <?php if ($reviews): ?>
            <div>
                <h3>Các bình luận:</h3>
                <ul>
                    <?php foreach ($reviews as $review): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($review['FullName']); ?> (<?php echo $review['Rating']; ?> sao)</strong>
                            <p><?php echo htmlspecialchars($review['Comment']); ?></p>
                            <small>Đánh giá vào <?php echo $review['ReviewDate']; ?></small>

                            <!-- Hiển thị các trả lời -->
                            <?php
                            // Lấy danh sách trả lời liên quan đến bình luận
                            $stmt = $conn->prepare("SELECT r.*, c.FullName as AdminName FROM replies r 
                                                    LEFT JOIN customers c ON r.AdminID = c.CustomerID 
                                                    WHERE r.ReviewID = :reviewId
                                                    ORDER BY r.ReplyDate ASC");
                            $stmt->bindParam(':reviewId', $review['ReviewID'], PDO::PARAM_INT);
                            $stmt->execute();
                            $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            ?>
                            <?php if ($replies): ?>
                                <ul>
                                    <?php foreach ($replies as $reply): ?>
                                        <li>
                                            <strong>Admin <?php echo htmlspecialchars($reply['AdminName'] ?? ''); ?>:</strong>
                                            <p><?php echo htmlspecialchars($reply['ReplyContent']); ?></p>
                                            <small>Trả lời vào <?php echo $reply['ReplyDate']; ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <!-- Form trả lời -->
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="reviewId" value="<?php echo $review['ReviewID']; ?>">
                                <textarea name="replyContent" rows="2" class="form-control mb-2" placeholder="Trả lời bình luận..." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Gửi trả lời</button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Chưa có bình luận nào cho sách này.</p>
        <?php endif; ?>
    </div>
</div>
</body>

</html>


