<!-- <?php
session_start();
include '../includes/db.php'; // Kết nối cơ sở dữ liệu
// Hàm xử lý đăng nhập
function login($email, $password, $conn) {
    // Kiểm tra thông tin đăng nhập
    $stmt = $conn->prepare("SELECT * FROM Customers WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    //Kiểm tra nếu người dùng tồn tại và mật khẩu khớp
    // if ($user && $password === $user['Password']) {
    //     // Lưu thông tin vào session
    //     $_SESSION['CustomerID'] = $user['CustomerID'];
    //     $_SESSION['role'] = $user['Role'];

    //     // Phân quyền và chuyển hướng
    //     if ($user['Role'] === 'Admin') {
    //         header("Location: ../dashboard/admin.php"); // Chuyển hướng đến trang admin
    //     } else {
    //         header("Location: ../pages/index.php"); // Chuyển hướng đến trang user
    //     }
    //     exit; // Dừng thực thi mã sau khi chuyển hướng
    // } else {
    //     return "Email hoặc mật khẩu không đúng.";
    // }
    //  // Kiểm tra nếu người dùng tồn tại và mật khẩu khớp
     if ($user && password_verify($password, $user['Password'])) { // Sử dụng password_verify
        // Lưu thông tin vào session
        $_SESSION['CustomerID'] = $user['CustomerID'];
        $_SESSION['role'] = $user['Role'];

        // Phân quyền và chuyển hướng
        if ($user['Role'] === 'Admin') {
            header("Location: ../dashboard/admin.php"); // Chuyển hướng đến trang admin
        } else {
            header("Location: ../pages/index.php"); // Chuyển hướng đến trang user
        }
        exit; // Dừng thực thi mã sau khi chuyển hướng
    } else {
        return "Email hoặc mật khẩu không đúng.";
    }
}

// Xử lý khi người dùng gửi biểu mẫu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Gọi hàm login và lấy lỗi nếu có
    $error = login($email, $password, $conn);
    if ($error) {
        // Chuyển hướng về trang login với thông báo lỗi
        header("Location: login_page.php?error=" . urlencode($error));
        exit;
    }
}
?> -->

