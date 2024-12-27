<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Xử lý tìm kiếm
$searchQuery = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM customers WHERE FullName LIKE ? OR Email LIKE ?");
    $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
} else {
    // Nếu không tìm kiếm, hiển thị tất cả người dùng
    $stmt = $conn->prepare("SELECT * FROM customers WHERE Role = 'Admin'");

    $stmt->execute();
}

// Lấy kết quả
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <a href="../../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../book/showbook.php   " class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>
</header>  
<div class="row mb-3 mt-3">
    <!-- Cột 1: Form tìm kiếm -->
    <div class="col-md-5">
        <form method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="search" 
                       placeholder="Tìm kiếm người dùng theo tên hoặc email"
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="btn btn-primary">Tìm</button>
            </div>
        </form>
    </div>

    <!-- Cột 2: Tiêu đề -->
    <div class="col-md-3 text-center d-flex align-items-center justify-content-center">
        <h5 class="mb-0">Quản lý người dùng:</h5>
    </div>

    <!-- Cột 3: Nút điều hướng -->
    <div class="col-md-4 text-end d-flex justify-content-end gap-3">
        <a href="showuser_cus.php" class="btn btn-outline-primary">Khách hàng</a>
        <a href="add_user.php" class="btn btn-success">Thêm người dùng</a>
    </div>
</div>

        <!-- Bảng danh sách người dùng -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Tên đầy đủ</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['CustomerID']); ?></td>
                            <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td><?php echo htmlspecialchars($user['Role']); ?></td>
                            <td><?php echo htmlspecialchars($user['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($user['Address']); ?></td>
                            <td>
                                <!-- Nút Sửa -->
                                <a href="edit_user.php?id=<?php echo $user['CustomerID']; ?>" class="btn btn-primary btn-sm">
                                    Sửa
                                </a>
                                <!-- Nút Xóa với Modal -->
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $user['CustomerID']; ?>">
                                    Xóa
                                </button>

                                <!-- Modal Xóa -->
                                <div class="modal fade" id="deleteModal<?php echo $user['CustomerID']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel">Xóa người dùng</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Bạn có chắc chắn muốn xóa người dùng <strong><?php echo htmlspecialchars($user['FullName']); ?></strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                <a href="delete_user.php?id=<?php echo $user['CustomerID']; ?>" class="btn btn-danger">Xóa</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">Không tìm thấy người dùng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
