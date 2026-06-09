<?php

namespace app\Controllers;

class HomeController
{
    /**
     * Exibe a página inicial do Farn Chat
     */
    public function index()
    {
        $pageTitle = "Farn Chat - Landing";
        $viewPath = dirname(__DIR__) . '/Views/landing.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            http_response_code(500);
            echo "Erro interno: A view da homepage não foi encontrada.";
        }
    }
}
