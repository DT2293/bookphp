<?php
session_start();
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$userLoggedIn = isset($_SESSION['user']);
if ($userLoggedIn) {
    $user = $_SESSION['user']; // Lấy thông tin người dùng đã đăng nhập
} else {
    $user = null; // Không có thông tin người dùng nếu chưa đăng nhập
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookstore</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<header class="bg-primary text-white py-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
        <h1><a href="../pages/index.php" class="bookstore-link">Bookstore</a></h1>
        <style>
            .bookstore-link {
    text-decoration: none; /* Loại bỏ gạch chân của thẻ a */
    color: inherit; /* Thừa hưởng màu sắc từ phần tử cha (trong trường hợp này là <h1>) */
}

/* Không áp dụng hiệu ứng hover */
.bookstore-link:hover {
    text-decoration: none; /* Đảm bảo không có gạch chân khi hover */
    color: inherit; /* Giữ nguyên màu sắc khi hover */
}

        </style>
            <nav>
                <ul class="nav">
                <!-- <li>
                    <?php
                    // Kiểm tra nếu người dùng đã đăng nhập
                    echo isset($user) && !empty($user['FullName']) ? htmlspecialchars($user['FullName']) : 'Guest';

                    ?>
                </li> -->

                    <li class="nav-item">
                        <a href="../pages/index.php" class="nav-link text-white">Home</a>
                    </li>
                    <!-- Giả sử bạn có một phần tử này trong header để hiển thị số lượng giỏ hàng -->

                    <!-- <li class="nav-item">
                        <a href="../pages/cart.php" class="nav-link text-white">
                            Cart <span class="badge bg-light text-primary"><?= count($_SESSION['cart']) ?></span>
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a href="../pages/cart.php" class="nav-link text-white">
                            Cart <span class="badge bg-light text-primary" id="cart-count"><?= count($_SESSION['cart']) ?></span>
                        </a>
                    </li>

                     <li class="nav-item">
                        <a class="btn btn-primary" href="../logout.php">Đăng xuất</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
<!-- Optional JavaScript and Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
