<?php
include '../includes/header.php';
include '../includes/db.php';

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['CustomerID'])) {
    header("Location: ../auth/login_page.php");
    exit;
}

// Get the logged-in customer's ID
$customerID = $_SESSION['CustomerID'];

try {
    // Prepare the SQL query to fetch orders for the logged-in user
    $sql = "
        SELECT 
            o.OrderID,
            o.OrderDate,
            o.TotalAmount,
            o.Status
        FROM 
            orders o
        WHERE 
            o.CustomerID = :customerID
        ORDER BY 
            o.OrderDate DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->execute();

    // Fetch all orders
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
    exit;
}
?>
<main class="container my-4">
    <h2>Đơn hàng của bạn</h2>
    <?php if (!empty($orders)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Mã Đơn Hàng</th>
                    <th>Ngày Đặt Hàng</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['OrderID']); ?></td>
                        <td><?php echo htmlspecialchars(date("d-m-Y H:i", strtotime($row['OrderDate']))); ?></td>
                        <td><?php echo htmlspecialchars(number_format($row['TotalAmount'], 0)); ?> VND</td>
                        <td>
                            <?php if ($row['Status'] === 'Ship'): ?>
                                <form method="POST" action="edit_order.php" style="display: inline;">
                                    <input type="hidden" name="OrderID" value="<?php echo $row['OrderID']; ?>">
                                    <button type="submit" name="edit" class="btn btn-warning btn-sm">Sửa</button>
                                </form>
                                <form method="POST" action="delete_order.php" style="display: inline;">
                                    <input type="hidden" name="OrderID" value="<?php echo $row['OrderID']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Xóa</button>
                                </form>
                            <?php elseif ($row['Status'] === 'Shipped'): ?>
                                <span class="badge bg-success">Shipped</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($row['Status']); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Bạn chưa có đơn hàng nào.</p>
    <?php endif; ?>
</main>
