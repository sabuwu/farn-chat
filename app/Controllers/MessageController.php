<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;

class MessageController
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $this->db = \getConnection();
    }

    public function create(string $channelId): void
    {
        header('Content-Type: application/json');
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($userId <= 0 || !$this->canAccessChannel((int)$channelId, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $messageText = $input['content'] ?? '';

        if (empty($messageText)) {
            http_response_code(400);
            echo json_encode(['error' => 'A mensagem não pode estar vazia.']);
            return;
        }

        try {
            $stmt = $this->db->prepare('
                INSERT INTO messages (channel_id, user_id, message) 
                VALUES (:channel_id, :user_id, :message)
            ');
            
            $stmt->execute([
                ':channel_id' => (int)$channelId,
                ':user_id'    => $userId,
                ':message'    => $messageText
            ]);

            http_response_code(201);
            echo json_encode(['success' => true]);
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