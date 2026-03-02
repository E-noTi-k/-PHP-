<?php
include '../config/db.php';
include '../includes/navbar.php';
if ($role !== 'salesperson' && $role !== 'director') { die("<div class='alert alert-danger'>Доступ запрещен</div>"); }

if (isset($_POST['add_cust'])) {
    $stmt = $pdo->prepare("INSERT INTO customers (name, type, phone, inn) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['type'], $_POST['phone'], $_POST['inn']]);
}

// --- ЛОГИКА УДАЛЕНИЯ ПОКУПАТЕЛЯ ---
if (isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id_to_delete]);
        echo "<script>window.location='manage_customers.php';</script>";
    } catch (PDOException $e) {
        // Ошибка возникнет, если у покупателя уже есть совершенные продажи (связи в БД)
        echo "<div class='alert alert-danger'>Ошибка: Нельзя удалить покупателя, у которого есть история покупок!</div>";
    }
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll();
?>
<h2>👥 База клиентов и компаний</h2>
<div class="card" style="margin-bottom: 30px;">
    <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; align-items: flex-end;">
        <div class="form-group"><label>Имя / Компания</label><input type="text" name="name" required></div>
        <div class="form-group">
            <label>Тип</label>
            <select name="type">
                <option value="person">Частное лицо</option>
                <option value="company">Компания (Юр. лицо)</option>
            </select>
        </div>
        <div class="form-group"><label>Телефон</label><input type="text" name="phone"></div>
        <div class="form-group"><label>ИНН (для комп.)</label><input type="text" name="inn"></div>
        <div class="form-group"><button type="submit" name="add_cust" class="btn btn-success" style="width: 100%";>Добавить</button></div>
    </form>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Имя/Название</th>
                <th>Тип</th>
                <th>Телефон</th>
                <th>ИНН</th>
                <th style="text-align: right;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($customers as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td><?= $c['type'] == 'company' ? '🏢 Компания' : '👤 Физлицо' ?></td>
                <td><?= htmlspecialchars($c['phone']) ?></td>
                <td><?= $c['inn'] ?: '<span style="color:#ccc">-</span>' ?></td>
                <td style="text-align: right;">
                    <!-- ИСПРАВЛЕННАЯ ССЫЛКА НА edit_customer.php -->
                    <a href="edit_customer.php?id=<?= $c['id'] ?>" class="btn btn-primary" style="padding:5px 10px; font-size:12px;">✏️ Править</a>
                    
                    <!-- НОВАЯ КНОПКА УДАЛЕНИЯ -->
                    <a href="manage_customers.php?delete=<?= $c['id'] ?>" 
                       class="btn btn-danger" 
                       style="padding:5px 10px; font-size:12px;" 
                       onclick="return confirm('Удалить покупателя из базы?')">
                       🗑️ Удалить
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo '</div></body></html>'; ?>