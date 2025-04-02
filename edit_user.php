<?php
session_start();

// بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// اتصال به پایگاه داده
require_once 'includes/db.php';

// دریافت ID کاربر از URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// بررسی وجود پیغام خطا یا موفقیت در Session
$error_message = $_SESSION['edit_user_error'] ?? "";
$success_message = $_SESSION['edit_user_success'] ?? "";

// پاک کردن پیغام‌ها پس از نمایش
unset($_SESSION['edit_user_error']);
unset($_SESSION['edit_user_success']);

try {
    // دریافت اطلاعات کاربر
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // اگر کاربر وجود نداشت، به صفحه مدیریت کاربران هدایت شود
        header('Location: manage_users.php');
        exit;
    }

    // دریافت لیست نقش‌ها
    $stmt = $pdo->query("SELECT id, name FROM roles");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // دریافت نقش‌های فعلی کاربر
    $stmt = $pdo->prepare("SELECT role_id FROM user_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $_SESSION['edit_user_error'] = "خطا در دریافت اطلاعات: " . $e->getMessage();
    header("Location: manage_users.php");
    exit;
}

// پردازش فرم ویرایش
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['new_password'])) {
    try {
        // Validation و Sanitization
        $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
        $full_name = trim(filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING));
        $role_id = (int)filter_input(INPUT_POST, 'role', FILTER_VALIDATE_INT);

        if (empty($username) || empty($full_name) || empty($role_id)) {
            throw new Exception("لطفاً تمام فیلدها را پر کنید.");
        }

        // بررسی تکراری بودن نام کاربری (به جز کاربر فعلی)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("نام کاربری قبلاً ثبت شده است.");
        }

        // بروزرسانی اطلاعات کاربر
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ? WHERE id = ?");
        $stmt->execute([$username, $full_name, $user_id]);

        // حذف نقش‌های قبلی کاربر
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // اضافه کردن نقش جدید کاربر
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $role_id]);

        $_SESSION['edit_user_success'] = "اطلاعات کاربر با موفقیت ویرایش شد.";
        header("Location: manage_users.php"); // هدایت به صفحه مدیریت کاربران پس از ویرایش
        exit;

    } catch (Exception $e) {
        $_SESSION['edit_user_error'] = $e->getMessage();
        header("Location: edit_user.php?id=$user_id");
        exit;
    }
}

