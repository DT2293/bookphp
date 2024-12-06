<?php
include '../includes/header.php';
include '../includes/db.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Lấy ID sách từ URL
if (isset($_GET['id'])) {
    $bookId = $_GET['id'];
} else {
    die("Không tìm thấy sách.");
}

try {
    // Lấy thông tin chi tiết sách từ cơ sở dữ liệu
    $stmt = $conn->prepare("
        SELECT b.*, a.Name AS AuthorName
        FROM books b
        JOIN authors a ON b.AuthorID = a.AuthorID
        WHERE b.BookID = :bookId
    ");
    $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $stmt->execute();
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        die("Sách không tồn tại.");
    }

    $stmt = $conn->prepare("
    SELECT r.Rating, r.Comment, r.ReviewDate, c.FullName AS CustomerName
    FROM Reviews r
    JOIN Customers c ON r.CustomerID = c.CustomerID
    WHERE r.BookID = :bookId
    ORDER BY r.ReviewDate DESC
");
    $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

?>

<main class="container my-4">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <!-- Hiển thị ảnh bìa sách -->
                <img src="../assets/uploads/<?php echo htmlspecialchars(basename($book['CoverImageUrl'])); ?>"
                    alt="Book Cover" class="mt-0" style="height: 450px; object-fit: contain;">
            </div>
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($book['Title']); ?></h2>
                <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($book['AuthorName']); ?></p>
                <p><strong>Giá:</strong> <?php echo number_format($book['Price'], 0, ',', '.') . " VND"; ?></p>
                <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($book['Description'])); ?></p>

                <!-- Input số lượng -->
                <label for="quantity" class="form-label">Số lượng:</label>
                <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" required />

                <!-- Form thêm vào giỏ hàng -->
                <form method="post" action="../cart/add.php" class="add-to-cart">
                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($book['BookID']); ?>">
                    <input type="hidden" name="book_title" value="<?= htmlspecialchars($book['Title']); ?>">
                    <input type="hidden" name="book_author" value="<?= htmlspecialchars($book['AuthorName']); ?>">
                    <input type="hidden" name="book_price" value="<?= htmlspecialchars($book['Price']); ?>">
                    <input type="hidden" name="quantity" id="hidden_quantity" value="1"> <!-- Đảm bảo gửi số lượng đúng -->

                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                        Thêm vào giỏ
                    </button>
                </form>

                <?php if (isset($message)) {
                    echo "<div class='alert alert-success mt-3'>$message</div>";
                } ?>
            </div>
            <h3>Bình luận và đánh giá</h3>
            <div class="reviews">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review border rounded p-3 mb-2">
                            <p><strong><?php echo htmlspecialchars($review['CustomerName']); ?></strong>
                                (<?php echo htmlspecialchars($review['ReviewDate']); ?>)
                            </p>
                            <p>Đánh giá: <?php echo str_repeat('⭐', $review['Rating']); ?></p>
                            <p><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có bình luận nào cho cuốn sách này.</p>
                <?php endif; ?>
            </div>

            <h4>Viết bình luận của bạn</h4>
            <form method="post" action="add_review.php" class="mt-3">
                <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($bookId); ?>">
                <div class="mb-3">
                    <label for="rating" class="form-label">Đánh giá (1-5):</label>
                    <select name="rating" id="rating" class="form-select" required>
                        <option value="">Chọn đánh giá</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> ⭐</option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Bình luận:</label>
                    <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Gửi bình luận</button>
            </form>
<!-- 
<h3>Bình luận và đánh giá</h3>
<div class="reviews">
    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review border rounded p-3 mb-2">
                <p><strong><?php echo htmlspecialchars($review['CustomerName']); ?></strong>
                    (<?php echo htmlspecialchars($review['ReviewDate']); ?>)
                </p>
                <p>Đánh giá: <?php echo str_repeat('⭐', $review['Rating']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($review['Comment'])); ?></p>

                <form method="post" action="replies.php" class="mt-3">
                    <input type="hidden" name="review_id" value="<?php echo htmlspecialchars($review['ReviewID']); ?>">
                    <div class="mb-3">
                        <label for="reply_comment" class="form-label">Phản hồi:</label>
                        <textarea name="reply_comment" id="reply_comment" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-info">Gửi phản hồi</button>
                </form>

               
                <div class="replies mt-3">
                    <?php
                    // Lấy các phản hồi của bình luận này
                    $stmt = $conn->prepare("SELECT * FROM replies WHERE ReviewID = :reviewID ORDER BY ReplyDate DESC");
                    $stmt->bindParam(':reviewID', $review['ReviewID'], PDO::PARAM_INT);
                    $stmt->execute();
                    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (!empty($replies)): ?>
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply border rounded p-2 mt-2">
                                <p><strong><?php echo htmlspecialchars($reply['CustomerName']); ?></strong> (<?php echo htmlspecialchars($reply['ReplyDate']); ?>)</p>
                                <p><?php echo nl2br(htmlspecialchars($reply['Comment'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Chưa có phản hồi nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Chưa có bình luận nào cho cuốn sách này.</p>
    <?php endif; ?>
</div> -->


        </div>
    </div>
</main>

<!-- Thêm mã JavaScript và jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Khi người dùng thay đổi số lượng
        $('#quantity').on('input', function() {
            var quantity = $(this).val();
            // Cập nhật giá trị quantity trong hidden field
            $('#hidden_quantity').val(quantity);
        });

        // Khi người dùng nhấn vào nút "Thêm vào giỏ"
        $('.add-to-cart button').on('click', function(e) {
            e.preventDefault(); // Ngừng form mặc định submit

            var quantity = $('#hidden_quantity').val();
            var bookId = $('input[name="book_id"]').val();
            var bookTitle = $('input[name="book_title"]').val();
            var bookPrice = $('input[name="book_price"]').val();

            // Gửi AJAX để thêm sản phẩm vào giỏ hàng
            $.ajax({
                url: '../cart/add.php', // Đảm bảo URL đúng
                type: 'POST',
                data: {
                    book_id: bookId,
                    book_title: bookTitle,
                    book_price: bookPrice,
                    quantity: quantity
                },
                success: function(response) {
                    // Kiểm tra xem response có hợp lệ không
                    if (response.success) {
                        alert('Sản phẩm đã được thêm vào giỏ hàng!');
                    } else {
                        alert('Đã có lỗi khi thêm sản phẩm vào giỏ hàng.');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra. Vui lòng thử lại.');
                }
            });
        });
    });
</script>

</body>

</html>