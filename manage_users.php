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

// اضافه کردن کتابخانه تاریخ شمسی
require_once 'includes/jdf.php';

// بررسی وجود پیغام خطا یا موفقیت در Session
$delete_error = isset($_SESSION['delete_user_error']) ? $_SESSION['delete_user_error'] : "";
$delete_success = isset($_SESSION['delete_user_success']) ? $_SESSION['delete_user_success'] : "";

// پاک کردن پیغام‌ها پس از نمایش
unset($_SESSION['delete_user_error']);
unset($_SESSION['delete_user_success']);

// دریافت لیست کاربران از پایگاه داده
$stmt = $pdo->query("
    SELECT 
        u.id, 
        u.username, 
        u.full_name, 
        GROUP_CONCAT(DISTINCT r.name SEPARATOR ', ') AS role_names, 
        u.last_login
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    GROUP BY u.id, u.username, u.full_name, u.last_login
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تبدیل last_login به تاریخ شمسی
foreach ($users as &$user) {
    if (!empty($user['last_login'])) {
        $user['last_login_jalali'] = convertToJalali($user['last_login']);
    } else {
        $user['last_login_jalali'] = 'وارد نشده';
    }
}

// تابع تبدیل تاریخ میلادی به شمسی
function convertToJalali($date) {
    $timestamp = strtotime($date);
    return jdate('Y/m/d H:i', $timestamp); // استفاده از تابع jdate برای تبدیل
}

// محتوای اصلی صفحه
ob_start();
?>

<!-- نمایش پیغام موفقیت یا خطا -->
<?php if (!empty($delete_success)): ?>
    <div class="notification success"><?= htmlspecialchars($delete_success) ?></div>
<?php endif; ?>

<?php if (!empty($delete_error)): ?>
    <div class="notification error"><?= htmlspecialchars($delete_error) ?></div>
<?php endif; ?>

<div class="notification-container" id="notification-container"></div>

<main class="content main-content">
    <!-- نوار بالای صفحه -->
    <header class="user-management-header">
        <div class="search-container">
            <!-- دکمه جستجو -->
            <button class="search-btn" onclick="toggleSearchBox()">
                <i class="fa-solid fa-magnifying-glass"></i> جستجو
            </button>
            <!-- سرچ باکس (پنهان در حالت اولیه) -->
            <div class="search-box" id="search-box">
                <input type="text" id="search-input" placeholder="جستجوی کاربر..." oninput="filterUsers()">
                <i class="search-icon fa-solid fa-magnifying-glass"></i>
            </div>
         </div>

        <!-- دکمه افزودن کاربر جدید -->
        <a href="add_user.php" class="add-user-btn"><i class="fa-solid fa-plus"></i> افزودن کاربر</a>
    </header>

    <!-- جدول نمایش کاربران -->
    <div class="user-management">
        <table class="user-table">
            <thead>
                <tr>
                    <th>نام کاربری</th>
                    <th>نام و نام خانوادگی</th>
                    <th>نقش‌ها</th>
                    <th>آخرین فعالیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="5">هیچ کاربری یافت نشد.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => &$user): ?>
                        <tr class="fade-in" data-user-id="<?= $user['id'] ?>">
                            <td><?= htmlspecialchars($user['username'] ?? 'بدون نام کاربری') ?></td>
                            <td><?= htmlspecialchars($user['full_name'] ?? 'بدون نام') ?></td>
                            <td><?= htmlspecialchars($user['role_names'] ?? 'بدون نقش') ?></td>
                            <td><?= htmlspecialchars($user['last_login_jalali'] ?? 'وارد نشده') ?></td>
                            <td class="action-buttons">
                                <!-- کانتینر Flexbox برای دکمه‌ها -->
                                <div class="button-group">
                                    <!-- آیکون ویرایش -->
                                    <button class="edit-btn" onclick="location.href='edit_user.php?id=<?= $user['id'] ?>'">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <!-- آیکون حذف -->
                                    <button class="delete-btn" onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['full_name']) ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    function confirmDelete(userId, username, fullName) {
        const message = `آیا می‌خواهید نام کاربری ${username} - ${fullName} را حذف کنید؟`;
        if (confirm(message)) {
            window.location.href = 'delete_user.php?id=' + userId;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
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

<?php
$content = ob_get_clean();

// استفاده از قالب پایه
require_once 'base.php';
?>