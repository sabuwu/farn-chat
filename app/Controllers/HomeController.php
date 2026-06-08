<?php

namespace app\Controllers;

class HomeController
{
    /**
     * Exibe a página inicial do Farn Chat
     */
    public function index()
    {
        // Define o título da página ou outras variáveis que a view possa usar
        $pageTitle = "Farn Chat - Landing";

        // Caminho absoluto para a view da homepage baseado na árvore de diretórios do seu projeto
        $viewPath = __DIR__ . '/../Views/landing.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            // Tratamento simples de erro caso a view não seja encontrada
            http_response_code(500);
            echo "Erro interno: A view da homepage não foi encontrada.";
        }
    }
}