// پردازش فرم بازنشانی رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    try {
        // دریافت مقادیر از فرم
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // اعتبارسنجی ورودی‌ها
        if (empty($new_password) || empty($confirm_password)) {
            throw new Exception("لطفاً رمز عبور جدید و تکرار آن را وارد کنید.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("رمز عبور جدید و تکرار آن باید یکسان باشند.");
        }

        // به‌روزرسانی رمز عبور در پایگاه داده (بدون هش کردن)
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user_id]);

        // بررسی تأثیر پرس و جو
        if ($stmt->rowCount() === 0) {
            throw new Exception("خطا در به‌روزرسانی رمز عبور. لطفاً دوباره تلاش کنید.");
        }

        // ذخیره پیغام موفقیت در Session
        $_SESSION['edit_user_success'] = "رمز عبور با موفقیت بازنشانی شد.";
        header("Location: edit_user.php?id=$user_id");
        exit;

    } catch (Exception $e) {
        // ثبت خطا در لاگ (اختیاری)
        error_log("Error updating password: " . $e->getMessage());

        // ذخیره پیغام خطا در Session
        $_SESSION['edit_user_error'] = $e->getMessage();
        header("Location: edit_user.php?id=$user_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ویرایش کاربر</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="dark-theme">
    <!-- نوتیفیکیشن -->
    <div class="notification-container">
        <?php if (!empty($error_message)): ?>
            <div class="notification error"><?= htmlspecialchars($error_message) ?></div>
        <?php elseif (!empty($success_message)): ?>
            <div class="notification success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
    </div>

    <main class="content">
        <div class="form-container glass-effect">
            <header class="header">
                <div class="title-container">
                    <i class="fas fa-user-edit title-icon"></i>  
                    <h2 class="title">ویرایش اطلاعات کاربر</h2>
                </div>
            </header>

            <!-- فرم ویرایش اطلاعات کاربر -->
            <form method="POST" class="styled-form" id="user-form">
                <!-- نام کاربری -->
                <div class="form-group">
                    <label for="username">نام کاربری:</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required placeholder="نام کاربری را وارد کنید">
                </div>

                <!-- نام و نام خانوادگی -->
                <div class="form-group">
                    <label for="full_name">نام و نام خانوادگی:</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" placeholder="نام و نام خانوادگی را وارد کنید">
                </div>

                <!-- نقش کاربر -->
                <div class="form-group">
                    <label for="role">نقش:</label>
                    <select name="role" id="role" required>
                        <option value="" disabled>انتخاب نقش</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= htmlspecialchars($role['id']) ?>" <?= in_array($role['id'], $current_roles) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- دکمه بازنشانی رمز عبور -->
                <div class="form-group">
                    <button type="button" class="reset-password-btn" onclick="showResetPasswordForm()">
                        <i class="fas fa-key"></i> بازنشانی رمز عبور
                    </button>
                </div>

                <!-- دکمه‌ها -->
                <div class="button-group">
                    <button type="submit" class="submit-btn" id="save-button">
                        <i class="fas fa-save"></i> ذخیره
                    </button>
                    <a href="manage_users.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> بازگشت
                    </a>
                </div>
            </form>

            <!-- فرم بازنشانی رمز عبور -->
            <div id="reset-password-form" class="form-container glass-effect" style="display: none;">
                <header class="header">
                    <div class="title-container">
                        <i class="fas fa-key title-icon"></i>  
                        <h2 class="title">بازنشانی رمز عبور</h2>
                    </div>
                </header>

                <form method="POST" class="styled-form">
                    <!-- رمز عبور جدید -->
                    <div class="form-group">
                        <label for="new_password">رمز عبور جدید:</label>
                        <input type="password" name="new_password" id="new_password" required placeholder="رمز عبور جدید را وارد کنید">
                    </div>

                    <!-- تکرار رمز عبور جدید -->
                    <div class="form-group">
                        <label for="confirm_password">تکرار رمز عبور:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required placeholder="رمز عبور را دوباره وارد کنید">
                    </div>

                    <!-- دکمه‌ها -->
                    <div class="button-group">
                        <button type="submit" class="submit-btn" id="reset-password-save-button">
                            <i class="fas fa-save"></i> ذخیره
                        </button>
                        <button type="button" class="back-button" onclick="hideResetPasswordForm()">
                            <i class="fas fa-times"></i> انصراف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        notification.remove();
                    }, 500);
                }, 3000);
            });
        });

        // تأیید قبل از ذخیره‌سازی
        const saveButton = document.getElementById('save-button');
        saveButton.addEventListener('click', (event) => {
            const isConfirmed = confirm('آیا از ویرایش اطلاعات مطمئن هستید؟');
            if (!isConfirmed) {
                event.preventDefault(); // جلوگیری از ارسال فرم
            }
        });

        // نمایش فرم بازنشانی رمز عبور
        function showResetPasswordForm() {
            const isConfirmed = confirm('آیا از بازنشانی رمز عبور مطمئن هستید؟');
            if (isConfirmed) {
                document.getElementById('reset-password-form').style.display = 'block';
            }
        }

        // مخفی کردن فرم بازنشانی رمز عبور
        function hideResetPasswordForm() {
            document.getElementById('reset-password-form').style.display = 'none';
        }

        // تأیید قبل از ذخیره‌سازی رمز عبور جدید
        document.getElementById('reset-password-save-button').addEventListener('click', (event) => {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (newPassword !== confirmPassword) {
                alert('رمز عبور جدید و تکرار آن باید یکسان باشند.');
                event.preventDefault(); // جلوگیری از ارسال فرم
                return;
            }

            const isConfirmed = confirm('آیا از ذخیره رمز عبور جدید مطمئن هستید؟');
            if (!isConfirmed) {
                event.preventDefault(); // جلوگیری از ارسال فرم
            }
        });
    </script>
</body>
</html>