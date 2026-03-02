<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

if ($role !== 'director') { 
    die("<div class='alert alert-danger'>Доступ разрешен только директору.</div>"); 
}

// 1. ПОЛУЧАЕМ ПАРАМЕТРЫ ФИЛЬТРА
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// 2. ЛОГИКА ПАГИНАЦИИ
$limit = 15; // Количество записей на одной странице
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

try {
    // Считаем общее количество записей за этот период
    $count_sql = "SELECT COUNT(*) FROM sales WHERE DATE(sale_date) BETWEEN :start AND :end";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute(['start' => $start_date, 'end' => $end_date]);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    // Считаем общую сумму ЗА ВЕСЬ ПЕРИОД (независимо от страницы)
    $sum_sql = "SELECT SUM(total_amount) FROM sales WHERE DATE(sale_date) BETWEEN :start AND :end";
    $sum_stmt = $pdo->prepare($sum_sql);
    $sum_stmt->execute(['start' => $start_date, 'end' => $end_date]);
    $grand_total_period = $sum_stmt->fetchColumn() ?: 0;

    // Получаем данные для ТЕКУЩЕЙ СТРАНИЦЫ
    $sql = "SELECT s.sale_date, c.name as cust, u.username as seller, s.total_amount 
            FROM sales s 
            JOIN customers c ON s.customer_id = c.id 
            JOIN users u ON s.user_id = u.id
            WHERE DATE(s.sale_date) BETWEEN :start AND :end
            ORDER BY s.sale_date DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':start', $start_date);
    $stmt->bindValue(':end', $end_date);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $sales = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}
?>

<div class="report-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>📊 История продаж (Всего: <?= $total_items ?>)</h2>
    <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" class="btn btn-success">
        📥 Весь период в Excel
    </a>
</div>

<!-- Фильтр -->
<div class="card" style="margin-bottom: 30px;">
    <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label>С какой даты:</label>
            <input type="date" name="start_date" value="<?= $start_date ?>">
        </div>
        <div class="form-group" style="flex: 1; min-width: 150px; margin-bottom: 0;">
            <label>По какую дату:</label>
            <input type="date" name="end_date" value="<?= $end_date ?>">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <button type="submit" class="btn btn-primary">📊 Показать</button>
            <a href="reports.php" class="btn btn-danger" style="text-decoration:none;">Сброс</a>
        </div>
    </form>
</div>

<!-- Итоговая карточка за весь период -->
<div class="card" style="background: var(--primary-color); color: white; margin-bottom: 20px; padding: 15px;">
    <h3 style="margin:0;">Общая выручка за выбранный период: <?= number_format($grand_total_period, 2, '.', ' ') ?> руб.</h3>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Дата и время</th>
                <th>Покупатель</th>
                <th>Сотрудник</th>
                <th>Сумма чека</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales) > 0): ?>
                <?php foreach($sales as $s): ?>
                <tr>
                    <td><?= date('d.m.Y H:i', strtotime($s['sale_date'])) ?></td>
                    <td><?= htmlspecialchars($s['cust']) ?></td>
                    <td><?= htmlspecialchars($s['seller']) ?></td>
                    <td><strong><?= number_format($s['total_amount'], 2, '.', ' ') ?> р.</strong></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center; padding: 30px;">Нет данных за указанный период.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- БЛОК ПАГИНАЦИИ -->
<?php if ($total_pages > 1): ?>
<div class="pagination" style="margin-top: 30px; display: flex; justify-content: center; gap: 8px;">
    <?php 
    // Сохраняем даты в ссылках пагинации
    $date_params = "&start_date=$start_date&end_date=$end_date";
    ?>
    
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?><?= $date_params ?>" class="btn btn-primary">&larr; Назад</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?><?= $date_params ?>" 
           class="btn <?= $i == $page ? 'btn-success' : 'btn-primary' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?><?= $date_params ?>" class="btn btn-primary">Вперед &rarr;</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php echo '</div></body></html>'; ?>