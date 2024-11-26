<?php include '../includes/db.php'; ?>
<?php
// Kết nối cơ sở dữ liệu

// Nhận dữ liệu từ form tìm kiếm
$bookName = isset($_GET['bookName']) ? trim($_GET['bookName']) : '';
$authorID = isset($_GET['author']) ? trim($_GET['author']) : '';
$categoryID = isset($_GET['category']) ? trim($_GET['category']) : '';

// Câu truy vấn SQL
$sql = "SELECT * FROM Books WHERE 1"; // Điều kiện tìm kiếm mặc định
$params = [];

// Thêm điều kiện tìm kiếm theo tên sách nếu có
if ($bookName !== '') {
    $sql .= " AND Title LIKE ?";
    $params[] = "%$bookName%";
}

// Thêm điều kiện tìm kiếm theo tác giả nếu có
if ($authorID !== '') {
    $sql .= " AND AuthorID = ?";
    $params[] = $authorID;
}

// Thêm điều kiện tìm kiếm theo thể loại nếu có
if ($categoryID !== '') {
    $sql .= " AND CategoryID = ?";
    $params[] = $categoryID;
}

// Thực thi câu truy vấn
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả tìm kiếm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2>Kết quả tìm kiếm</h2>

        <?php if ($books): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tên sách</th>
                        <th>Tác giả</th>
                        <th>Thể loại</th>
                        <th>Giá</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= htmlspecialchars($book['Title']) ?></td>
                            <td>
                                <?php
                                // Lấy tên tác giả từ AuthorID
                                $authorStmt = $conn->prepare("SELECT Name FROM Authors WHERE AuthorID = ?");
                                $authorStmt->execute([$book['AuthorID']]);
                                $author = $authorStmt->fetch(PDO::FETCH_ASSOC);
                                echo htmlspecialchars($author['Name']);
                                ?>
                            </td>
                            <td>
                                <?php
                                // Lấy tên thể loại từ CategoryID
                                $categoryStmt = $conn->prepare("SELECT CategoryName FROM Categories WHERE CategoryID = ?");
                                $categoryStmt->execute([$book['CategoryID']]);
                                $category = $categoryStmt->fetch(PDO::FETCH_ASSOC);
                                echo htmlspecialchars($category['CategoryName']);
                                ?>
                            </td>
                            <td><?= number_format($book['Price'], 2) ?> VND</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Không tìm thấy sách nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
