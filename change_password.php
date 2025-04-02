<?php
session_start();

// بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// اتصال به پایگاه داده
require_once 'includes/db.php';

// پردازش فرم تغییر رمز عبور
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // دریافت ورودی‌ها
        $new_password = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        // اعتبارسنجی ورودی‌ها
        if (empty($new_password) || empty($confirm_password)) {
            throw new Exception("همه فیلدها را پر کنید.");
        }

        if ($new_password !== $confirm_password) {
            throw new Exception("رمز عبور جدید و تکرار آن باید یکسان باشند.");
        }

        if (strlen($new_password) < 6) {
            throw new Exception("رمز عبور باید حداقل 6 کاراکتر باشد.");
        }

        // به‌روزرسانی رمز عبور در پایگاه داده
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $_SESSION['user_id']]);

        // بررسی تأثیر پرس و جو
        if ($stmt->rowCount() === 0) {
            throw new Exception("خطا در به‌روزرسانی رمز عبور.");
        }

        // ذخیره پیام موفقیت و هدایت به صفحه پروفایل
        $_SESSION['success_message'] = "رمز عبور با موفقیت تغییر کرد.";
        header("Location: profile.php");
        exit;

    } catch (Exception $e) {
        // ذخیره پیام خطا و هدایت به صفحه پروفایل
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: profile.php");
        exit;
    }
}
?>