<?php
session_start();

// بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر رمز عبور</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- نمایش پیام‌های موفقیت/خطا -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="notification success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="notification error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- فرم تغییر رمز عبور -->
    <div class="form-container">
        <h2>تغییر رمز عبور</h2>
        <form method="POST" action="change_password.php">
            <div class="form-group">
                <label for="new_password">رمز عبور جدید:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">تکرار رمز عبور:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="button-group">
                <button type="submit" class="submit-btn">ذخیره</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='profile.php'">انصراف</button>
            </div>
        </form>
    </div>
</body>
</html>