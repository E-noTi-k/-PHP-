<?php
include '../config/db.php';
include '../includes/navbar.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    // 1. Получаем инфо о товаре
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] >= $quantity) {
        $total_price = $product['price'] * $quantity;

        // Начинаем транзакцию, чтобы данные сохранились корректно
        $pdo->beginTransaction();

        try {
            // 2. Уменьшаем остаток
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$quantity, $product_id]);

            // 3. Создаем запись о продаже
            $stmt = $pdo->prepare("INSERT INTO sales (total_price) VALUES (?)");
            $stmt->execute([$total_price]);
            $sale_id = $pdo->lastInsertId();

            // 4. Записываем детали продажи
            $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
            $stmt->execute([$sale_id, $product_id, $quantity, $product['price']]);

            $pdo->commit();
            header("Location: index.php?success=1");
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Ошибка: " . $e->getMessage();
        }
    } else {
        echo "Недостаточно товара на складе!";
    }
}
?>