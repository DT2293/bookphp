<?php
// Bao gồm tệp db.php để kết nối cơ sở dữ liệu
include '../includes/db.php';

// Lấy dữ liệu từ form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = 'customer'; // Giá trị mặc định cho vai trò người dùng

    try {
        // Kiểm tra email có tồn tại không
        $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // if ($stmt->rowCount() > 0) {
        //     // Email đã tồn tại
        //     header("Location: register_form.php?error=Email đã tồn tại!");
        //     exit();
        // } 
        if ($stmt->rowCount() > 0) {
            
         
           header("Location: register_page.php?error=Email đã tồn tại");
           exit();
        }
        
        else {
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
                header("Location: login.php?success=Đăng ký thành công!");
                exit();
            } else {
                header("Location: register_form.php?error=Đã xảy ra lỗi. Vui lòng thử lại!");
                exit();
            }
        }
    } catch (PDOException $e) {
        header("Location: register_form.php?error=Lỗi hệ thống: " . htmlspecialchars($e->getMessage()));
        exit();
    }
}
?>
