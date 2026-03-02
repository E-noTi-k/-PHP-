<?php
include '../config/db.php';
include '../includes/navbar.php';
if ($role !== 'admin' && $role !== 'director') { die("<div class='alert alert-danger'>Доступ запрещен</div>"); }

if (isset($_POST['add_cat'])) {
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$_POST['name']]);
}

if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id_to_delete]);
        echo "<script>window.location='manage_categories.php';</script>"; // Перезагрузка
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Ошибка: Нельзя удалить категорию, в которой есть товары!</div>";
    }
}

$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<h2>📂 Категории продукции</h2>
<div class="card" style="max-width: 500px; margin-bottom: 20px;">
    <form method="POST" style="display:flex; gap:10px;">
        <input type="text" name="name" placeholder="Название (напр: Йогурты)" required>
        <button type="submit" name="add_cat" class="btn btn-primary">Создать</button>
    </form>
</div>

<div class="table-responsive" style="max-width: 600px;">
    <table>
        <thead><tr><th>ID</th><th>Название категории</th><th>Действие</th></tr></thead>
        <?php foreach($cats as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
            <td>
                <a href="edit_category.php?id=<?= $c['id'] ?>" class="btn btn-primary" style="padding:5px 10px; font-size:12px;">✏️</a>
                <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger" style="padding:5px 10px; font-size:12px;" onclick="return confirm('Удалить?')">🗑️</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php echo '</div></body></html>'; ?>