<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Carrega as dependências externas do vendor (se houver)
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

require_once dirname(__DIR__) . '/app/Router.php';

use App\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\ChatController;
use App\Controllers\MessageController;
use App\Controllers\ServerController; 
use App\Controllers\ChannelController;

$router = new Router();

// 3. Telemetria de Rotas no Terminal do Termux
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
error_log("=== [ROUTER OPSEC] Mapeando: {$method} {$uri} ===");

# =+=+=+==+=+=+= ROTAS PÚBLICAS =+=+=+==+=+=+=
$router->get('/', [HomeController::class, 'index']);
$router->get('/signup', [UserController::class, 'renderSignup']);
$router->post('/signup', [UserController::class, 'signup']);
$router->get('/signin', [UserController::class, 'renderSignin']);
$router->post('/signin', [UserController::class, 'signin']);
$router->get('/forgot-password', [UserController::class, 'renderForgotPassword']);
$router->post('/forgot-password', [UserController::class, 'forgotPassword']);

# =+=+=+==+=+=+= ÁREA AUTENTICADA (DASHBOARD) =+=+=+==+=+=+=
$router->get('/dashboard', [ChatController::class, 'index']);
$router->get('/api/channels/{channelId}/messages', [ChatController::class, 'getHistory']);
$router->post('/api/channels/{channelId}/messages', [MessageController::class, 'create']);

# API de Servidores (CRUD)
$router->get('/api/servers', [ServerController::class, 'listByUser']);
$router->post('/api/servers', [ServerController::class, 'create']);
$router->post('/api/servers/{id}/edit', [ServerController::class, 'update']);
$router->post('/api/servers/{id}/delete', [ServerController::class, 'delete']);

# API de Canais
$router->get('/api/servers/{serverId}/channels', [ChannelController::class, 'listByServer']);
$router->post('/api/servers/{serverId}/channels', [ChannelController::class, 'create']);
$router->post('/api/channels/{id}/delete', [ChannelController::class, 'delete']);

# =+=+=+==+=+=+= FIREWALL DE SESSÃO (OPSEC) =+=+=+==+=+=+=
if (strpos($uri, '/dashboard') === 0 || strpos($uri, '/api') === 0) {
    \App\Middleware\AuthMiddleware::handle();
}

// 4. Despacha a requisição para o Controller correspondente
$router->dispatch($method, $uri);
