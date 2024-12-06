<?php include '../includes/db.php'; ?>
<?php include '../includes/header.php'; ?>
<?php
// Nhận dữ liệu từ form tìm kiếm
$bookName = isset($_GET['bookName']) ? trim($_GET['bookName']) : '';
$authorID = isset($_GET['author']) ? trim($_GET['author']) : '';
$categoryID = isset($_GET['category']) ? trim($_GET['category']) : '';

// Câu truy vấn SQL
$sql = "SELECT b.BookID, b.Title, b.Price, b.CoverImageURL, a.Name AS AuthorName 
        FROM Books b 
        JOIN Authors a ON b.AuthorID = a.AuthorID
        WHERE 1"; // Điều kiện mặc định để tránh lỗi SQL

$params = [];

// Thêm điều kiện tìm kiếm theo tên sách nếu có
if ($bookName !== '') {
    $sql .= " AND b.Title LIKE ?";
    $params[] = "%$bookName%";
}

// Thêm điều kiện tìm kiếm theo tác giả nếu có
if ($authorID !== '') {
    $sql .= " AND b.AuthorID = ?";
    $params[] = $authorID;
}

// Thêm điều kiện tìm kiếm theo thể loại nếu có
if ($categoryID !== '') {
    $sql .= " AND b.CategoryID = ?";
    $params[] = $categoryID;
}

// Thực thi câu truy vấn
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<main class="container my-4">
    <div class="container mt-5">
        <h2>Tìm kiếm sách</h2>
        <!-- Form tìm kiếm sách -->
        <form action="search.php" method="GET">
            <div class="row mb-3 d-flex align-items-center">
                <!-- Tìm theo tên sách -->
                <div class="col-md-3">
                    <input type="text" class="form-control" id="bookName" name="bookName" placeholder="Nhập tên sách..." value="<?= htmlspecialchars($bookName); ?>">
                </div>

                <!-- Tìm theo tác giả -->
                <div class="col-md-3">
                    <select class="form-select" id="author" name="author">
                        <option value="">Tất cả tác giả</option>
                        <?php
                        $stmt = $conn->query("SELECT * FROM Authors");
                        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($authors as $author) {
                            $selected = $authorID == $author['AuthorID'] ? 'selected' : '';
                            echo "<option value='" . $author['AuthorID'] . "' $selected>" . htmlspecialchars($author['Name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Tìm theo thể loại -->
                <div class="col-md-3">
                    <select class="form-select" id="category" name="category">
                        <option value="">Tất cả thể loại</option>
                        <?php
                        $stmt = $conn->query("SELECT * FROM Categories");
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories as $category) {
                            $selected = $categoryID == $category['CategoryID'] ? 'selected' : '';
                            echo "<option value='" . $category['CategoryID'] . "' $selected>" . htmlspecialchars($category['CategoryName']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Nút tìm kiếm -->
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                </div>
            </div>
        </form>
    </div>

    <h2 class="text-center mb-4">Danh sách sách</h2>

    <?php if (!empty($books)): ?>
        <div class="row g-4">
            <?php foreach ($books as $row): ?>
                <div class="col-md-3">
                    <div class="card h-100 shadow-sm">
                        <img src="../assets/uploads/<?= htmlspecialchars(basename($row['CoverImageURL'])); ?>" 
                             alt="Books" class="card-img-top img-fluid p-3" style="height: 250px; object-fit: contain;">
                        <div class="card-body text-center">
                            <h5 class="card-title text-truncate"><?= htmlspecialchars($row['Title']); ?></h5>
                            <p class="card-text text-muted mb-1">
                                Tác giả: <strong><?= htmlspecialchars($row['AuthorName']); ?></strong>
                            </p>
                            <p class="card-text text-primary fw-bold">
                                $<?= htmlspecialchars(number_format($row['Price'], 2)); ?>
                            </p>
                        </div>
                        <div class="card-footer text-center">
                            <form method="post" action="../cart/add.php" class="add-to-cart">
                                <input type="hidden" name="book_id" value="<?= htmlspecialchars($row['BookID']); ?>"> 
                                <input type="hidden" name="book_title" value="<?= htmlspecialchars($row['Title']); ?>"> 
                                <input type="hidden" name="book_price" value="<?= htmlspecialchars($row['Price']); ?>">

                                <button type="button" 
                                        class="btn btn-primary w-100 add-to-cart-btn" 
                                        data-book-id="<?= htmlspecialchars($row['BookID']); ?>" 
                                        data-book-title="<?= htmlspecialchars($row['Title']); ?>" 
                                        data-book-price="<?= number_format($row['Price'], 2, '.', ''); ?>">
                                    Thêm vào giỏ
                                </button>
                            </form>
                            <a href="readmore.php?id=<?= htmlspecialchars($row['BookID']); ?>" class="text-decoration-none text-dark hover-link">
                                Xem chi tiết sách
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted text-center">Không tìm thấy sách nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
    <?php endif; ?>
</main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Khi người dùng nhấn vào nút "Thêm vào giỏ"
        $('.add-to-cart-btn').on('click', function() {
            var button = $(this);
            var bookId = button.data('book-id');
            var bookTitle = button.data('book-title');
            var bookPrice = button.data('book-price');

            // Gửi AJAX để thêm sản phẩm vào giỏ hàng
            $.ajax({
                url: '../cart/add.php', // Đảm bảo rằng URL đúng
                type: 'POST',
                data: {
                    book_id: bookId,
                    book_title: bookTitle,
                    book_price: bookPrice
                },
                success: function(response) {
                    if (response.success) {
                        // Cập nhật số lượng giỏ hàng trên giao diện
                        $('#cart-count').text(response.totalItems);  // Giả sử có một phần tử để hiển thị số lượng giỏ hàng
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
