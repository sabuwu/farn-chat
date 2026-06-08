<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farn-Chat - Homepage</title>
    <style>
        /* Reset e Estilos de Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Barra de Navegação */
        header {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
        }

        .nav-links a {
            text-decoration: none;
            color: #4b5563;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #4f46e5;
        }

        .btn-primary {
            background-color: #4f46e5;
            color: white;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s;
            text-align: center;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        /* Seção Principal (Hero) */
        .hero {
            padding: 80px 0;
            text-align: center;
            background: linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
        }

        .hero h1 {
            font-size: 46px;
            color: #1f2937;
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .hero p {
            font-size: 18px;
            color: #6b7280;
            max-width: 650px;
            margin: 0 auto 35px auto;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 60px;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #4b5563;
            padding: 10px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #d1d5db;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        /* Preview da Interface do Chat (Mockup View) */
        .mockup {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            height: 480px;
            overflow: hidden;
        }

        .mockup-sidebar {
            width: 240px;
            background-color: #1e1b4b;
            color: #e0e7ff;
            padding: 20px;
            text-align: left;
            display: flex;
            flex-direction: column;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 25px;
            color: #ffffff;
        }

        .mockup-channels {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mockup-channel {
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 14px;
            opacity: 0.75;
            cursor: default;
        }

        .mockup-channel.active {
            background-color: #4338ca;
            opacity: 1;
            font-weight: 600;
        }

        .mockup-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            width: 100%;
        }

        .mockup-header {
            height: 60px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            padding: 0 25px;
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
        }

        .mockup-messages {
            flex: 1;
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
            text-align: left;
        }

        .message {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .msg-body {
            display: flex;
            flex-direction: column;
            gap: 4px;
            max-width: 85%;
        }

        .msg-username {
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
        }

        .msg-content {
            background-color: #f3f4f6;
            padding: 10px 16px;
            border-radius: 0 12px 12px 12px;
            font-size: 14px;
            color: #1f2937;
            word-break: break-word;
        }

        .message.own {
            flex-direction: row-reverse;
        }

        .message.own .msg-body {
            align-items: flex-end;
        }

        .message.own .msg-content {
            background-color: #e0e7ff;
            color: #1e1b4b;
            border-radius: 12px 0 12px 12px;
        }

        .mockup-input {
            padding: 20px 25px;
            border-top: 1px solid #e5e7eb;
        }

        .mockup-input-box {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            height: 44px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: #9ca3af;
            font-size: 14px;
        }

        /* Seção de Recursos (Features) */
        .features {
            padding: 90px 0;
            background-color: #ffffff;
        }

        .section-title {
            text-align: center;
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 50px;
            font-weight: 700;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .card {
            background-color: #f9fafb;
            padding: 35px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.04);
        }

        .card h3 {
            margin-bottom: 12px;
            color: #111827;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card p {
            color: #6b7280;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Rodapé (Footer) */
        footer {
            background-color: #111827;
            color: #9ca3af;
            padding: 40px 0;
            text-align: center;
            font-size: 14px;
            border-top: 1px solid #1f2937;
        }

        /* ==========================================
           REGRAS DE RESPONSIVIDADE (MOBILE / TABLET)
           ========================================== */

        @media (max-width: 768px) {
            /* Ajustes do Menu */
            .nav-container {
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
                gap: 12px;
            }
            .nav-links {
                gap: 15px;
                font-size: 14px;
            }

            /* Ajustes da Seção Hero */
            .hero {
                padding: 50px 0;
            }
            .hero h1 {
                font-size: 32px;
                line-height: 1.2;
            }
            .hero p {
                font-size: 16px;
                margin-bottom: 25px;
            }
            .hero-buttons {
                flex-direction: column;
                gap: 10px;
                max-width: 300px;
                margin: 0 auto 40px auto;
            }

            /* Transformação do Mockup em Chat Mobile */
            .mockup {
                height: 420px;
            }
            .mockup-sidebar {
                display: none; /* Esconde a barra lateral no mobile para focar no chat */
            }
            .mockup-header {
                padding: 0 15px;
                height: 50px;
            }
            .mockup-messages {
                padding: 15px;
                gap: 15px;
            }
            .mockup-input {
                padding: 15px;
            }
            
            /* Ajustes de Recursos */
            .features {
                padding: 60px 0;
            }
            .section-title {
                font-size: 26px;
                margin-bottom: 35px;
            }
            .card {
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 26px;
            }
            .grid {
                grid-template-columns: 1fr; /* Força uma coluna única em telas muito pequenas */
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="nav-container">
            <a href="#" class="logo">Farn-Chat</a>
            <nav>
                <ul class="nav-links">
                    <li><a href="#features">Recursos</a></li>
                    <li><a href="#">Preços</a></li>
                    <li><a href="#">Comunidade</a></li>
                </ul>
            </nav>
            <a href="/signin" class="btn-primary">Acessar App</a>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>Comunicação inteligente para times modernos</h1>
            <p>Conecte seus grupos, organize canais de tópicos e mantenha suas conversas fluindo em uma interface rápida, limpa e centralizada.</p>
            <div class="hero-buttons">
                <a href="/signin" class="btn-primary">Entrar</a>
                <a href="#" class="btn-secondary">Explorar Recursos</a>
            </div>

            <div class="mockup">
                <div class="mockup-sidebar">
                    <div class="sidebar-title">Farn Workspace</div>
                    <div class="mockup-channels">
                        <div class="mockup-channel active"># geral</div>
                        <div class="mockup-channel"># avisos-importantes</div>
                        <div class="mockup-channel"># projetos-2026</div>
                        <div class="mockup-channel"># feedback-ideias</div>
                    </div>
                </div>
                <div class="mockup-main">
                    <div class="mockup-header"># geral</div>
                    <div class="mockup-messages">
                        
                        <div class="message">
                            <div class="avatar" style="background-color: #ec4899;"></div>
                            <div class="msg-body">
                                <span class="msg-username">Mariana Santos</span>
                                <div class="msg-content">Olá equipe! Sejam muito bem-vindos ao novo espaço do Farn-Chat.</div>
                            </div>
                        </div>

                        <div class="message">
                            <div class="avatar" style="background-color: #0ea5e9;"></div>
                            <div class="msg-body">
                                <span class="msg-username">Carlos Eduardo</span>
                                <div class="msg-content">Ficou excelente! A navegação pelos canais laterais é bem intuitiva.</div>
                            </div>
                        </div>

                        <div class="message own">
                            <div class="msg-body">
                                <span class="msg-username">Você</span>
                                <div class="msg-content">Perfeito! O objetivo é manter nosso fluxo focado e as discussões organizadas por tópicos.</div>
                            </div>
                        </div>

                    </div>
                    <div class="mockup-input">
                        <div class="mockup-input-box">Conversar em # geral...</div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Construído para simplificar conversas</h2>
            <div class="grid">
                <div class="card">
                    <h3>⚡ Chat Ultra Veloz</h3>
                    <p>Envie mensagens, reações e mídias instantaneamente, sem atrasos ou travamentos na sincronização.</p>
                </div>
                <div class="card">
                    <h3>📁 Organização Estruturada</h3>
                    <p>Crie canais públicos ou privados para focar em assuntos específicos, eliminando o ruído das conversas em massa.</p>
                </div>
                <div class="card">
                    <h3>🛡️ Segurança de Ponta</h3>
                    <p>Criptografia ativa e controle de permissões rígidos para manter as interações da sua comunidade totalmente seguras.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2026 Farn-Chat. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>
</html>
