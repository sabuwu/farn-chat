<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    /**
     * Intercepta a requisição e valida a sessão.
     * Se não houver sessão ativa, bloqueia o fluxo imediatamente.
     */
    public static function handle(): void
    {
        // 1. Garante que a sessão está rodando na memória do PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2. Verifica se a credencial do usuário existe
        if (!isset($_SESSION['user_id'])) {
            
            $uri = $_SERVER['REQUEST_URI'];

            // Se for uma tentativa de invasão via API (nossos endpoints /api/...)
            // Respondemos com um JSON padrão e encerramos a execução (die).
            if (strpos($uri, '/api/') === 0) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'Acesso negado. Sessão inválida ou expirada.']);
                exit;
            }

            // Se for tentativa de acesso direto pelo navegador (ex: /dashboard)
            // Chutamos o intruso de volta para a tela de login
            header('Location: /signin');
            exit;
        }
    }
}
