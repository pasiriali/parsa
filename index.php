<?php
session_start(); // شروع جلسه

// اتصال به پایگاه داده
require_once 'includes/db.php';

// بررسی وجود پیغام خطا یا موفقیت در Session
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : "";
$success = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : "";

// پاک کردن پیغام‌ها پس از نمایش
unset($_SESSION['login_error']);
unset($_SESSION['login_success']);

// پردازش فرم لاگین
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // جستجوی کاربر در پایگاه داده
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ثبت آخرین ورود کاربر
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);

        // ذخیره اطلاعات کاربر در جلسه
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        
        // ذخیره پیغام موفقیت در Session
        $_SESSION['login_success'] = "ورود موفقیت‌آمیز بود!";
        header('Location: dashboard.php'); // بازگشت به صفحه لاگین
        exit;
    } else {
        // ذخیره پیغام خطا در Session
        $_SESSION['login_error'] = "نام کاربری یا رمز عبور اشتباه است!";
        header('Location: index.php'); // بازگشت به صفحه لاگین
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به سیستم</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <!-- کانتینر نوتیفیکیشن -->
    <div class="notification-container" id="notification-container">
        <?php if (!empty($error)): ?>
            <div id="error-notification" class="notification error show">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (!empty($success)): ?>
            <div id="success-notification" class="notification success show">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
    </div>

    <main class="content">
        <div class="login-container">
            <!-- مربع لوگو -->
            <div class="logo-box">
                <img src="images/logo.png" alt="لوگو" class="logo">
            </div>

            <!-- مربع فرم لاگین -->
            <div class="form-box">
                <!-- فرم لاگین -->
                <form method="POST" action="">
                    <label for="username">نام کاربری</label>
                    <input type="text" id="username" name="username" placeholder="نام کاربری" required>
                    
                    <label for="password">رمز عبور</label>
                    <input type="password" id="password" name="password" placeholder="رمز عبور" required>
                    
                    <button type="submit">ورود</button>
                </form>
            </div>
        </div>
    </main>

    <!-- نوار پایین (تم تاریک) -->
    <footer class="footer">
        <div class="dark-mode-toggle">
            <label class="switch">
                <input type="checkbox" id="dark-mode-checkbox">
                <span class="slider"></span>
            </label>
            <span id="dark-mode-label">تم تاریک</span>
        </div>
    </footer>

    <!-- لینک جاوااسکریپت -->
    <script src="js/script.js"></script>
    <!-- انیمیشن ظهور -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // مدیریت تم تاریک
            const darkModeCheckbox = document.getElementById('dark-mode-checkbox');
            const body = document.body;

            // بررسی حالت ذخیره‌شده در localStorage
            const savedDarkMode = localStorage.getItem('darkMode');
            if (savedDarkMode === 'enabled') {
                body.classList.add('dark-mode');
                darkModeCheckbox.checked = true;
            }

            // تغییر تم با کلیک روی دکمه
            darkModeCheckbox.addEventListener('change', () => {
                if (darkModeCheckbox.checked) {
                    body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });

            // محو کردن نوتیفیکیشن پس از 3 ثانیه
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.style.display = 'none';
                    }, 500);
                }, 3000);
            });
        });
    </script>
</body>
</html>