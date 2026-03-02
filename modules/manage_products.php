<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

if ($role !== 'admin' && $role !== 'director') { die("Доступ запрещен"); }

// Обработка добавления (с учетом производителя)
if (isset($_POST['add_product'])) {
    $stmt = $pdo->prepare("INSERT INTO products (name, category_id, manufacturer, price, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['category_id'], $_POST['manufacturer'], $_POST['price'], $_POST['stock']]);
    echo "<script>window.location='manage_products.php';</script>";
}

// Поиск
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Пагинация
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, c.name as cat_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.name LIKE :search OR p.manufacturer LIKE :search
        ORDER BY p.id DESC 
        LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Считаем общее кол-во для пагинации
    $t_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ? OR manufacturer LIKE ?");
    $t_stmt->execute(["%$search%", "%$search%"]);
    $total_items = $t_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);
} catch (PDOException $e) {
    die("Ошибка в базе данных: " . $e->getMessage());
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<h2>📦 Управление складом</h2>

<!-- Форма поиска -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Поиск товара или бренда...">
        <button type="submit" class="btn btn-primary">Найти</button>
        <a href="manage_products.php" class="btn btn-danger">Сброс</a>
    </form>
</div>

<!-- Форма добавления -->
<div class="card" style="margin-bottom: 20px;">
    <h3>Добавить товар</h3>
    <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: flex-end;">
        <div class="form-group"><label>Название</label><input type="text" name="name" required></div>
        <div class="form-group">
            <label>Категория</label>
            <select name="category_id">
                <?php foreach($categories as $cat) echo "<option value='{$cat['id']}'>{$cat['name']}</option>"; ?>
            </select>
        </div>
        <div class="form-group"><label>Производитель</label><input type="text" name="manufacturer" placeholder="Бренд"></div>
        <div class="form-group"><label>Цена</label><input type="number" step="0.01" name="price" required></div>
        <div class="form-group"><label>Склад</label><input type="number" name="stock" required></div>
        <div class="form-group"><label>&nbsp;</label><button type="submit" name="add_product" class="btn btn-success">Добавить</button></div>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Товар</th>
                <th>Производитель</th>
                <th>Категория</th>
                <th>Цена</th>
                <th>Остаток</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $p): ?>
            <tr>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td><?= htmlspecialchars($p['manufacturer']) ?></td>
                <td><?= htmlspecialchars($p['cat_name'] ?? 'Без категории') ?></td>
                <td><?= $p['price'] ?> р.</td>
                <td><?= $p['stock'] ?> шт.</td>
                <td><a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-primary" style="padding:5px 10px;">✏️</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Пагинация -->
<div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 5px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="btn <?= $i==$page?'btn-success':'btn-primary' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>

<?php echo '</div></body></html>'; ?>