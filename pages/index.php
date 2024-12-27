    <?php include '../includes/header.php'; ?>
    <?php include '../includes/db.php'; ?>
    <?php


    // Kiểm tra nếu người dùng chưa đăng nhập
    // if (!isset($_SESSION['CustomerID'])) {
    //     header("Location: ../auth/login_page.php");
    //     exit;
    // }
    ?>
    <main class="container my-4">
    <div class="container mt-5">
        <h2>Tìm kiếm sách</h2>
        <!-- Form tìm kiếm sách -->
        <form action="search.php" method="GET">
            <div class="row mb-3 d-flex align-items-center">
                <!-- Tìm theo tên sách -->
                <div class="col-md-3">
                    <input type="text" class="form-control" id="bookName" name="bookName" placeholder="Nhập tên sách...">
                </div>

                <!-- Tìm theo tác giả -->
                <div class="col-md-3">
                    <select class="form-select" id="author" name="author">
                        <option value="">Tất cả tác giả</option>
                        <?php
                        // Kết nối cơ sở dữ liệu và lấy danh sách tác giả
                        include 'includes/db.php';
                        $stmt = $conn->query("SELECT * FROM Authors");
                        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($authors as $author) {
                            echo "<option value='" . $author['AuthorID'] . "'>" . htmlspecialchars($author['Name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Tìm theo thể loại -->
                <div class="col-md-3">
                    <select class="form-select" id="category" name="category">
                        <option value="">Tất cả thể loại</option>
                        <?php
                        // Lấy danh sách thể loại từ cơ sở dữ liệu
                        $stmt = $conn->query("SELECT * FROM Categories");
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($categories as $category) {
                            echo "<option value='" . $category['CategoryID'] . "'>" . htmlspecialchars($category['CategoryName']) . "</option>";
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
        <div class="product-list">
            <div class="row g-4">
                <?php 
                $book_category_1 = "
                    SELECT b.BookID, b.title, a.Name AS author, b.price, b.CoverImageURL
                    FROM Books b
                    JOIN authors a ON b.AuthorID = a.AuthorID
                    
                    
                "; 
                //LIMIT 4 OFFSET 1  
                $stmt = $conn->prepare($book_category_1);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <div class="col-md-3">
                        <div class="card h-100 shadow-sm">
                            <img src="../assets/uploads/<?php echo htmlspecialchars(basename($row['CoverImageURL'])); ?>" 
                                alt="Books" class="card-img-top img-fluid p-3" style="height: 250px; object-fit: contain;">
                            <div class="card-body text-center">
                                <h5 class="card-title text-truncate"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text text-muted mb-1">
                                    Tác giả: <strong><?php echo htmlspecialchars($row['author']); ?></strong>
                                </p>
                                <p class="card-text text-primary fw-bold">
                                <td><?php echo htmlspecialchars(number_format($row['price'], 0)); ?> VND</td>
                                </p>
                            </div>
                            <div class="card-footer text-center">
                                <form method="post" action="../cart/add.php" class="add-to-cart">
                                    <input type="hidden" name="book_id" value="<?= htmlspecialchars($row['BookID']); ?>"> 
                                    <input type="hidden" name="book_title" value="<?= htmlspecialchars($row['title']); ?>"> 
                                    <input type="hidden" name="book_author" value="<?= htmlspecialchars($row['author']); ?>"> 
                                    <input type="hidden" name="book_price" value="<?= htmlspecialchars($row['price']); ?>"> 

                                    <button type="button" 
                                            class="btn btn-primary w-100 add-to-cart-btn" 
                                            data-book-id="<?= htmlspecialchars($row['BookID']); ?>" 
                                            data-book-title="<?= htmlspecialchars($row['title']); ?>" 
                                            data-book-price="<?= number_format($row['price'], 2, '.', ''); ?>">
                                        Thêm vào giỏ
                                    </button>
                                    <br/>
                                </form>
                                <a href="readmore.php?id=<?php echo $row['BookID']; ?>" class="text-decoration-none text-dark hover-link">
                                Xem chi tiết sách
                                    </a>
                            
                            </div>
                        </div>
                    </div>
                <?php } } else { ?>
                    <div class="col-12">
                        <p class="text-center text-muted">Không có sách nào trong danh mục này.</p>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <!-- Thêm mã JavaScript và jQuery -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            console.log(response.success);
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
    </script> -->
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
