<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Exception;

class ChannelController
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $this->db = \getConnection();
    }

    /**
     * CREATE: POST /api/servers/{serverId}/channels
     * Cria um novo canal de texto dentro de um servidor específico.
     */
    public function create(string $serverId): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $fullname = $input['fullname'] ?? '';

        // Limpa o nome do canal (padrão discord: minúsculo e sem espaços)
        $fullname = strtolower(str_replace(' ', '-', trim($fullname)));

        if (empty($fullname)) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome do canal não pode ser vazio.']);
            return;
        }

        try {
            $stmt = $this->db->prepare('
                INSERT INTO channels (server_id, fullname) 
                VALUES (:server_id, :fullname)
            ');
            $stmt->execute([
                ':server_id' => (int)$serverId,
                ':fullname'  => $fullname
            ]);

            http_response_code(201);
            echo json_encode(['success' => true, 'channel_id' => $this->db->lastInsertId()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar canal: ' . $e->getMessage()]);
        }
    }

    /**
     * READ: GET /api/servers/{serverId}/channels
     * Lista todos os canais pertencentes a um servidor específico.
     */
    public function listByServer(string $serverId): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('
                SELECT id, fullname, created_at 
                FROM channels 
                WHERE server_id = :server_id 
                ORDER BY fullname ASC
            ');
            $stmt->execute([':server_id' => (int)$serverId]);
            $channels = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $channels]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE: POST /api/channels/{id}/delete
     * Exclui um canal. O ON DELETE CASCADE limpa as mensagens dele automaticamente.
     */
    public function delete(string $id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('DELETE FROM channels WHERE id = :id');
            $stmt->execute([':id' => (int)$id]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
