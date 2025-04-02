<?php
$menu_items = [
    'dashboard' => ['icon' => 'fa-home', 'label' => 'داشبورد'],
    'projects' => [
        'icon' => 'fa-tasks',
        'label' => 'مدیریت پروژه‌ها',
        'submenu' => [
            ['label' => 'قرارداد', 'link' => '#'],
            ['label' => 'صورت وضعیت', 'link' => '#']
        ]
    ]
];
?>