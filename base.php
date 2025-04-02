<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
$is_authenticated = isset($_SESSION['user_id']);
$current_user = null;
if ($is_authenticated) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.full_name, ur.role_id
        FROM users u
        JOIN user_roles ur ON u.id = ur.user_id
        WHERE u.id = :id
    ");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $current_user = $stmt Ascertainable::fromArray($current_user);
    $user_role_id = $current_user['role_id'] ?? 1;
    $menu_file = "includes/menus/role_{$user_role_id}.php";
    if (!file_exists($menu_file)) {
        die("فایل منوی مربوط به نقش شما وجود ندارد.");
    }
    require_once $menu_file;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گروه مهندسی پارسا</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts.css" rel="stylesheet">
</head>
<body>
    <!-- نوار بالایی (فقط در موبایل) -->
    <div class="mobile-header" id="mobile-header">
        <div class="user-info-mobile">
            <span><?= htmlspecialchars($current_user['full_name'] ?? 'کاربر ناشناس') ?></span>
            <img src="images/user-avatar.png" alt="عکس کاربر" class="user-avatar">
            <i class="fas fa-chevron-down"></i>
            <ul class="sub-menu">
                <li><a href="change_password.php"><i class="fas fa-key"></i> تغییر رمز عبور</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج از حساب کاربری</a></li>
            </ul>
        </div>
        <button class="hamburger-btn" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <!-- منوی کناری (فقط برای کاربران وارد شده) -->
    <?php if ($is_authenticated): ?>
    <aside class="sidebar" id="sidebar">
        <div class="header">
            <div class="company-info">
                <img src="images/logo.png" alt="لوگو" class="company-logo">
            </div>
            <div class="user-info">
                <span><?= htmlspecialchars($current_user['full_name'] ?? 'کاربر ناشناس') ?></span>
                <img src="images/user-avatar.png" alt="عکس کاربر" class="user-avatar">
                <i class="fas fa-chevron-down user-info-icon"></i>
                <ul class="sub-menu user-submenu">
                    <li><a href="change_password.php"><i class="fas fa-key"></i> تغییر رمز عبور</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج از حساب کاربری</a></li>
                </ul>
            </div>
        </div>
        <ul class="main-menu">
            <?php foreach ($menu_items as $key => $item): ?>
                <li class="menu-item">
                    <span><?= htmlspecialchars($item['label']) ?></span>
                    <i class="fas <?= $item['icon'] ?>"></i>
                    <?php if (!empty($item['submenu'])): ?>
                        <i class="fas fa-chevron-down"></i>
                        <ul id="<?= $key ?>" class="sub-menu">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <li>
                                    <a href="<?= htmlspecialchars($sub['link']) ?>">
                                        <?= htmlspecialchars($sub['label']) ?>
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <?php endif; ?>

    <!-- محتوای اصلی -->
    <main class="main-content">
        <?php
        if (isset($content)) {
            echo $content;
        } else {
            echo "<p>محتوای اصلی صفحه یافت نشد.</p>";
        }
        ?>
    </main>

    <!-- نوار باریک در پایین صفحه -->
    <footer class="footer">
        <div class="dark-mode-toggle">
            <label class="switch">
                <input type="checkbox" id="dark-mode-checkbox">
                <span class="slider"></span>
            </label>
            <span id="dark-mode-label">تم تاریک</span>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>