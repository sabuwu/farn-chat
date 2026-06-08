<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use Exception;

class ServerController
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';
        $this->db = \getConnection();
    }

    /**
     * CREATE: POST /servers
     * Cria um novo servidor e automaticamente adiciona o criador como membro.
     */
    public function create(): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $fullname = $input['fullname'] ?? '';
        $description = $input['description'] ?? null;
        $ownerId = $_SESSION['user_id'] ?? 1; // Mock defensivo enquanto o Auth finaliza

        if (empty($fullname)) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome do servidor é obrigatório.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // 1. Insere o servidor
            $stmt = $this->db->prepare('
                INSERT INTO servers (owner_id, fullname, description) 
                VALUES (:owner_id, :fullname, :description)
            ');
            $stmt->execute([
                ':owner_id'    => $ownerId,
                ':fullname'    => $fullname,
                ':description' => $description
            ]);

            $serverId = (int)$this->db->lastInsertId();

            // 2. Cria automaticamente o canal #geral para o novo servidor
            $stmtChannel = $this->db->prepare('
                INSERT INTO channels (server_id, fullname) 
                VALUES (:server_id, :fullname)
            ');
            $stmtChannel->execute([
                ':server_id' => $serverId,
                ':fullname'  => 'geral'
            ]);

            // 3. Vincula o criador como membro do servidor
            $stmtMember = $this->db->prepare('
                INSERT INTO server_members (server_id, user_id) 
                VALUES (:server_id, :user_id)
            ');
            $stmtMember->execute([
                ':server_id' => $serverId,
                ':user_id'   => $ownerId
            ]);

            $this->db->commit();
            http_response_code(201);
            echo json_encode(['success' => true, 'server_id' => $serverId]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Falha ao criar servidor: ' . $e->getMessage()]);
        }
    }

    /**
     * READ: GET /api/servers
     * Lista todos os servidores em que o usuário logado é membro (para a ServerSidebar).
     */
    public function listByUser(): void
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? 1;

        try {
            $stmt = $this->db->prepare('
                SELECT s.id, s.fullname, s.description 
                FROM servers s
                JOIN server_members sm ON s.id = sm.server_id
                WHERE sm.user_id = :user_id
                ORDER BY s.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
            $servers = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => $servers]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * UPDATE: POST /servers/{id}/edit
     * Atualiza as configurações de nome e descrição do servidor.
     */
    public function update(string $id): void
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $fullname = $input['fullname'] ?? '';
        $description = $input['description'] ?? null;

        if (empty($fullname)) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome do servidor não pode ser vazio.']);
            return;
        }

        try {
            $stmt = $this->db->prepare('
                UPDATE servers 
                SET fullname = :fullname, description = :description 
                WHERE id = :id
            ');
            $stmt->execute([
                ':fullname'    => $fullname,
                ':description' => $description,
                ':id'          => (int)$id
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * DELETE: POST /servers/{id}/delete
     * Remove o servidor. Graças ao ON DELETE CASCADE do schema, 
     * canais, mensagens e membros caem juntos automaticamente.
     */
    public function delete(string $id): void
    {
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare('DELETE FROM servers WHERE id = :id');
            $stmt->execute([':id' => (int)$id]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
