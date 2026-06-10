<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

function getConnection(): PDO
{
    static $pdo = null;
    
    if ($pdo === null) {
        $host   = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_NAME'] ?? 'farn_chat';
        $user   = $_ENV['DB_USER'] ?? 'root';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';
        $port   = $_ENV['DB_PORT'] ?? '3306';

        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo "Erro interno ao conectar ao banco de dados.";
            exit;
        }
    }
    
    return $pdo;
}
