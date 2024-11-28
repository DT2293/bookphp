<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}
// Kiểm tra nếu có `CustomerID` được gửi qua URL
if (!isset($_GET['id'])) {
    echo "Không tìm thấy người dùng.";
    exit;
}

$customerId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM customers WHERE CustomerID = ?");
$stmt->execute([$customerId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Người dùng không tồn tại.";
    exit;
}

// Xử lý cập nhật thông tin khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['FullName']);
    $email = trim($_POST['Email']);
    $phone = trim($_POST['Phone']);
    $address = trim($_POST['Address']);
    $role = $_POST['Role'];

    // Kiểm tra dữ liệu
    if (empty($fullName) || empty($email)) {
        $error = "Tên đầy đủ và Email không được để trống.";
    } else {
        // Thực hiện cập nhật
        $updateStmt = $conn->prepare("UPDATE customers SET FullName = ?, Email = ?, Phone = ?, Address = ?, Role = ? WHERE CustomerID = ?");
        $updateStmt->execute([$fullName, $email, $phone, $address, $role, $customerId]);

        // Chuyển hướng sau khi cập nhật thành công
        header("Location: showuser.php?success=1");
        exit;
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
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
            <a href="../user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../book/showbook.php" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
        </div>

        <!-- Nút đăng xuất -->
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
        </div>
    </div>
</div>
<div class="container my-5">
    <h2 class="mb-4">Sửa thông tin người dùng</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="FullName" class="form-label">Tên đầy đủ</label>
            <input type="text" class="form-control" id="FullName" name="FullName" 
                   value="<?php echo htmlspecialchars($user['FullName']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="Email" class="form-label">Email</label>
            <input type="email" class="form-control" id="Email" name="Email" 
                   value="<?php echo htmlspecialchars($user['Email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="Phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="Phone" name="Phone" 
                   value="<?php echo htmlspecialchars($user['Phone']); ?>">
        </div>

        <div class="mb-3">
            <label for="Address" class="form-label">Địa chỉ</label>
            <textarea class="form-control" id="Address" name="Address"><?php echo htmlspecialchars($user['Address']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="Role" class="form-label">Vai trò</label>
            <select class="form-select" id="Role" name="Role">
                <option value="customer" <?php echo $user['Role'] === 'Customer' ? 'selected' : ''; ?>>Khách hàng</option>
                <option value="admin" <?php echo $user['Role'] === 'Admin' ? 'selected' : ''; ?>>Quản trị viên</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="showuser.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
</body>

</html>
