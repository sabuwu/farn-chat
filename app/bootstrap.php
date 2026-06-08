<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. O Autoloader artesanal do farn-chat
spl_autoload_register(function (string $class): void {
    // O $class vem como "app\Router" ou "app\Controllers\UserController"
    // Mudamos para "app/Router.php" ou "app/Controllers/UserController.php"
    $classPath = dirname(__DIR__) . '/' . str_replace('\\', '/', $class) . '.php';

    if (file_exists($classPath)) {
        require_once $classPath;
    }
});

// 2. Carrega suas variáveis de ambiente do .env normalmente
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}
