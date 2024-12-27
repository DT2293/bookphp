<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Kiểm tra nếu có `BookID` được gửi qua URL
if (!isset($_GET['id'])) {
    echo "Không tìm thấy sách.";
    exit;
}

$bookId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM Books WHERE BookID = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo "Sách không tồn tại.";
    exit;
}

// Xử lý cập nhật thông tin khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['Title']);
    $authorId = intval($_POST['AuthorID']);
    $categoryId = intval($_POST['CategoryID']);
    $price = floatval($_POST['Price']);
    $publishedDate = $_POST['PublishedDate'];
    $description = trim($_POST['Description']);
    $coverImageUrl = $book['CoverImageUrl']; // Giữ lại ảnh cũ mặc định

    // Kiểm tra nếu có file ảnh được upload
    if (!empty($_FILES['CoverImage']['name'])) {

        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/bookphp/assets/uploads/";
        $cover_image_name = basename($_FILES["cover_image"]["name"]);
        $cover_image_url = "assets/uploads/" . $cover_image_name;

        // $targetDir = "../bookphp/assets/uploads/";
        // $targetFile = $targetDir . basename($_FILES['CoverImage']['name']);
        // $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Kiểm tra định dạng file
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['CoverImage']['tmp_name'], $targetFile)) {
                $coverImageUrl = $targetFile; // Cập nhật đường dẫn ảnh mới
            } else {
                $error = "Không thể tải ảnh bìa lên.";
            }
        } else {
            $error = "Chỉ chấp nhận các định dạng ảnh JPG, JPEG, PNG, GIF.";
        }
    }

    // Kiểm tra dữ liệu
    if (empty($title) || empty($authorId) || empty($categoryId)) {
        $error = "Tiêu đề, Tác giả và Thể loại không được để trống.";
    } else {
        // Thực hiện cập nhật
        $updateStmt = $conn->prepare("UPDATE Books 
            SET Title = ?, AuthorID = ?, CategoryID = ?, Price = ?, PublishedDate = ?, Description = ?, CoverImageUrl = ? 
            WHERE BookID = ?");
        $updateStmt->execute([$title, $authorId, $categoryId, $price, $publishedDate, $description, $coverImageUrl, $bookId]);

        // Chuyển hướng sau khi cập nhật thành công
        header("Location: showbook.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật sách</title>
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
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <div class="d-flex gap-4">
            <a href="../admin.php" class="text-decoration-none text-dark hover-link">Home</a>
            <a href="../statistical/statistical.php" class="text-decoration-none text-dark hover-link">Quản lý Thống kê</a>
            <a href="../../dashboard/user/showuser.php" class="text-decoration-none text-dark hover-link">Quản lý người dùng</a>
            <a href="../../dashboard/book/showbook.php" class="text-decoration-none text-dark hover-link">Quản lý Sách</a>
            <a href="../orders/showorders.php" class="text-decoration-none text-dark hover-link">Quản lý hóa đơn</a>
        </div>
        <a class="btn btn-danger" href="../../logout.php">Đăng xuất</a>
    </div>
</div>    
<div class="container my-5">
    <h2 class="mb-4">Cập nhật thông tin sách</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="Title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="Title" name="Title" value="<?php echo htmlspecialchars($book['Title']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="AuthorID" class="form-label">Tác giả</label>
            <select class="form-select" id="AuthorID" name="AuthorID" required>
                <?php
                $authorsStmt = $conn->query("SELECT AuthorID, Name FROM Authors");
                while ($author = $authorsStmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $author['AuthorID'] == $book['AuthorID'] ? 'selected' : '';
                    echo "<option value='{$author['AuthorID']}' $selected>{$author['Name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="CategoryID" class="form-label">Thể loại</label>
            <select class="form-select" id="CategoryID" name="CategoryID" required>
                <?php
                $categoriesStmt = $conn->query("SELECT CategoryID, CategoryName FROM Categories");
                while ($category = $categoriesStmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = $category['CategoryID'] == $book['CategoryID'] ? 'selected' : '';
                    echo "<option value='{$category['CategoryID']}' $selected>{$category['CategoryName']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="Price" class="form-label">Giá</label>
            <input type="number" step="0.01" class="form-control" id="Price" name="Price" value="<?php echo htmlspecialchars($book['Price']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="PublishedDate" class="form-label">Ngày xuất bản</label>
            <input type="date" class="form-control" id="PublishedDate" name="PublishedDate" value="<?php echo htmlspecialchars($book['PublishedDate']); ?>">
        </div>

        <div class="mb-3">
            <label for="Description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="Description" name="Description"><?php echo htmlspecialchars($book['Description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="CoverImage" class="form-label">Ảnh bìa</label>
            <input type="file" class="form-control" id="CoverImage" name="CoverImage">
            <img src="<?php echo htmlspecialchars($book['CoverImageUrl']); ?>" alt="Ảnh bìa" class="mt-3" style="max-width: 150px;">
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="showbook.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
</body>

</html>
