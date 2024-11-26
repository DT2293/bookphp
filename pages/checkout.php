<?php
include '../includes/header.php';
include '../includes/db.php';

// Kiểm tra nếu giỏ hàng trống
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<main class='container mt-5'><p>Giỏ hàng của bạn đang trống. <a href='../pages/cart.php'>Quay lại giỏ hàng</a>.</p></main>";
    exit;
}

// Lấy thông tin sách trong giỏ hàng
$cartItems = [];
$total = 0;
foreach ($_SESSION['cart'] as $bookId => $item) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE BookID = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($book) {
        $quantity = $item['quantity'];
        $price = $book['Price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        $cartItems[] = [
            'book' => $book,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<main class="container mt-5">
    <h2 class="mb-4">Thanh toán</h2>
    <div class="row">
        <!-- Thông tin giỏ hàng -->
        <div class="col-md-6">
            <h4 class="mb-3">Thông tin giỏ hàng</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sách</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['book']['Title']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['subtotal'], 0, ',', '.') ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Tổng cộng</strong></td>
                        <td><strong><?= number_format($total, 0, ',', '.') ?> VNĐ</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Form nhập thông tin thanh toán -->
        <div class="col-md-6">
            <h4 class="mb-3">Thông tin thanh toán</h4>
            <form action="process_checkout.php" method="POST">
                <div class="mb-3">
                    <label for="Fullname" class="form-label">Họ và tên</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="Email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="Phone" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="mb-3">
                    <label for="Address" class="form-label">Địa chỉ</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="d-flex justify-content-between mt-4">
                    <a href="../pages/cart.php" class="btn btn-secondary">Quay lại giỏ hàng</a>
                    <button type="submit" class="btn btn-success">Hoàn tất thanh toán</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
    function checkout() {
        var email = document.getElementById('email').value;
        var phone = document.getElementById('phone').value;
        var address = document.getElementById('address').value;

        var formData = new FormData();
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('address', address);

        // Gửi AJAX request đến process_checkout.php
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_checkout.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var response = JSON.parse(xhr.responseText);

                if (response.status === 'success') {
                    // Hiển thị thông báo thành công
                    alert("Thanh toán thành công! Mã đơn hàng của bạn là: " + response.orderId);

                    // Chuyển hướng về trang index
                    window.location.href = 'index.php';
                } else {
                    // Hiển thị lỗi nếu có
                    alert(response.message);
                }
            }
        };
        xhr.send(formData);
    }
</script>

