<?php
include '../../includes/db.php';
session_start();

// Kiểm tra nếu người dùng chưa đăng nhập hoặc không phải admin
if (!isset($_SESSION['CustomerID']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../auth/login_page.php");
    exit;
}

// Xử lý dữ liệu từ form
$startDate = $_POST['startDate'] ?? '2024-01-01'; // Giá trị mặc định
$endDate = $_POST['endDate'] ?? '2024-12-30'; // Giá trị mặc định

$data = [];
$labels = [];
$revenues = [];

try {

     // Tính tổng số tiền đã mua sách
     $stmt = $conn->prepare("SELECT SUM(Quantity * PricePerUnit) AS TotalImport FROM stock_transactions WHERE TransactionType = 'Import'");
     $stmt->execute();
     $totalImportCost = $stmt->fetchColumn() ?? 0;
 
     // Tính tổng doanh thu bán sách
     $stmt = $conn->prepare("SELECT SUM(TotalAmount) AS TotalRevenue FROM orders WHERE OrderDate BETWEEN :startDate AND :endDate");
     $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
     $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
     $stmt->execute();
     $totalRevenue = $stmt->fetchColumn() ?? 0;
     
     $stmt = $conn->prepare("SELECT 
                                SUM(oi.Quantity * (oi.UnitPrice - b.ImportPrice)) AS TotalProfit
                            FROM 
                                orders o
                            JOIN 
                                orderitems oi ON o.OrderID = oi.OrderID
                            JOIN 
                                books b ON oi.BookID = b.BookID
                           ");  // Chỉ tính các đơn hàng đã giao
    $stmt->execute();

    // Lấy kết quả tổng lợi nhuận
    $profit = $stmt->fetchColumn() ?? 0;
    // Gọi thủ tục GetRevenueStatistics
    $stmt = $conn->prepare("CALL GetRevenueStatistics(:startDate, :endDate)");
    $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
    $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
    $stmt->execute();

    // Lấy dữ liệu từ kết quả trả về
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Chuẩn bị dữ liệu cho Chart.js
    foreach ($data as $row) {
        $labels[] = $row['OrderDate']; // Ngày
        $revenues[] = $row['TotalRevenue']; // Doanh thu
    }
} catch (PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biểu đồ doanh thu</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<div class="container-header">
    <div class="container d-flex justify-content-between align-items-center">
        <p class="mb-0">
            <strong>Xin chào, <?php echo htmlspecialchars($_SESSION['FullName'] ?? 'Admin'); ?></strong>
        </p>
        <nav>
            <ul class="nav">
                <li class="nav-item">
                    <a href="../admin.php" class="nav-link text-dark hover-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="" class="nav-link text-dark hover-link">Quản lý Thống kê</a>
                </li>
                <li class="nav-item">
                    <a href="../user/showuser.php" class="nav-link text-dark hover-link">Quản lý người dùng</a>
                </li>
                <li class="nav-item">
                    <a href="../book/showbook.php" class="nav-link text-dark hover-link">Quản lý Sách</a>
                </li>
                <li class="nav-item">
                    <a href="../orders/showorders.php" class="nav-link text-dark hover-link">Quản lý hóa đơn</a>
                </li>
                <li class="nav-item">
                    <a href="../stock/stock.php" class="nav-link text-dark hover-link">Quản lý kho hàng</a>
                </li>
            </ul>
        </nav>
        <a href="../../logout.php" class="btn btn-danger btn-sm btn-logout">Đăng xuất</a>
    </div>
</div>
<div class="container mt-5">
    <div class="row justify-content-center">
        <!-- Thẻ 1: Tổng số tiền đã mua sách -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Tổng số tiền đã mua sách</div>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo number_format($totalImportCost, 0, ',', '.') . " VND"; ?>
                    </h5>
                </div>
            </div>
        </div>

        <!-- Thẻ 2: Tổng doanh thu bán sách -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Tổng doanh thu bán sách</div>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo number_format($totalRevenue, 0, ',', '.') . " VND"; ?>
                    </h5>
                </div>
            </div>
        </div>

        <!-- Thẻ 3: Số tiền lời -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Số tiền lời</div>
                <div class="card-body">
                    <h5 class="card-title">
                        <?php echo number_format($profit, 0, ',', '.') . " VND"; ?>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="container mt-5">
    <h2 class="text-center mb-4">Biểu đồ doanh thu</h2>
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="startDate" class="form-label">Ngày bắt đầu</label>
                <input type="date" id="startDate" name="startDate" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>" required>
            </div>
            <div class="col-md-4">
                <label for="endDate" class="form-label">Ngày kết thúc</label>
                <input type="date" id="endDate" name="endDate" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Xem thống kê</button>
            </div>
        </div>
    </form>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Dữ liệu từ PHP
    const labels = <?php echo json_encode($labels); ?>;
    const revenues = <?php echo json_encode($revenues); ?>;

    // Vẽ biểu đồ
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VND)',
                data: revenues,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw.toLocaleString('vi-VN') + " VND";
                            return `${context.dataset.label}: ${value}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ngày'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Doanh thu (VND)'
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
