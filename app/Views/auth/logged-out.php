<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sessão Encerrada - Farn-Chat</title>
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
        .logout-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 400px;
            padding: 45px 35px;
            text-align: center;
        }

        /* Ícone de Sucesso/Saída em Pure CSS */
        .icon-box {
            width: 64px;
            height: 64px;
            background-color: #e0e7ff;
            color: #4f46e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px auto;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
        }

        .message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        /* Botão principal */
        .btn-login-again {
            display: block;
            background-color: #4f46e5;
            color: white;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
            margin-bottom: 15px;
        }

        .btn-login-again:hover {
            background-color: #4338ca;
        }

        /* Link secundário */
        .back-home {
            font-size: 14px;
            color: #4b5563;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .back-home:hover {
            color: #4f46e5;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="icon-box">✓</div>

        <h2>Sessão Encerrada</h2>
        <p class="message">Você saiu da sua conta no <strong>Farn-Chat</strong> com segurança. Até logo!</p>

        <a href="#" class="btn-login-again">Fazer Login Novamente</a>
        <a href="#" class="back-home">Ir para a Página Inicial</a>
    </div>

</body>
</html>
