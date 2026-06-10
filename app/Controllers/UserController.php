<?php

declare(strict_types=1);

namespace app\Controllers;

use app\Services\UserService;
use Exception;

final class UserController
{
    private UserService $userService;

    public function __construct()
    {
        // Inicializa a camada de serviço responsável pelas validações de negócio
        $this->userService = new UserService();
        
        // Garante que a sessão está ativa no ciclo do Termux para trafegar erros/sucessos
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Renderiza a tela de cadastro (Sign-Up)
     */
    public function renderSignup(): void
    {      
        $pageTitle = "Criar Conta - Farn-Chat";
        $viewPath = dirname(__DIR__) . '/Views/auth/signup.php';

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            http_response_code(500);
            echo "Janela de cadastro não encontrada... ;-; (Caminho esperado: {$viewPath})";
        }
    }
    
    /**
     * Renderiza a tela de login (Sign-In)
     */
    public function renderSignin(): void 
    {
        $pageTitle = "Entrar - Farn-Chat";
        $viewPath = dirname(__DIR__) . '/Views/auth/signin.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            http_response_code(500);
            echo "Janela de login não encontrada... ;-; (Caminho esperado: {$viewPath})";
        }
    }
    
    /**
     * Renderiza a tela de recuperação de senha (Forgot Password)
     */
    public function renderForgotPassword(): void 
    {
        $pageTitle = "Esqueci minha senha - Farn-Chat";
        $viewPath = dirname(__DIR__) . '/Views/auth/forgot-password.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            http_response_code(500);
            echo "Janela de recuperação não encontrada... ;-; (Caminho esperado: {$viewPath})";
        }
    }

    /**
     * Renderiza a tela de redefinição de senha
     */
    public function renderResetPassword(): void
    {
        $pageTitle = 'Redefinir senha - Farn-Chat';
        $viewPath = dirname(__DIR__) . '/Views/auth/reset-password.php';
        $token = trim((string) ($_GET['token'] ?? ''));

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            http_response_code(500);
            echo "Janela de redefinição não encontrada... ;-; (Caminho esperado: {$viewPath})";
        }
    }
    
    /**
     * Processa a requisição POST para criação de conta
     */
    public function signup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /signup');
            exit();
        }

        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Usuário, e-mail e senha são obrigatórios... ;-;";
            $this->renderSignup();
            exit();
        }

        try {
            // Repassa os dados brutos para o bicho-pau do Service aplicar o hash e validar o banco
            $this->userService->register([
                'fullname' => $fullname,
                'username' => $username,
                'email'    => $email,
                'password' => $password
            ]);

            $_SESSION['success'] = "Conta criada com sucesso! Faça o seu login. 🚀";
            
            // Sucesso absoluto: Redirecionamos para o login para limpar os cabeçalhos de POST
            header('Location: /signin');
            exit();
        } catch (Exception $e) {
            // Se o e-mail ou o username já existirem, cai aqui
            $_SESSION['error'] = $e->getMessage();
            
            // CHAMA A VIEW DE VOLTA: Redesenha a tela injetando o erro guardado na sessão
            $this->renderSignup();
            exit();
        }
    }
    
    /**
     * Processa a requisição POST para efetuar a autenticação
     */
    public function signIn(): void 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /signin');
            exit();
        }
    
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
    
        if (!$email || empty($password)) {
            $_SESSION['error'] = 'Por favor, preencha todos os campos... ;-;';
            $this->renderSignin();
            exit();
        }
    
        try {
            // Valida as credenciais na UserService
            $userData = $this->userService->authenticate($email, $password);
    
            // Regenera o ID por OpSec (evita Session Fixation) antes de injetar dados confidenciais
            session_regenerate_id(true);
    
            $_SESSION['user_id']   = $userData['id'];
            $_SESSION['user_name'] = $userData['name'];
    
            // Redireciona o usuário autenticado para a homepage do farn-chat
            header('Location: /dashboard');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            
            // CHAMA A VIEW DE VOLTA: Erro de senha ou e-mail redesenha a tela de Sign-In
            $this->renderSignin();
            exit();
        }
    }

    /**
     * Processa a requisição POST para gerar o link de recuperação de senha
     */
    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        if (!$email) {
            $_SESSION['error'] = 'Insira um e-mail válido... ;-;';
            $this->renderForgotPassword();
            exit();
        }

        try {
            $this->userService->generatePasswordReset($email);
            $_SESSION['success'] = 'Se a conta existir, um link de recuperação foi gerado.';
            header('Location: /forgot-password');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = 'Não foi possível processar a solicitação agora.';
            $this->renderForgotPassword();
            exit();
        }
    }

    /**
     * Processa a redefinição de senha via token
     */
    public function resetPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /forgot-password');
            exit();
        }

        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        try {
            $this->userService->resetPassword($token, $password, $passwordConfirmation);

            $_SESSION['success'] = 'Senha atualizada com sucesso. Faça login novamente.';
            header('Location: /signin');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /reset-password?token=' . urlencode($token));
            exit();
        }
    }
}
