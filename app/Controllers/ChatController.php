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
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}