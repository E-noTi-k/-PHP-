<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if ($_SESSION['role'] !== 'director') { die("Доступ запрещен"); }

$start = $_GET['start_date'] ?? date('Y-m-d');
$end = $_GET['end_date'] ?? date('Y-m-d');

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Dairy_Report_{$start}_to_{$end}.xls");

$sql = "SELECT s.sale_date, c.name as cust, p.name as prod, si.quantity, si.price_at_sale, (si.quantity * si.price_at_sale) as subtotal
        FROM sales s 
        JOIN customers c ON s.customer_id = c.id 
        JOIN sale_items si ON s.id = si.sale_id
        JOIN products p ON si.product_id = p.id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        ORDER BY s.sale_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$start, $end]);
$sales = $stmt->fetchAll();

echo "\xEF\xBB\xBF"; 
?>
<table border="0">
    <tr><td colspan="6" style="font-size: 16px; font-weight: bold; text-align: center;">ОТЧЕТ О ПРОДАЖАХ ЗА ПЕРИОД</td></tr>
    <tr><td colspan="6" style="text-align: center;">с <?= date('d.m.Y', strtotime($start)) ?> по <?= date('d.m.Y', strtotime($end)) ?></td></tr>
    <tr><td></td></tr>
</table>

<table border="1">
    <tr style="background-color: #eee;">
        <th>Дата</th>
        <th>Клиент</th>
        <th>Товар</th>
        <th>Кол-во</th>
        <th>Цена</th>
        <th>Сумма</th>
    </tr>
    <?php $grand_total = 0; foreach ($sales as $s): $grand_total += $s['subtotal']; ?>
    <tr>
        <td><?= date('d.m.y', strtotime($s['sale_date'])) ?></td>
        <td><?= htmlspecialchars($s['cust']) ?></td>
        <td><?= htmlspecialchars($s['prod']) ?></td>
        <td><?= $s['quantity'] ?></td>
        <td><?= $s['price_at_sale'] ?></td>
        <td><?= $s['subtotal'] ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="5" align="right"><b>ИТОГО ВЫРУЧКА ЗА ПЕРИОД:</b></td>
        <td><b><?= $grand_total ?></b></td>
    </tr>
</table>

<table border="0">
    <tr><td></td></tr>
    <tr><td></td></tr>
    <tr><td colspan="2">Отчет составил: _________________ (<?= htmlspecialchars($_SESSION['username']) ?>)</td></tr>
    <tr><td></td></tr>
    <tr><td colspan="2">Генеральный директор: _________________ (_________________)</td></tr>
    <tr><td></td></tr>
    <tr><td colspan="5" style="font-size: 10px; color: #666;">* Сформировано автоматически в системе Dairy System. Дата: <?= date('d.m.Y H:i') ?></td></tr>
</table>