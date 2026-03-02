<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/navbar.php';

// Путь к файлу, где будут храниться данные
$file_path = __DIR__ . '/../data_storage.json';

// --- ЛОГИКА СОХРАНЕНИЯ В ФАЙЛ ---
if (isset($_POST['send_feedback'])) {
    $name = htmlspecialchars($_POST['visitor_name']);
    $message = htmlspecialchars($_POST['message']);
    $date = date('d.m.Y H:i');

    // 1. Считываем текущие данные из файла
    $current_data = [];
    if (file_exists($file_path)) {
        $json_content = file_get_contents($file_path);
        $current_data = json_decode($json_content, true) ?? [];
    }

    // 2. Добавляем новый отзыв в массив
    $current_data[] = [
        'name' => $name,
        'message' => $message,
        'date' => $date
    ];

    // 3. Сохраняем обновленный массив обратно в файл
    file_put_contents($file_path, json_encode($current_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    echo "<div class='alert alert-success'>✅ Ваш отзыв успешно сохранен в файл!</div>";
}

// --- ЛОГИКА СЧИТЫВАНИЯ ИЗ ФАЙЛА ---
$feedbacks = [];
if (file_exists($file_path)) {
    $json_content = file_get_contents($file_path);
    $feedbacks = json_decode($json_content, true) ?? [];
    // Перевернем массив, чтобы последние отзывы были сверху
    $feedbacks = array_reverse($feedbacks);
}
?>

<div style="max-width: 800px; margin: 0 auto;">
    <h2>📖 Книга отзывов и предложений (Работа с файлами)</h2>

    <!-- Форма добавления -->
    <div class="card" style="margin-bottom: 30px;">
        <h3>Оставить отзыв</h3>
        <form method="POST">
            <div class="form-group">
                <label>Ваше имя</label>
                <input type="text" name="visitor_name" value="<?= htmlspecialchars($_SESSION['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Ваше сообщение / предложение</label>
                <textarea name="message" rows="4" required placeholder="Напишите здесь ваши замечания по работе магазина..."></textarea>
            </div>
            <button type="submit" name="send_feedback" class="btn btn-primary">Отправить отзыв</button>
        </form>
    </div>

    <!-- Список отзывов из файла -->
    <div class="feedback-list">
        <h3>Последние записи:</h3>
        <?php if (empty($feedbacks)): ?>
            <p>Записей пока нет. Будьте первым!</p>
        <?php else: ?>
            <?php foreach ($feedbacks as $item): ?>
                <div class="card" style="margin-bottom: 15px; border-left: 5px solid var(--primary-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <strong>👤 <?= htmlspecialchars($item['name']) ?></strong>
                        <small style="color: #999;"><?= $item['date'] ?></small>
                    </div>
                    <p style="margin: 0; font-style: italic;">"<?= nl2br(htmlspecialchars($item['message'])) ?>"</p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php echo '</div></body></html>'; ?>