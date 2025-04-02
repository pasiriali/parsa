<?php
$menu_items = [
    'dashboard' => ['icon' => 'fa-home', 'label' => 'داشبورد', 'link' => 'dashboard.php'],
    'projects' => [
        'icon' => 'fa-tasks',
        'label' => 'مدیریت پروژه‌ها',
        'submenu' => [
            ['label' => 'قرارداد', 'link' => '#'],
            ['label' => 'صورت وضعیت', 'link' => '#'],
            ['label' => 'گزارشات', 'link' => '#']
        ]
    ],
    'finance' => [
        'icon' => 'fa-coins',
        'label' => 'مدیریت مالی',
        'submenu' => [
            ['label' => 'گزارشات مالی', 'link' => '#'],
            ['label' => 'پرداختی‌ها', 'link' => '#'],
            ['label' => 'دریافتی‌ها', 'link' => '#']
        ]
    ],
    'user-management' => [
        'icon' => 'fa-users',
        'label' => 'مدیریت کاربران',
        'submenu' => [
            ['label' => 'افزودن کاربر', 'link' => 'add_user.php'],
            ['label' => 'مشاهده کاربران', 'link' => 'manage_users.php']
        ]
    ]
];
?>