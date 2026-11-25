<?php
declare(strict_types=1);
session_start();


require_once '../vendor/autoload.php';

// 1. Подключаем класс (файл лежит на уровень выше, в папке src)
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

// 3. Говорим, что используем класс из пространства имен App
use App\ServerManager;

// 4. Создаем объект и получаем данные
$manager = new ServerManager($pdo);
$servers = $manager->getAllServers();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мониторинг серверов</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="page-header">
        <h1>Мониторинг серверов</h1>
        
        <div class="header-actions">
            <a href="admin.php" class="btn-admin">⚙️ Управление серверами</a>
            
            <?php if (isset($_SESSION['admin'])): ?>
                <span class="admin-badge">👑 Вы вошли как Админ</span>
            <?php endif; ?>
        </div>
    </header>

    <div class="grid">
        <?php foreach ($servers as $server): ?>
            <?php $status = $manager->getServerStatus($server['address']); ?>
            
            <!-- Если is_vip == 1, добавляем класс premium-vip -->
            <div class="server-card <?php if ($server['is_vip'] == 1) echo 'premium-vip'; ?>">
                
                <!-- Бейдж VIP -->
                <?php if ($server['is_vip'] == 1): ?>
                    <div class="premium-badge vip">💎 VIP</div>
                <?php endif; ?>

                <!-- 3D Кубик -->
                <div class="cube-container">
                    <div class="cube">
                        <div class="face front tex-grass"></div><div class="face back tex-grass"></div>
                        <div class="face right tex-grass"></div><div class="face left tex-grass"></div>
                        <div class="face top tex-grass"></div><div class="face bottom tex-grass"></div>
                    </div>
                </div>

                <div class="card-header">
                    <h3>
                        <?php if ($status !== false && !empty($status['icon'])): ?>
                            <img src="<?= htmlspecialchars($status['icon']) ?>" class="server-icon" alt="icon">
                        <?php else: ?>
                            <div class="server-icon"><?= strtoupper(substr($server['name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        
                        <!-- Имя сервера всегда идет после иконки -->
                        <span><?= htmlspecialchars($server['name']) ?></span>
                    </h3>
                    <div class="mode"><?= htmlspecialchars($server['address']) ?></div>
                </div>

                <div class="card-info">
                    <div class="online-row">
                        <?php if ($status !== false): ?>
                            <div class="online"><div class="dot"></div> <?= $status['players'] ?></div>
                            <div class="max">/ <?= $status['max_players'] ?></div>
                        <?php else: ?>
                            <div class="online" style="color: #ef4444;">Оффлайн</div>
                            <div class="max">/ 0</div>
                        <?php endif; ?>
                    </div>
                    <div class="motd">
                        <?php if ($status !== false): ?>
                            <?= htmlspecialchars($status['motd']) ?>
                        <?php else: ?>
                            Сервер временно не доступен
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="ip-box"><?= htmlspecialchars($server['address']) ?></div>
                    <a href="minecraft://connect?ip=<?= htmlspecialchars($server['address']) ?>" class="btn-play">▶ Играть</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


</body>
</html>
