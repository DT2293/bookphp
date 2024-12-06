<?php
include '../includes/header.php';
include '../includes/db.php';
// Kiểm tra xem giỏ hàng có tồn tại trong session không

?>

<main class="container mt-5">
    <h2 class="mb-4">Giỏ hàng</h2>
    <?php if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])): ?>
        <p>Giỏ hàng của bạn đang trống.</p>
    <?php else: ?>
        <form id="cart-form">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Sách</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Tổng</th>
                        <th>Chức năng</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $total = 0; // Tổng tiền
                foreach ($_SESSION['cart'] as $bookId => $item) {
                    $stmt = $conn->prepare("SELECT * FROM books WHERE BookID = ?");
                    $stmt->execute([$bookId]);
                    $book = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Kiểm tra nếu sách không tồn tại
                    if (!$book) {
                        echo "<tr><td colspan='4' class='text-center text-danger'>Không tìm thấy sách với ID: $bookId</td></tr>";
                        continue;
                    }

                    // Lấy thông tin sách
                    $price = $book['Price']; // Giá sách
                    $quantity = $item['quantity']; // Số lượng trong giỏ
                    $subtotal = $price * $quantity; // Tổng tiền cho sách đó
                    $total += $subtotal; // Cộng tổng tiền
                    ?>
                    <tr data-book-id="<?= $bookId ?>">
                        <td><?= htmlspecialchars($book['Title']) ?></td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-secondary btn-sm decrease" data-action="minus" data-book-id="<?= $bookId ?>">-</button>
                                <input type="text" class="form-control form-control-sm text-center quantity" value="<?= $quantity ?>" style="width: 50px;" disabled/>
                                <button type="button" class="btn btn-secondary btn-sm increase" data-action="plus" data-book-id="<?= $bookId ?>">+</button>
                            </div>
                        </td>
                        <td><?= number_format($price, 0, ',', '.') ?> VNĐ</td>
                        <td class="subtotal"><?= number_format($subtotal, 0, ',', '.') ?> VNĐ</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove" data-book-id="<?= $bookId ?>">Xóa</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between mt-3">
                <p><strong>Tổng cộng: <span id="total"><?= number_format($total, 0, ',', '.') ?> VNĐ</span></strong></p>
                
                <a href="../pages/checkout.php" class="btn btn-primary">Thanh toán</a>
                <a href="../pages/clearcart.php" class="btn btn-danger">Xóa hết giỏ hàng</a>
            </div>
        </form>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Xử lý sự kiện tăng/giảm số lượng
    $('.increase, .decrease').on('click', function() {
        var action = $(this).data('action');
        var bookId = $(this).data('book-id');
        var quantityField = $(this).closest('tr').find('.quantity');
        var quantity = parseInt(quantityField.val());

        // Tăng hoặc giảm số lượng
        if (action === 'plus') {
            quantity++;
        } else if (action === 'minus' && quantity > 1) {
            quantity--;
        }

        // Cập nhật số lượng mới vào ô input
        quantityField.val(quantity);

        // Gửi yêu cầu AJAX để cập nhật giỏ hàng
        $.ajax({
            url: 'update.php',
            method: 'POST',
            data: {
                bookId: bookId,
                quantity: quantity
            },
            success: function(response) {
                try {
                    var jsonResponse = JSON.parse(response);

                    // Cập nhật subtotal của sách
                    $('tr[data-book-id="' + bookId + '"]').find('.subtotal').text(jsonResponse.subtotals[bookId]);

                    // Cập nhật tổng tiền
                    $('#total').text(jsonResponse.total);
                } catch (e) {
                    alert('Lỗi xử lý dữ liệu. Vui lòng thử lại!');
                }
            },
            error: function() {
                alert('Đã có lỗi xảy ra. Vui lòng thử lại!');
            }
        });
    });
});

$(document).ready(function() {
    // Xử lý sự kiện xóa sản phẩm khỏi giỏ hàng
    $('.remove').on('click', function() {
        var bookId = $(this).data('book-id');

        // Gửi yêu cầu AJAX để xóa sản phẩm
        $.ajax({
            url: 'clear_product_cart.php', // Tạo file remove.php để xử lý
            method: 'POST',
            data: { bookId: bookId },
            success: function(response) {
                try {
                    var jsonResponse = JSON.parse(response);

                    // Xóa dòng sản phẩm khỏi bảng
                    $('tr[data-book-id="' + bookId + '"]').remove();

                    // Cập nhật tổng tiền
                    $('#total').text(jsonResponse.total);

                    // Hiển thị thông báo nếu giỏ hàng trống
                    if (jsonResponse.empty) {
                        $('table').remove();
                        $('main').append('<p>Giỏ hàng của bạn đang trống.</p>');
                    }
                } catch (e) {
                    alert('Lỗi xử lý dữ liệu. Vui lòng thử lại!');
                }
            },
            error: function() {
                alert('Đã có lỗi xảy ra. Vui lòng thử lại!');
            }
        });
    });
});
</script>
</body>
</html>
