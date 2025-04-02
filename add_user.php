<?php
// شروع جلسه
session_start();

// بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// اتصال به پایگاه داده
require_once 'includes/db.php';

// دریافت لیست نقش‌ها
try {
    $stmt = $pdo->query("SELECT id, name FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "خطا در دریافت نقش‌ها: " . $e->getMessage();
    header('Location: manage_users.php');
    exit;
}

// پردازش فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation و Sanitization
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
        $role_id = (int)filter_input(INPUT_POST, 'role', FILTER_VALIDATE_INT);

        if (empty($username) || empty($password) || empty($full_name) || empty($role_id)) {
            throw new Exception("لطفاً تمام فیلدها را پر کنید.");
        }

        // بررسی تکراری بودن نام کاربری
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("نام کاربری قبلاً ثبت شده است.");
        }

        // هش کردن رمز عبور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // اضافه کردن کاربر جدید
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashed_password, $full_name]);

        // دریافت ID کاربر اضافه‌شده
        $user_id = $pdo->lastInsertId();

        // اضافه کردن نقش کاربر
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $role_id]);

        $_SESSION['success'] = "کاربر با موفقیت اضافه شد.";
        header('Location: manage_users.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: add_user.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>افزودن کاربر جدید</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dark-theme">
    <!-- نوتیفیکیشن -->
    <div class="notification-container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php elseif (isset($_SESSION['success'])): ?>
            <div class="notification success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </div>

    <main class="content">
        <div class="form-container glass-effect">
            <header class="header">
                <div class="title-container">
                    <i class="fas fa-user-plus title-icon"></i>  
                    <h2 class="title">افزودن کاربر جدید</h2>
                </div>
            </header>

            <form method="POST" class="styled-form">
                <div class="form-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" name="username" required placeholder="نام کاربری را وارد کنید">
                </div>

                <div class="form-group">
                    <label for="password">رمز عبور:</label>
                    <input type="password" name="password" required placeholder="رمز عبور را وارد کنید">
                </div>

                <div class="form-group">
                    <label for="full_name">نام و نام خانوادگی:</label>
                    <input type="text" name="full_name" placeholder="نام و نام خانوادگی را وارد کنید">
                </div>

                <div class="form-group">
                    <label for="role">نقش:</label>
                    <select name="role" id="role" required>
                        <option value="" disabled selected>انتخاب نقش</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['id']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="button-group">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> ذخیره
                    </button>
                    <a href="manage_users.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> بازگشت
                    </a>
        </div>
    </main>

    <script src="js/script.js"></script>
</body>
</html>