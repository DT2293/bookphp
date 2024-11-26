<?php
include '../../includes/db.php';
session_start();

if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

$bookName = isset($_GET['bookName']) ? trim($_GET['bookName']) : '';
$authorID = isset($_GET['author']) ? $_GET['author'] : '';
$categoryID = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT b.*, a.Name AS AuthorName, c.CategoryName 
        FROM Books b
        LEFT JOIN Authors a ON b.AuthorID = a.AuthorID
        LEFT JOIN Categories c ON b.CategoryID = c.CategoryID
        WHERE 1";
$params = [];

if ($bookName !== '') {
    $sql .= " AND b.Title LIKE ?";
    $params[] = "%$bookName%";
}

if ($authorID !== '') {
    $sql .= " AND b.AuthorID = ?";
    $params[] = $authorID;
}

if ($categoryID !== '') {
    $sql .= " AND b.CategoryID = ?";
    $params[] = $categoryID;
}

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn cơ sở dữ liệu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Sách</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Danh sách sách</h2>
    <form method="GET" action="showbook.php" class="mb-4">
        <div class="row mb-3 d-flex align-items-center">
            <div class="col-md-3">
                <input type="text" class="form-control" id="bookName" name="bookName" placeholder="Nhập tên sách..." value="<?php echo htmlspecialchars($bookName); ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="author" name="author">
                    <option value="">Tất cả tác giả</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM Authors");
                    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($authors as $author) {
                        echo "<option value='" . $author['AuthorID'] . "' " . ($author['AuthorID'] == $authorID ? 'selected' : '') . ">" . htmlspecialchars($author['Name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="category" name="category">
                    <option value="">Tất cả thể loại</option>
                    <?php
                    $stmt = $conn->query("SELECT * FROM Categories");
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $category) {
                        echo "<option value='" . $category['CategoryID'] . "' " . ($category['CategoryID'] == $categoryID ? 'selected' : '') . ">" . htmlspecialchars($category['CategoryName']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Tiêu đề</th>
                <th>Tác giả</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Ngày xuất bản</th>
                <th>Mô tả</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['Title']); ?></td>
                        <td><?php echo htmlspecialchars($book['AuthorName']); ?></td>
                        <td><?php echo htmlspecialchars($book['CategoryName']); ?></td>
                        <td><?php echo number_format($book['Price'], 2); ?> VND</td>
                        <td><?php echo htmlspecialchars($book['PublishedDate']); ?></td>
                        <td><?php echo htmlspecialchars($book['Description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">Không tìm thấy sách nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
