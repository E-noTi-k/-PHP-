<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base = "/Курсач/"; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: " . $base . "login.php"); 
    exit; 
}

$role = $_SESSION['role'];
$user_name = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>
<nav>
    <div class="user-info">
        📍 <strong><?= htmlspecialchars($user_name) ?></strong> (<?= $role ?>)
    </div>
    <div class="nav-links">
        <!-- Везде добавляем переменную $base -->
        <a href="<?= $base ?>index.php">🏠 Главная</a>

        <?php if ($role == 'salesperson' || $role == 'director'): ?>
            <a href="<?= $base ?>make_sale.php">💰 Продажа</a>
            <a href="<?= $base ?>modules/manage_customers.php">👥 Клиенты</a>
        <?php endif; ?>

        <?php if ($role == 'admin' || $role == 'director'): ?>
            <a href="<?= $base ?>modules/manage_products.php">📦 Склад</a>
            <a href="<?= $base ?>modules/manage_categories.php">📂 Категории</a>
        <?php endif; ?>

        <?php if ($role == 'director'): ?>
            <a href="<?= $base ?>modules/reports.php">📊 Отчеты</a>
        <?php endif; ?>
        <!--<a href="<?= $base ?>modules/guestbook.php">📖 Отзывы (Файлы)</a>-->
        <a href="<?= $base ?>logout.php" style="background:rgba(255,0,0,0.3)">🚪 Выход</a>
    </div>
</nav>
<div class="container">