<?php
// شروع جلسه
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// بررسی لاگین کاربر
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// محتوای اصلی داشبورد
ob_start(); ?>
<main class="content main-content">
    <div class="dashboard-content">
        <h1>خوش آمدید به داشبورد</h1>
        <p>این صفحه داشبورد شماست.</p>
        <p style="font-family: 'calibri', sans-serif;">این یک متن تست با فونت Iranian Sans است.</p>
        <p style="font-family: 'B Yekan', sans-serif;">این یک متن تست با فونت B Yekan است.</p>
    </div>
</main>
<?php
$content = ob_get_clean();

// استفاده از قالب پایه
require_once 'base.php';
?>