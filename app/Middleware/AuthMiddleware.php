<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $uri = $_SERVER['REQUEST_URI'];
        $userId = $_SESSION['user_id'] ?? 'NENHUMA';

        error_log("=== [MIDDLEWARE] Verificando acesso para: {$uri} | User_ID na Sessão: {$userId} ===");

        if (!isset($_SESSION['user_id'])) {
            error_log("=== [MIDDLEWARE ALERTA] Bloqueado! Chutando intruso. ===");
            
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Acesso negado. Sessão inválida.']);
                exit;
            }

            header('Location: /signin');
            exit;
        }
    }
}
