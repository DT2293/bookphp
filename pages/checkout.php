<?php
include '../includes/header.php';
include '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Get the logged-in customer's ID
$customerID = $_SESSION['CustomerID'];

// Kiểm tra nếu giỏ hàng trống
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<main class='container mt-5'><p>Giỏ hàng của bạn đang trống. <a href='../pages/cart.php'>Quay lại giỏ hàng</a>.</p></main>";
    exit;
}

// Lấy thông tin sách trong giỏ hàng
$cartItems = [];
$total = 0;
$quantityTotal = 0;  // Tổng số lượng sách trong giỏ hàng để tính giảm giá
$discountAmount = 0; // Mức giảm giá từ bảng sale

// Tính toán tổng số lượng sách và tổng tiền
foreach ($_SESSION['cart'] as $bookId => $item) {
    $stmt = $conn->prepare("SELECT * FROM books WHERE BookID = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($book) {
        $quantity = $item['quantity'];
        $price = $book['Price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        $quantityTotal += $quantity;  // Cộng dồn tổng số lượng sách trong giỏ

        $cartItems[] = [
            'book' => $book,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}

// Tính toán giảm giá dựa trên số lượng sách
$stmt = $conn->prepare("SELECT * FROM sale WHERE MinQuantity <= ? AND (MaxQuantity IS NULL OR MaxQuantity >= ?)");
$stmt->execute([$quantityTotal, $quantityTotal]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sale) {
    $discountAmount = $sale['DiscountAmount']; // Mức giảm giá từ bảng sale
    $total -= $discountAmount;  // Áp dụng mức giảm giá vào tổng tiền
} else {
    $discountAmount = 0;  // Không có mức giảm nếu không thỏa mãn điều kiện
}

// Giới thiệu thông tin giỏ hàng và mức giảm giá
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
                    <?php if ($discountAmount > 0): ?>
                        <tr>
                            <td colspan="2"><strong>Giảm giá</strong></td>
                            <td><strong>- <?= number_format($discountAmount, 0, ',', '.') ?> VNĐ</strong></td>
                        </tr>
                    <?php endif; ?>
                </tfoot>
            </table>
        </div>

        <!-- Form nhập thông tin thanh toán -->
        <div class="col-md-6">
            <h4 class="mb-3">Thông tin thanh toán</h4>
            <form id="checkout-form">
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
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        e.preventDefault(); // Ngừng gửi form bình thường

        var formData = new FormData(this);

        // Gửi yêu cầu AJAX đến server
        fetch('process_checkout.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Hiển thị thông báo thành công
                alert("Thanh toán thành công! Mã đơn hàng của bạn là: " + data.orderId);
                // Chuyển hướng về trang index
                window.location.href = '../pages/index.php';
            } else {
                // Hiển thị thông báo lỗi
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Có lỗi xảy ra:', error);
            alert('Đã xảy ra lỗi khi thanh toán.');
        });
    });
</script> 

