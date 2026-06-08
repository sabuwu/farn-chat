<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Farn-Chat</title>
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

        /* Card */
        .recovery-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 400px;
            padding: 40px 35px;
        }

        /* Cabeçalho */
        .brand-header {
            text-align: center;
            margin-bottom: 25px;
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
            margin-top: 8px;
            line-height: 1.4;
        }

        /* Input */
        .form-group {
            margin-bottom: 22px;
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

        /* Botão */
        .btn-recovery {
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

        .btn-recovery:hover {
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

    <div class="recovery-container">
        <div class="brand-header">
            <a href="/homepage" class="logo">Farn-Chat</a>
            <div class="subtitle">Esqueceu sua senha? Digite seu e-mail abaixo para receber as instruções de recuperação.</div>
        </div>

        <form action="/forgot-password" method="POST">
            <div class="form-group">
                <label for="email">E-mail Cadastrado</label>
                <input type="email" id="email" name="email" placeholder="nome@provedor.com" required>
            </div>

            <button type="submit" class="btn-recovery">Enviar Link de Recuperação</button>
        </form>

        <div class="footer-text">
            <a href="/signin">Voltar para o login</a>
        </div>
    </div>

</body>
</html>
