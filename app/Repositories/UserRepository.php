<?php

declare(strict_types=1);

namespace app\Repositories;

use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        require_once dirname(__DIR__, 2) . '/config/database.php';

        $this->db = getConnection();
    }

    public function create(array $data): bool
    {
        $sql = <<<SQL
            INSERT INTO users (
                username,
                email,
                password_hash,
                bio
            )
            VALUES (
                :username,
                :email,
                :password_hash,
                :bio
            )
        SQL;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':bio'           => $data['bio'] ?? null,
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM users
            WHERE email = :email
            LIMIT 1
        SQL;

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = <<<SQL
            SELECT *
            FROM users
            WHERE username = :username
            LIMIT 1
        SQL;

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':username' => $username,
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function saveResetToken(
        int $userId,
        string $token,
        string $expiresAt
    ): bool {
        $sql = <<<SQL
            UPDATE users
            SET
                reset_token = :token,
                token_expires_at = :expires_at
            WHERE id = :id
        SQL;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':token'      => $token,
            ':expires_at' => $expiresAt,
            ':id'         => $userId,
        ]);
    }
}
