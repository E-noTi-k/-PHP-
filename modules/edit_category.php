<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

if ($role !== 'admin' && $role !== 'director') { die("Доступ запрещен"); }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_POST['update_cat'])) {
    $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->execute([$_POST['name'], $id]);
    echo "<div class='alert alert-success'>Категория обновлена! <a href='manage_categories.php'>Назад</a></div>";
}

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$cat = $stmt->fetch();
?>

<div style="max-width: 500px; margin: 0 auto;">
    <h2>✏️ Редактировать категорию</h2>
    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Название категории</label>
                <input type="text" name="name" value="<?= htmlspecialchars($cat['name']) ?>" required>
            </div>
            <button type="submit" name="update_cat" class="btn btn-primary" style="width:100%">Сохранить</button>
        </form>
    </div>
</div>
<?php echo '</div></body></html>'; ?>