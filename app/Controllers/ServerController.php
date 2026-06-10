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
     * Create a new server and automatically add the creator as a member.
     *
     * Expects JSON input:
     * - fullname (string, required): The server name
     * - description (string, optional): The server description
     *
     * @return void Outputs JSON response with server data or error message
     *
     * @throws \Throwable If database transaction fails
     */
    public function create(): void
    {
        header('Content-Type: application/json');
        
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $fullname = $input['fullname'] ?? '';
        $description = $input['description'] ?? null;

        if (empty($fullname)) {
            http_response_code(400);
            echo json_encode(['error' => 'O nome do servidor não pode ser vazio.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Insert the server record with the current user as owner
            $stmt = $this->db->prepare('
                INSERT INTO servers (owner_id, fullname, description)
                VALUES (:owner_id, :fullname, :description)
            ');
            $stmt->execute([
                ':owner_id'    => $userId,
                ':fullname'    => $fullname,
                ':description' => $description
            ]);

            $serverId = (int)$this->db->lastInsertId();

            // Automatically add the creator as a member so they can access the server
            $stmtMember = $this->db->prepare('
                INSERT INTO server_members (server_id, user_id)
                VALUES (:server_id, :user_id)
            ');
            $stmtMember->execute([
                ':server_id' => $serverId,
                ':user_id'   => $userId
            ]);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'data' => [
                    'id' => $serverId,
                    'fullname' => $fullname,
                    'description' => $description
                ]
            ]);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno.']);
        }
    }

    /**
     * Retrieve all servers where the authenticated user is a member.
     *
     * Returns a paginated list of servers from newest to oldest creation date.
     * Only includes servers where the user has an entry in server_members table.
     *
     * @return void Outputs JSON array of server objects or error message
     *
     * @throws \Throwable If database query fails
     */
    public function listByUser(): void
    {
        header('Content-Type: application/json');
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Não autenticado.']);
            return;
        }

        try {
            // Query uses INNER JOIN to ensure user is a member of each returned server
            $stmt = $this->db->prepare('
                SELECT s.id, s.fullname, s.description 
                FROM servers s
                JOIN server_members sm ON s.id = sm.server_id
                WHERE sm.user_id = :user_id
                ORDER BY s.created_at DESC
            ');
            $stmt->execute([':user_id' => $userId]);
            $servers = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $servers]);
            exit;
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno.']);
            exit;
        }
    }

    /**
     * Update server name and description.
     *
     * Expects JSON input:
     * - fullname (string, required): The new server name
     * - description (string, optional): The new server description
     *
     * @param string $id The server ID to update
     * @return void Outputs JSON success response or error message
     *
     * @throws Exception If database update fails
     */
    public function update(string $id): void
    {
        header('Content-Type: application/json');
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($userId <= 0 || !$this->ownsServer((int)$id, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            return;
        }
        
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
            echo json_encode(['error' => 'Erro interno.']);
        }
    }

    /**
     * Delete a server and all associated data.
     *
     * Cascading delete removes all channels, messages, and server members
     * due to ON DELETE CASCADE constraints defined in the database schema.
     * This is an atomic operation that cannot be partially rolled back.
     *
     * @param string $id The server ID to delete
     * @return void Outputs JSON success response or error message
     *
     * @throws Exception If database delete fails
     */
    public function delete(string $id): void
    {
        header('Content-Type: application/json');
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($userId <= 0 || !$this->ownsServer((int)$id, $userId)) {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado.']);
            return;
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM servers WHERE id = :id');
            $stmt->execute([':id' => (int)$id]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro interno.']);
        }
    }

    private function ownsServer(int $serverId, int $userId): bool
    {
        $stmt = $this->db->prepare(<<<SQL
            SELECT 1
            FROM servers
            WHERE id = :server_id
              AND owner_id = :user_id
            LIMIT 1
        SQL);

        $stmt->execute([
            ':server_id' => $serverId,
            ':user_id' => $userId,
        ]);

        return (bool) $stmt->fetchColumn();
    }
}
