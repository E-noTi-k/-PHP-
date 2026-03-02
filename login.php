<?php
include 'config/db.php';
session_start();

// 1. ПРОВЕРКА COOKIE (Автоматический вход)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_COOKIE['remember_user']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    }
}

// 2. ОБРАБОТКА ВХОДА
if (isset($_POST['login'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];

        // Если нажата галочка "Запомнить"
        if (isset($_POST['remember'])) {
            setcookie("remember_user", $user['username'], time() + (86400 * 30), "/");
        }

        header("Location: index.php");
        exit;
    } else {
        $error = "Неверный логин или пароль!";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Вход в систему</title>
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh; background:#e9ecef; margin:0;">

    <div class="card" style="width:100%; max-width:400px; padding:30px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="color:var(--primary-color); margin-bottom:10px;">🥛 Молочная система</h2>
        <p style="color:var(--secondary-color); margin-bottom:25px;">Авторизация сотрудника</p>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger" style="padding:10px; font-size:14px; margin-bottom:20px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="text-align:left;">
                <label>Логин</label>
                <input type="text" name="username" placeholder="Введите ваш логин" required>
            </div>

            <div class="form-group" style="text-align:left;">
                <label>Пароль</label>
                <input type="password" name="password" placeholder="Введите пароль" required>
            </div>

            <div class="form-group" style="text-align:left; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" name="remember" id="rem" style="width:auto; margin:0;">
                <label for="rem" style="margin:0; font-weight:normal; cursor:pointer;">Запомнить меня</label>
            </div>

            <button type="submit" name="login" class="btn btn-primary" style="width:100%; padding:12px; font-size:16px;">
                Войти в кабинет
            </button>
        </form>

        <hr style="margin:25px 0; border:0; border-top:1px solid #eee;">
        
        <p style="font-size:14px; color:var(--secondary-color);">
            Нет учетной записи? <br>
            <a href="register.php" style="color:var(--primary-color); text-decoration:none; font-weight:bold;">Зарегистрировать нового сотрудника</a>
        </p>
    </div>

</body>
</html>