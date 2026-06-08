<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Farn-Chat</title>
    <style>
        /* Reset e Estilos Gerais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #1f2937;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Card de Cadastro */
        .register-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 440px;
            padding: 40px 35px;
        }

        /* Cabeçalho do Formulário */
        .brand-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-top: 6px;
        }

        /* Grupos de Campos (Inputs) */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            color: #1f2937;
            background-color: #f9fafb;
            transition: all 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #4f46e5;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Linha Dupla (para senhas ficarem lado a lado se desejar, ou empilhadas) */
        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        /* Termos de Uso */
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 22px 0;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        .terms-checkbox input {
            margin-top: 3px;
            cursor: pointer;
        }

        .terms-checkbox a {
            color: #4f46e5;
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        /* Botão de Cadastro */
        .btn-register {
            width: 100%;
            background-color: #4f46e5;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-register:hover {
            background-color: #4338ca;
        }

        /* Links de Rodapé */
        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 14px;
            color: #6b7280;
        }

        .footer-text a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            alert("❌ Erro: <?php echo addslashes($_SESSION['error']); ?>");
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            alert("🚀 Sucesso: <?php echo addslashes($_SESSION['success']); ?>");
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>



    <div class="register-container">
        <div class="brand-header">
            <a href="/homepage" class="logo">Farn-Chat</a>
            <div class="subtitle">Crie sua conta e junte-se ao seu time</div>
        </div>

        <form action="/signup" method="POST">
            
            <div class="form-group">
                <label for="fullname">Nome Completo</label>
                <input type="text" id="fullname" name="fullname" placeholder="Ex: Marcelo Silva" required>
            </div>

            <div class="form-group">
                <label for="username">Nome de Usuário (@username)</label>
                <input type="text" id="username" name="username" placeholder="Ex: marcelosilva" required>
            </div>

            <div class="form-group">
                <label for="email">E-mail Corporativo ou Pessoal</label>
                <input type="email" id="email" name="email" placeholder="nome@provedor.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmar Senha</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" required>
                </div>
            </div>

            <label class="terms-checkbox">
                <input type="checkbox" name="terms" required>
                <span>Eu concordo com os <a href="#">Termos de Serviço</a> e com a <a href="#">Política de Privacidade</a> do Farn-Chat.</span>
            </label>

            <button type="submit" class="btn-register">Registrar Conta</button>
        </form>

        <div class="footer-text">
            Já possui uma conta? <a href="/signin">Fazer login</a>
        </div>
    </div>

</body>
</html>
