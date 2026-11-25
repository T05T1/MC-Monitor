<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}

if (isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    //для обучения
    if ($username === 'admin' && $password === '12345') {
        $_SESSION['admin'] = true; ///создаем сессию
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Неверный логин или пароль!';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Логин</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <a href="index.php" class="btn-back">← На главную</a>

    <div class="login-container">
        <!-- Форма добавления -->
        <h2>Авторизация</h2>
        <?php if (isset($error)): ?>
            <p style="color: red; margin-bottom: 15px;"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST" class="login-form">
            <input type="text" name="username" placeholder="Логин" class="form-input">
            <input type="password" name="password" placeholder="Пароль" class="form-input">
            <button type="submit" class="btn-submit">Войти!</button>
        </form>


    </div>

</body>
</html>