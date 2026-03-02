<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

// Защита доступа
if ($role !== 'admin' && $role !== 'director') { 
    die("<div class='alert alert-danger'>Доступ запрещен.</div>"); 
}

// Получаем ID товара из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die("<div class='alert alert-danger'>Товар не выбран.</div>");
}

// --- ЛОГИКА ОБНОВЛЕНИЯ ДАННЫХ ---
if (isset($_POST['update_product'])) {
    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, category_id = ?, manufacturer = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'], 
            $_POST['category_id'], 
            $_POST['manufacturer'], 
            $_POST['price'], 
            $_POST['stock'], 
            $id
        ]);
        
        echo "<div class='alert alert-success'>✅ Данные товара успешно обновлены! <a href='manage_products.php'>Вернуться на склад</a></div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Ошибка при обновлении: " . $e->getMessage() . "</div>";
    }
}

// --- ПОЛУЧЕНИЕ ТЕКУЩИХ ДАННЫХ ТОВАРА ---
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("<div class='alert alert-danger'>Товар с таким ID не найден.</div>");
}

// Получаем все категории для выпадающего списка
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>✏️ Редактирование товара</h2>
        <a href="manage_products.php" class="btn btn-primary" style="background: #6c757d; padding: 5px 15px;">Назад</a>
    </div>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Наименование товара</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Категория</label>
                <select name="category_id">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ДОБАВЛЕННОЕ ПОЛЕ ПРОИЗВОДИТЕЛЯ -->
            <div class="form-group">
                <label>Производитель (Бренд)</label>
                <input type="text" name="manufacturer" value="<?= htmlspecialchars($product['manufacturer'] ?? '') ?>" placeholder="Напр: Савушкин продукт">
            </div>

            <div class="form-group">
                <label>Цена за единицу (руб.)</label>
                <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Остаток на складе (шт.)</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
            
            <button type="submit" name="update_product" class="btn btn-success" style="width: 100%; padding: 12px; font-size: 16px;">
                💾 Сохранить изменения
            </button>
        </form>
    </div>
</div>

<?php echo '</div></body></html>'; ?>