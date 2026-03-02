<?php
include 'config/db.php';

if (isset($_POST['register'])) {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$user, $pass, $role])) {
        $success = "Сотрудник успешно создан!";
    } else {
        $error = "Ошибка при регистрации.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Регистрация</title>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:#e9ecef; margin:0;">

    <div class="card" style="width:100%; max-width:450px; padding:30px;">
        <h2 style="color:var(--primary-color);">📝 Регистрация</h2>
        <p>Создание нового аккаунта в системе</p>

        <?php if(isset($success)): ?>
            <div class="alert alert-success"><?= $success ?> <a href="login.php">Войти</a></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="text-align:left;">
                <label>Имя пользователя (Логин)</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group" style="text-align:left;">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group" style="text-align:left;">
                <label>Роль сотрудника</label>
                <select name="role">
                    <option value="salesperson">Продавец (Касса)</option>
                    <option value="admin">Администратор (Склад)</option>
                    <option value="director">Директор (Все права)</option>
                </select>
            </div>

            <button type="submit" name="register" class="btn btn-success" style="width:100%; padding:12px;">Создать сотрудника</button>
        </form>

        <p style="margin-top:20px; font-size:14px;">
            <a href="login.php" style="text-decoration:none; color:var(--secondary-color);">← Вернуться к входу</a>
        </p>
    </div>

</body>
</html>