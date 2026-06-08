<?php

declare(strict_types=1);

// 1. Carrega as dependências externas do vendor (se houver)
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

// 2. Carrega o bootstrap e o Router (com namespace App)
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/Router.php';
use App\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\ChatController;
use App\Controllers\MessageController;
use App\Controllers\ServerController; // Adicione no topo com os outros uses
use App\Controllers\ChannelController; // Importe no topo do arquivo!

$router = new Router();

# =+=+=+==+=+=+= Páginas Públicas =+=+=+==+=+=+=

# Agora a raiz '/' é a página de marketing (com descrição e botão de login)
$router->get('/', [HomeController::class, 'index']);

# ————————————————————————————————————————

# =+=+=+==+=+=+= Área Autenticada (Dashboard) =+=+=+==+=+=+=

# =+=+=+==+=+=+= Área Autenticada (Dashboard) =+=+=+==+=+=+=

# Esta é a rota que vai carregar o layout de 3 colunas que criamos em homepage.php
$router->get('/dashboard', [ChatController::class, 'index']);
    
# API que alimenta o chat via Ajax/Fetch
$router->get('/api/channels/{channelId}/messages', [ChatController::class, 'getHistory']);
$router->post('/api/channels/{channelId}/messages', [MessageController::class, 'create']);
# ————————————————————————————————————————

# =+=+=+==+=+=+= Signup =+=+=+==+=+=+=
# -------------- View --------------
$router->get('/signup', [UserController::class, 'renderSignup']);

# -------------- Controller --------------
$router->post('/signup', [UserController::class, 'signup']);

# ————————————————————————————————————————

# =+=+=+==+=+=+= Signin =+=+=+==+=+=+=
# -------------- View --------------
$router->get('/signin', [UserController::class, 'renderSignin']);

# -------------- Controller --------------
$router->post('/signin', [UserController::class, 'signin']);

# ————————————————————————————————————————

# =+=+=+==+=+=+= Forgot Password =+=+=+==+=+=+=
# -------------- View --------------
$router->get('/forgot-password', [UserController::class, 'renderForgotPassword']);

# -------------- Controller --------------
$router->post('/forgot-password', [UserController::class, 'forgotPassword']);

# ————————————————————————————————————————

// ... código anterior ...

# API de Servidores (CRUD)
$router->get('/api/servers', [ServerController::class, 'listByUser']);
$router->post('/api/servers', [ServerController::class, 'create']);
$router->post('/api/servers/{id}/edit', [ServerController::class, 'update']);
$router->post('/api/servers/{id}/delete', [ServerController::class, 'delete']);

// ... código anterior ...

# API de Servidores (CRUD)
$router->get('/api/servers', [ServerController::class, 'listByUser']);
$router->post('/api/servers', [ServerController::class, 'create']);
$router->post('/api/servers/{id}/edit', [ServerController::class, 'update']);
$router->post('/api/servers/{id}/delete', [ServerController::class, 'delete']);

# API de Canais (Focado em Comunidade)
$router->get('/api/servers/{serverId}/channels', [ChannelController::class, 'listByServer']);
$router->post('/api/servers/{serverId}/channels', [ChannelController::class, 'create']);
$router->post('/api/channels/{id}/delete', [ChannelController::class, 'delete']);
// ... código das suas rotas ...

// Captura o método e a URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// =+=+=+==+=+=+= FIREWALL DE SESSÃO (OPSEC) =+=+=+==+=+=+=

if (strpos($uri, '/dashboard') === 0 || strpos($uri, '/api') === 0) {
    \App\Middleware\AuthMiddleware::handle();
}
// =+=+=+==+=+=+= =+=+=+==+=+=+= =+=+=+==+=+=+= =+=+=+==+=+=
$router->dispatch($method, $uri);
