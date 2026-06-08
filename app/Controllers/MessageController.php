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
                ':user_id'    => 1, // ID fixo para teste até termos o Auth 100%
                ':message'    => $messageText
            ]);

            http_response_code(201);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}