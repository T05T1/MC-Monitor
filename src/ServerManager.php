<?php
declare(strict_types=1);

namespace App; // Объявляем пространство имен

use PDO;
use Throwable;
use MCServerStatus\MCPing;

class ServerManager {
    public PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAllServers(): array {
        $stmt = $this->pdo->prepare("SELECT * FROM servers");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addServer(string $address, string $name, int $is_vip = 0): void {
        // Мы добавили is_vip в запрос!
        $stmt = $this->pdo->prepare("INSERT INTO servers (address, name, is_vip) VALUES (:address, :name, :is_vip)");
        $stmt->execute([
            ':address' => $address,
            ':name' => $name,
            ':is_vip' => $is_vip
        ]);
    }

    public function deleteServer(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM servers WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getServerStatus(string $address): array|false {
        try {
            // Убираем лишние пробелы по краям, если они есть в базе
            $address = trim($address);
            
            $response = @MCPing::check($address);

            // Если сервер не вернул статус "онлайн", возвращаем false
            if (!$response->online) {
                return false;
            }

            $cleanMotd = preg_replace('/§[0-9a-fklmnor]/i', '', $response->motd ?? '');

            return [
                'online' => $response->online,
                'players' => $response->players ?? 0,
                'max_players' => $response->max_players ?? 0,
                'version' => $response->version ?? 'Неизвестно',
                'ping' => $response->ping ?? 0,
                'motd' => $cleanMotd,
                'icon' => $response->favicon ?? null
            ];

        } catch (Throwable $e) {
            //echo "Ошибка для " . $address . ": " . $e->getMessage() . "<br>";
            return false;
        }
    }
}