<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = 'customer'; // Vai trò mặc định

    try {
        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Email đã tồn tại, trả về với thông báo lỗi
            header("Location: register_form.php?error=" . urlencode("Email đã tồn tại. Vui lòng sử dụng email khác!"));
            exit();
        }

        // Mã hóa mật khẩu
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Thêm người dùng mới
        $stmt = $conn->prepare("INSERT INTO customers (FullName, Email, Password, Phone, Address, Role) VALUES (:fullname, :email, :password, :phone, :address, :role)");
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            // Đăng ký thành công, chuyển về trang đăng nhập
            header("Location: login.php?success=" . urlencode("Đăng ký thành công! Vui lòng đăng nhập."));
            exit();
        } else {
            // Đăng ký thất bại
            header("Location: register_form.php?error=" . urlencode("Đã xảy ra lỗi. Vui lòng thử lại sau."));
            exit();
        }
    } catch (PDOException $e) {
        // Xử lý lỗi hệ thống
        header("Location: register_form.php?error=" . urlencode("Lỗi hệ thống: " . $e->getMessage()));
        exit();
    }
}
?>
