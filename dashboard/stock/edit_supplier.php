<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu có SupplierID trong URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Mã nhà cung cấp không hợp lệ.";
    exit;
}

$supplierID = $_GET['id'];

// Truy vấn để lấy thông tin nhà cung cấp hiện tại
$sql = "SELECT * FROM suppliers WHERE SupplierID = :supplierID";
$stmt = $conn->prepare($sql);
$stmt->execute(['supplierID' => $supplierID]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu không tìm thấy nhà cung cấp
if (!$supplier) {
    echo "Không tìm thấy nhà cung cấp.";
    exit;
}

// Xử lý cập nhật khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $contactInfo = $_POST['contact_info'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Cập nhật thông tin nhà cung cấp trong cơ sở dữ liệu
    $updateSql = "UPDATE suppliers SET 
                    Name = :name, 
                    ContactInfo = :contactInfo, 
                    Address = :address, 
                    Email = :email, 
                    Phone = :phone
                  WHERE SupplierID = :supplierID";

    $stmt = $conn->prepare($updateSql);
    $result = $stmt->execute([
        'name' => $name,
        'contactInfo' => $contactInfo,
        'address' => $address,
        'email' => $email,
        'phone' => $phone,
        'supplierID' => $supplierID,
    ]);

    if ($result) {
        $_SESSION['success_message'] = "Cập nhật nhà cung cấp thành công!";
        header("Location: stock.php");
        exit;
    } else {
        echo "Có lỗi xảy ra khi cập nhật thông tin.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật nhà cung cấp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
<header>
 <div class="bg-light py-2 px-3">
  <div class="container py-2 bg-light rounded shadow-sm">
    <div class="d-flex align-items-center justify-content-between">
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <div class="d-flex gap-4">
        <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
                <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
                <a href="../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
                <a href="../book/showbook.php   " class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
                <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản đơn hàng</a>
                <a href="../stock/stock.php" class="text-decoration-none text-dark hover-link">Quản Kho hàng</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
</header>
<div class="container mt-5">
    <h1 class="mb-4">Cập nhật nhà cung cấp</h1>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Tên nhà cung cấp</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($supplier['Name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="contact_info" class="form-label">Thông tin liên hệ</label>
            <input type="text" class="form-control" id="contact_info" name="contact_info" value="<?= htmlspecialchars($supplier['ContactInfo']) ?>">
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($supplier['Address']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($supplier['Email']) ?>">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($supplier['Phone']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="show_suppliers.php" class="btn btn-secondary">Hủy</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
