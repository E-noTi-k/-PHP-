<?php
include 'config/db.php';
include 'includes/navbar.php';

if (isset($_POST['sell'])) {
    $p_id = $_POST['product_id'];
    $c_id = $_POST['customer_id'];
    $email = $_POST['customer_email'];
    $qty = (int)$_POST['quantity'];

    try {
        $stmt = $pdo->prepare("SELECT name, price, stock FROM products WHERE id = ?");
        $stmt->execute([$p_id]);
        $prod = $stmt->fetch();

        if ($prod && $prod['stock'] >= $qty) {
            $total = $prod['price'] * $qty;
            $pdo->beginTransaction();

            // Сохраняем продажу с email
            $stmt = $pdo->prepare("INSERT INTO sales (customer_id, user_id, total_amount, customer_email) VALUES (?, ?, ?, ?)");
            $stmt->execute([$c_id, $_SESSION['user_id'], $total, $email]);
            $s_id = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)")
                ->execute([$s_id, $p_id, $qty, $prod['price']]);

            $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$qty, $p_id]);
            
            $pdo->commit();

            // ОТПРАВКА ПИСЬМА
            if (!empty($email)) {
                $subject = "Ваш чек из магазина Молочной Продукции";
                $message = "Дата: " . date('Y-m-d H:i:s') . "\n" .
                        "Кому: $email\n" .
                        "Товар: {$prod['name']}\n" .
                        "Количество: $qty\n" .
                        "Итого: $total руб.\n" .
                        "--------------------------\n";

                // 1. Попытка отправить через стандартную функцию
                @mail($email, $subject, $message, "From: info@dairyshop.ru");

                // 2. ПРЯМАЯ ЗАПИСЬ В ФАЙЛ (Гарантированный результат)
                // Указываем путь к файлу лога. 
                // __DIR__ — это текущая папка, где лежит make_sale.php
                $log_file = __DIR__ . '/my_mail_log.txt'; 
                
                // Записываем данные в файл (FILE_APPEND позволяет добавлять записи в конец)
                file_put_contents($log_file, $message, FILE_APPEND);
            }

            echo "<script>alert('Продажа оформлена! Чек записан в лог для отправки на $email'); window.location='index.php';</script>";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Ошибка: " . $e->getMessage();
    }
}
?>
<h2>Оформление продажи</h2>
<form method="POST">
    <label>Клиент:</label><br>
    <select name="customer_id" required>
        <?php foreach($pdo->query("SELECT * FROM customers") as $c) echo "<option value='{$c['id']}'>{$c['name']}</option>"; ?>
    </select><br><br>

    <label>Email покупателя для чека:</label><br>
    <input type="email" name="customer_email" placeholder="example@mail.ru"><br><br>

    <label>Товар:</label><br>
    <select name="product_id" required>
        <?php foreach($pdo->query("SELECT * FROM products WHERE stock > 0") as $p) echo "<option value='{$p['id']}'>{$p['name']}</option>"; ?>
    </select><br><br>

    <label>Количество:</label><br>
    <input type="number" name="quantity" min="1" value="1"><br><br>

    <button type="submit" name="sell" class="btn btn-primary">Выбить чек и отправить Email</button>
</form>
<?php echo '</div></body></html>';