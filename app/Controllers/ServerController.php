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
     
             /**
     * READ: GET /api/servers
     * Lista todos os servidores em que o usuário logado é membro.
     */
    public function listByUser(): void
    {
        header('Content-Type: application/json');
        
        // Captura o usuário logado na sessão (o usuário 6)
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if (!$userId) {
            error_log("=== [READ SERVER ALERTA] listByUser chamado sem usuário logado na sessão ===");
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado.']);
            return;
        }

        try {
            // Esta query exige que o usuário exista na tabela 'server_members'
            $stmt = $this->db->prepare('
                SELECT s.id, s.fullname, s.description 
                FROM servers s
                JOIN server_members sm ON s.id = sm.server_id
                WHERE sm.user_id = :user_id
                ORDER BY s.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
            $servers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // TELEMETRIA: Mostra no Termux quantos servidores foram achados para o usuário 6
            error_log("=== [READ SERVER] Encontrados " . count($servers) . " servidores para o User ID: {$userId} ===");

            echo json_encode(['success' => true, 'data' => $servers]);
            exit;
        } catch (\Throwable $e) {
            error_log("=== [READ SERVER ERRO] Falha ao listar: " . $e->getMessage() . " ===");
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }


    
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
