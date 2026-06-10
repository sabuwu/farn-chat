<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

class ChatController
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $this->db = \getConnection();
    }

    public function index(): void
    {
        require_once dirname(__DIR__, 2) . '/app/Views/dashboard.php';
    }

    public function getHistory(string $channelId): void
    {
        header('Content-Type: application/json');
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($userId <= 0 || !$this->canAccessChannel((int)$channelId, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            return;
        }
        
        try {
            $stmt = $this->db->prepare('
                SELECT m.message, m.created_at, u.username 
                FROM messages m
                JOIN users u ON m.user_id = u.id
                WHERE m.channel_id = :channel_id
                ORDER BY m.created_at ASC
            ');
            $stmt->execute([':channel_id' => (int)$channelId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $messages]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno.']);
        }
    }

    private function canAccessChannel(int $channelId, int $userId): bool
    {
        $stmt = $this->db->prepare(<<<SQL
            SELECT 1
            FROM channels c
            JOIN servers s ON s.id = c.server_id
            LEFT JOIN server_members sm ON sm.server_id = s.id AND sm.user_id = :user_id
            WHERE c.id = :channel_id
              AND (s.owner_id = :user_id OR sm.user_id IS NOT NULL)
            LIMIT 1
        SQL);

        $stmt->execute([
            ':channel_id' => $channelId,
            ':user_id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}