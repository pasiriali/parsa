<?php
session_start();
session_destroy(); // پاک کردن جلسه
header('Location: index.php');
exit;
?>