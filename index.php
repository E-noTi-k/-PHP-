<?php 
include 'config/db.php'; 
include 'includes/navbar.php'; 

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель управления</title>
    <style>
        .dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 8px; text-align: center; background: #f9f9f9; }
        .card h3 { margin-top: 0; }
        .card a { display: inline-block; margin-top: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p>Ваша роль: <strong><?= $role ?></strong></p>

    <div class="dashboard">
        <!-- Блок Продавца и Директора -->
        <?php if ($role == 'salesperson' || $role == 'director'): ?>
            <div class="card">
                <h3>Продажи</h3>
                <p>Оформление новых чеков и работа с кассой.</p>
                <a href="make_sale.php">Оформить продажу</a>
            </div>
            <div class="card">
                <h3>Клиенты</h3>
                <p>База физлиц и компаний (ИНН, телефоны).</p>
                <a href="modules/manage_customers.php">База клиентов</a>
            </div>
        <?php endif; ?>

        <!-- Блок Администратора и Директора -->
        <?php if ($role == 'admin' || $role == 'director'): ?>
            <div class="card">
                <h3>Склад</h3>
                <p>Управление товарами, ценами и остатками.</p>
                <a href="modules/manage_products.php">Товары</a>
            </div>
            <div class="card">
                <h3>Категории</h3>
                <p>Настройка групп товаров (Молоко, Сыры и т.д.).</p>
                <a href="modules/manage_categories.php">Настройка категорий</a>
            </div>
        <?php endif; ?>

        <!-- Блок только для Директора -->
        <?php if ($role == 'director'): ?>
            <div class="card" style="border-color: #28a745;">
                <h3>Отчетность</h3>
                <p>Просмотр выручки и экспорт данных.</p>
                <a href="modules/reports.php" style="background: #28a745;">Открыть отчеты</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php echo '</div></body></html>';