<?php
declare(strict_types=1);

session_start();
require_once '../vendor/autoload.php';

// Если сессии админа нет, выкидываем на страницу входа
if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy(); 
    header('Location: login.php');
    exit;
}

// 1. Подключаем класс
require_once '../src/ServerManager.php';

// 2. Подключаемся к базе (через конфиг)
try {
    $config = require_once '../config.php';
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $pdo = new PDO($dsn, $config['user'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

use App\ServerManager;

// 3. Получаем данные
$manager = new ServerManager($pdo);

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $manager->deleteServer($id);
    header('Location: admin.php');
    exit;
}

if (isset($_POST['address'], $_POST['name'])) {
    $address = trim($_POST['address']);
    $name = trim($_POST['name']);

    if (!empty($address) && !empty($name)) {
        // Проверяем, стоит ли галочка VIP
        $is_vip = isset($_POST['is_vip']) ? 1 : 0;
        
        // Передаем третий аргумент
        $manager->addServer($address, $name, $is_vip);

        header('Location: admin.php');
        exit;
    }
}

$servers = $manager->getAllServers();
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="admin-header">
        <a href="index.php" class="btn-back">← На главную</a>
        <a href="admin.php?logout=1" class="btn-logout">Выйти</a>
    </div>

    <div class="admin-container">
        <h2>Добавить новый сервер</h2>
        <form method="POST" class="admin-form">
            <input type="text" name="address" placeholder="IP-адрес" class="form-input">
            <input type="text" name="name" placeholder="Название сервера" class="form-input">
            <label style="display: flex; align-items: center; gap: 5px; color: #fff; cursor: pointer;">
                <input type="checkbox" name="is_vip" value="1"> VIP
            </label>
            <button type="submit" class="btn-submit">Добавить</button>
        </form>

        <h2>Существующие серверы</h2>
        <ul class="admin-list">
            <?php foreach ($servers as $server): ?>
                <li class="admin-item">
                    <div>
                        <strong><?= htmlspecialchars($server['name']) ?></strong>
                        <span class="admin-ip"><?= htmlspecialchars($server['address']) ?></span>
                    </div>
                    <a href="admin.php?delete=<?= $server['id'] ?>" class="btn-delete">Удалить</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</body>
</html>