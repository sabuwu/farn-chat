<?php

declare(strict_types=1);

namespace App;

final class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    public function post(string $uri, callable|array $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    public function put(string $uri, callable|array $handler): void
    {
        $this->addRoute('PUT', $uri, $handler);
    }

    public function delete(string $uri, callable|array $handler): void
    {
        $this->addRoute('DELETE', $uri, $handler);
    }

    private function addRoute(string $method, string $uri, callable|array $handler): void
    {
        // Converte {param} em um grupo de captura Regex: (?P<param>[a-zA-Z0-9_-]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $uri);
        $pattern = '#^' . $pattern . '$#';
        
        $this->routes[$method][$pattern] = $handler;
    }

        public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            if (preg_match($pattern, $path, $matches)) {
                
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                try {
                    if (is_array($handler)) {
                        [$controller, $action] = $handler;
                        
                        // Telemetria bruta para sabermos se o PHP conseguiu instanciar a classe
                        error_log("=== [ROUTER MATCH] Invocando: {$controller}->{$action} ===");
                        
                        $instance = new $controller();
                        $instance->$action(...$params);
                        return;
                    }

                    $handler(...$params);
                    return;
                } catch (\Throwable $e) {
                    // Se o Controller falhar internamente, o Roteador bota a boca no trombone
                    error_log("=== [ROUTER FATAL] O Controller capotou internamente: " . $e->getMessage() . " ===");
                    http_response_code(500);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }
            }
        }

        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Route not found']);
        exit;
    }

}
