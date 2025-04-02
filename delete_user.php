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

// دریافت ID کاربر از URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id > 0) {
    try {
        // حذف نقش‌های کاربر از جدول user_roles
        $stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // حذف کاربر از جدول users
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // ذخیره پیغام موفقیت در Session
        $_SESSION['delete_user_success'] = "کاربر با موفقیت حذف شد.";
    } catch (PDOException $e) {
        // ذخیره پیغام خطا در Session
        $_SESSION['delete_user_error'] = "خطا در حذف کاربر: " . $e->getMessage();
    }
} else {
    // اگر ID کاربر معتبر نباشد
    $_SESSION['delete_user_error'] = "کاربر مورد نظر یافت نشد.";
}

// بازگشت به صفحه مدیریت کاربران
header('Location: manage_users.php');
exit;
?>