<?php
// db.php

$host = '127.0.0.1'; // آدرس سرور MySQL
$dbname = 'project_management'; // نام پایگاه داده
$username = 'root'; // نام کاربری
$password = ''; // رمز عبور

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // نمایش خطاهای SQL
} catch (PDOException $e) {
    die("خطا در اتصال به پایگاه داده: " . $e->getMessage());
}
?>