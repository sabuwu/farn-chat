<?php

declare(strict_types=1);

// Inicializa a sessão se ainda não foi aberta
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;

    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }
}

// REGISTRO DO AUTOLOADER DE TERRA ARRASADA
spl_autoload_register(function (string $className) {
    // 1. O Router manda: "App\Controllers\HomeController"
    // 2. Transforma as barras invertidas em barras de diretório: "App/Controllers/HomeController"
    $classPath = str_replace('\\', '/', $className);
    
    // 3. Se começar com "App/", nós arrancamos e trocamos pela pasta física "app/" em minúsculo
    if (strpos($classPath, 'App/') === 0) {
        $classPath = 'app/' . substr($classPath, 4);
    }
    
    // 4. Monta o caminho absoluto a partir da raiz do Codespaces
    $fullPath = dirname(__DIR__) . '/' . $classPath . '.php';
    
    // 5. Se o arquivo existir na pasta em minúsculo, carrega!
    if (file_exists($fullPath)) {
        require_once $fullPath;
    } else {
        // Se falhar, joga um log no terminal pra gente ver o caminho exato que ele tentou ler
        error_log("=== [AUTOLOADER ALERTA] Tentou ler o arquivo físico e não achou: {$fullPath} ===");
    }
});