<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

// 1. ИСПРАВЛЕНА ЗАЩИТА: Редактировать клиентов могут Продавец и Директор
if ($role !== 'salesperson' && $role !== 'director') { 
    die("<div class='alert alert-danger'>Доступ запрещен.</div>"); 
}

// Получаем ID клиента из URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die("<div class='alert alert-danger'>Клиент не выбран.</div>");
}

// --- ЛОГИКА ОБНОВЛЕНИЯ ДАННЫХ КЛИЕНТА ---
if (isset($_POST['update_customer'])) {
    try {
        // ИСПРАВЛЕН SQL: работаем с таблицей customers
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, type = ?, phone = ?, inn = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'], 
            $_POST['type'], 
            $_POST['phone'], 
            $_POST['inn'], 
            $id
        ]);
        
        echo "<div class='alert alert-success'>✅ Данные клиента обновлены! <a href='manage_customers.php'>Вернуться в базу</a></div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Ошибка при обновлении: " . $e->getMessage() . "</div>";
    }
}

// --- ПОЛУЧЕНИЕ ТЕКУЩИХ ДАННЫХ КЛИЕНТА ---
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$cust = $stmt->fetch();

if (!$cust) {
    die("<div class='alert alert-danger'>Клиент с таким ID не найден.</div>");
}
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>✏️ Редактирование клиента</h2>
        <a href="manage_customers.php" class="btn btn-primary" style="background: #6c757d; padding: 5px 15px;">Назад</a>
    </div>

    <div class="card">
        <form method="POST">
            <div class="form-group">
                <label>Имя / Название компании</label>
                <input type="text" name="name" value="<?= htmlspecialchars($cust['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Тип контрагента</label>
                <select name="type">
                    <option value="person" <?= $cust['type'] == 'person' ? 'selected' : '' ?>>👤 Частное лицо</option>
                    <option value="company" <?= $cust['type'] == 'company' ? 'selected' : '' ?>>🏢 Компания</option>
                </select>
            </div>

            <div class="form-group">
                <label>Номер телефона</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($cust['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>ИНН (только для компаний)</label>
                <input type="text" name="inn" value="<?= htmlspecialchars($cust['inn'] ?? '') ?>">
            </div>

            <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
            
            <button type="submit" name="update_customer" class="btn btn-primary" style="width: 100%; padding: 12px;">
                💾 Сохранить изменения
            </button>
        </form>
    </div>
</div>

<?php echo '</div></body></html>'; ?>