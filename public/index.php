<?php

declare(strict_types=1);

// 1. Inicialização defensiva de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. AUTOLOADER EMbutido - Joga as classes na memória respeitando o case minúsculo
spl_autoload_register(function (string $className) {
    $classPath = str_replace('\\', '/', $className);
    $fullPath = dirname(__DIR__) . '/' . $classPath . '.php';
    
    if (file_exists($fullPath)) {
        require_once $fullPath;
    } else {
        error_log("=== [AUTOLOADER] Arquivo ausente: {$fullPath} ===");
    }
});

// 3. Dependências de pacotes (Composer) se existirem
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// Tudo estritamente conversando em "app" minúsculo
use app\Router;
use app\Controllers\HomeController;
use app\Controllers\UserController;
use app\Controllers\ChatController;
use app\Controllers\MessageController;
use app\Controllers\ServerController; 
use app\Controllers\ChannelController;

$router = new Router();

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
error_log("=== [ROUTER OPSEC] Mapeando: {$method} {$uri} ===");

# =+=+=+==+=+=+= ROTAS PÚBLICAS =+=+=+==+=+=+=
$router->get('/', [HomeController::class, 'index']);
$router->get('/signup', [UserController::class, 'renderSignup']);
$router->post('/signup', [UserController::class, 'signup']);
$router->get('/signin', [UserController::class, 'renderSignin']);
$router->post('/signin', [UserController::class, 'signIn']);
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
    \app\Middleware\AuthMiddleware::handle();
}

$router->dispatch($method, $uri);
