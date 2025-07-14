<?php
include '../../includes/db.php';
session_start();

// Redirect to login if the user is not logged in or doesn't have Admin role
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

$bookName = isset($_GET['bookName']) ? trim($_GET['bookName']) : '';
$authorID = isset($_GET['author']) ? (int)$_GET['author'] : '';
$categoryID = isset($_GET['category']) ? (int)$_GET['category'] : '';

// Query to fetch the total stock for a book
$bookId = isset($_GET['bookId']) ? (int)$_GET['bookId'] : 0; // Ensure bookId is valid

$totalStock = 0;
if ($bookId > 0) {
    $stmt = $conn->prepare("
        SELECT SUM(Quantity) AS TotalStock
        FROM stock
        WHERE BookID = :bookId
    ");
    $stmt->bindParam(':bookId', $bookId, PDO::PARAM_INT);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalStock = $stock['TotalStock'] ?? 0; // Default to 0 if no stock found
}

// Constructing the main query to fetch book details
$sql = "
    SELECT 
        b.BookID, b.Title, 
        SUM(s.Quantity) AS TotalStock, 
        a.Name AS AuthorName, 
        c.CategoryName, 
        b.ImportPrice, 
        b.Price, 
        b.PublishedDate, 
        b.Description
    FROM Books b
    LEFT JOIN Authors a ON b.AuthorID = a.AuthorID
    LEFT JOIN Categories c ON b.CategoryID = c.CategoryID
    LEFT JOIN Stock s ON b.BookID = s.BookID
    WHERE 1
";

// Prepare the query parameters
$params = [];

if ($bookName !== '') {
    $sql .= " AND b.Title LIKE :bookName";
    $params[':bookName'] = "%$bookName%";
}

if ($authorID > 0) {
    $sql .= " AND b.AuthorID = :authorID";
    $params[':authorID'] = $authorID;
}

if ($categoryID > 0) {
    $sql .= " AND b.CategoryID = :categoryID";
    $params[':categoryID'] = $categoryID;
}

$sql .= " GROUP BY b.BookID"; // Ensure grouping by BookID to calculate total stock for each book

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <h2 class="mb-4">Danh sách sách</h2>
    <form method="GET" action="showbook.php" class="mb-4">
        <div class="row mb-3 d-flex align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control" id="bookName" name="bookName" placeholder="Nhập tên sách..." value="<?php echo htmlspecialchars($bookName); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="author" name="author">
                    <option value="">Tất cả tác giả</option>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM Authors");
                    $stmt->execute();
                    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($authors as $author) {
                        echo "<option value='" . $author['AuthorID'] . "' " . ($author['AuthorID'] == $authorID ? 'selected' : '') . ">" . htmlspecialchars($author['Name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="category" name="category">
                    <option value="">Tất cả thể loại</option>
                    <?php
                    $stmt = $conn->prepare("SELECT * FROM Categories");
                    $stmt->execute();
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $category) {
                        echo "<option value='" . $category['CategoryID'] . "' " . ($category['CategoryID'] == $categoryID ? 'selected' : '') . ">" . htmlspecialchars($category['CategoryName']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
            </div>
            <div class="col-md-2">
                <a href="add_book.php" class="btn btn-success w-100">Thêm Sách</a>
            </div>
            <div class="col-md-2">
                <a href="../stock/order_supplier.php" class="btn btn-primary w-100">Đặt hàng</a>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
    <thead class="table-dark">
    <tr>
        <th>Tiêu đề</th>
        <th>Tác giả</th>
        <th>Giá nhập</th>
        <th>Giá bán</th>
        <th>Ngày xuất bản</th>
        <th>Số lượng trong kho</th>
        <th>Mô tả</th>
        <th>Hành động</th>
    </tr>
</thead>

    <tbody>
    <?php if (count($books) > 0): ?>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?php echo htmlspecialchars($book['Title']); ?></td>
                <td><?php echo htmlspecialchars($book['AuthorName']); ?></td>
                <td><?php echo number_format($book['ImportPrice'], 2); ?> VND</td>
                <td><?php echo number_format($book['Price'], 2); ?> VND</td>
                <td><?php echo htmlspecialchars($book['PublishedDate']); ?></td>
                <td><?php echo htmlspecialchars($book['TotalStock'] ?? 0); ?></td>
                <td><?php echo htmlspecialchars($book['Description']); ?></td>
                <td>
                    <a href="edit_book.php?id=<?php echo $book['BookID']; ?>" class="btn btn-primary btn-sm">Sửa</a>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $book['BookID']; ?>">Xóa</button>

                    <!-- Modal Xóa -->
                    <div class="modal fade" id="deleteModal<?php echo $book['BookID']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel">Xóa</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Bạn có chắc chắn muốn xóa sách <strong><?php echo htmlspecialchars($book['Title']); ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                    <a href="delete_book.php?id=<?php echo $book['BookID']; ?>" class="btn btn-danger">Xóa</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="ShowComment.php?id=<?php echo $book['BookID']; ?>" class="btn btn-primary btn-sm">Bình luận</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="text-center">Không tìm thấy sách nào.</td>
        </tr>
    <?php endif; ?>
</tbody>

       
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
