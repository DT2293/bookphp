<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

$authors = [];
$categories = [];
$suppliers = [];

// Lấy danh sách tác giả
$authorResult = $conn->query("SELECT AuthorID, Name FROM Authors");
if ($authorResult && $authorResult->rowCount() > 0) {
    while ($row = $authorResult->fetch(PDO::FETCH_ASSOC)) {
        $authors[] = $row;
    }
} else {
    echo "<p>Lỗi: Không thể lấy danh sách tác giả. Vui lòng kiểm tra cơ sở dữ liệu.</p>";
}

// Lấy danh sách danh mục
$categoryResult = $conn->query("SELECT CategoryID, CategoryName FROM Categories");
if ($categoryResult && $categoryResult->rowCount() > 0) {
    while ($row = $categoryResult->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $row;
    }
} else {
    echo "<p>Lỗi: Không thể lấy danh sách danh mục. Vui lòng kiểm tra cơ sở dữ liệu.</p>";
}

// Lấy danh sách nhà cung cấp
$suppliersResult = $conn->query("SELECT SupplierID, Name FROM suppliers");
if ($suppliersResult && $suppliersResult->rowCount() > 0) {
    while ($row = $suppliersResult->fetch(PDO::FETCH_ASSOC)) {
        $suppliers[] = $row;
    }
} else {
    echo "<p>Lỗi: Không thể lấy danh sách nhà cung cấp. Vui lòng kiểm tra cơ sở dữ liệu.</p>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu từ form
    $title = trim($_POST['title']);
    $author_id = $_POST['author_id'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $importPrice = $_POST['importPrice'];
    $published_date = $_POST['published_date'];
    $description = trim($_POST['description']);
    $cover_image_url = null;
    $supplier_id = $_POST['SupplierID'];

    // Xử lý upload ảnh bìa
    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/bookphp/assets/uploads/";
        $cover_image_name = basename($_FILES["cover_image"]["name"]);
        $cover_image_url = "assets/uploads/" . $cover_image_name;

        // Di chuyển file ảnh vào thư mục uploads
        if (!move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_dir . $cover_image_name)) {
            echo "<p>Không thể tải lên ảnh bìa. Vui lòng kiểm tra quyền thư mục.</p>";
            exit;
        }
    }

    // Kiểm tra xem tên sách đã tồn tại trong cơ sở dữ liệu chưa
    $stmt = $conn->prepare("SELECT * FROM Books WHERE Title = ?");
    $stmt->execute([$title]);
    $existingBook = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingBook) {
        $error = "Sách này đã tồn tại trong cơ sở dữ liệu.";
    } else {
        // Thêm sách vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO Books (Title, AuthorID, CategoryID, Price,ImportPrice ,PublishedDate, Description, CoverImageUrl, SupplierID) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stmt->execute([$title, $author_id, $category_id, $price,$importPrice ,$published_date, $description, $cover_image_url, $supplier_id]);

        $success = "Sách đã được thêm thành công!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sách</title>
    <!-- Thêm Bootstrap 5 từ CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
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
    <div class="container mt-5">
        <h1 class="mb-4">Thêm Sách Mới</h1>
        
        <!-- Hiển thị thông báo thành công hoặc lỗi -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form action="add_book.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Tên sách:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="author_id" class="form-label">Tác giả:</label>
                <select id="author_id" name="author_id" class="form-select" required>
                    <option value="">-- Chọn tác giả --</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= $author['AuthorID'] ?>"><?= htmlspecialchars($author['Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Danh mục:</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['CategoryID'] ?>"><?= htmlspecialchars($category['CategoryName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="importPrice" class="form-label">Giá nhập vào:</label>
                <input type="number" id="importPrice" name="importPrice" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Giá:</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="published_date" class="form-label">Ngày xuất bản:</label>
                <input type="date" id="published_date" name="published_date" class="form-control">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Mô tả:</label>
                <textarea id="description" name="description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label for="cover_image" class="form-label">Ảnh bìa:</label>
                <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
            <label for="SupplierID" class="form-label">Nhà Cung Cấp:</label>
            <select id="SupplierID" name="SupplierID" class="form-select" required>
                <option value="">-- Chọn nhà cung cấp --</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['SupplierID'] ?>"><?= htmlspecialchars($supplier['Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

            <button type="submit" class="btn btn-primary">Thêm Sách</button>
            <a href="../book/showbook.php" class="btn btn-danger">Huỷ</a>
        </form>
    </div>

    <!-- Thêm Bootstrap JS từ CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
