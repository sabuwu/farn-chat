<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farn - Comunidades</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body { background-color: #0F172A; }
    </style>
</head>
<body class="text-slate-200 font-sans h-screen flex flex-col overflow-hidden">

    <header class="bg-slate-900/80 backdrop-blur border-b border-slate-800 px-6 py-4 flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 text-white font-black px-3 py-1.5 rounded-lg tracking-wider text-xl shadow-lg shadow-indigo-500/20">farn-chat</div>
        </div>
        <div class="flex items-center gap-4">
            <button id="btn-new-server" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all shadow-md shadow-indigo-600/10 active:scale-95">
                + Nova Comunidade
            </button>
            <div class="w-9 h-9 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center font-bold text-sm text-indigo-400 select-none">
                SB
            </div>
        </div>
    </header>

    <main class="flex flex-1 overflow-hidden p-6 gap-6">

        <section class="w-72 bg-slate-900/50 border border-slate-800 rounded-2xl p-4 flex flex-col gap-6 shrink-0 backdrop-blur-sm">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3 px-2">Suas Comunidades</h2>
                <div id="server-list" class="flex flex-col gap-1 overflow-y-auto max-h-48 pr-1">
                    <p class="text-xs text-slate-600 px-2 py-1 animate-pulse">Carregando espaços...</p>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-h-0 border-t border-slate-800/60 pt-4">
                <div class="flex justify-between items-center mb-3 px-2">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500">Canais do Espaço</h2>
                    <button id="btn-new-channel" class="text-slate-500 hover:text-indigo-400 text-lg transition-colors p-1 hidden" title="Criar Canal">+</button>
                </div>
                <div id="channel-list" class="flex flex-col gap-1 overflow-y-auto flex-1 pr-1">
                    <p class="text-xs text-slate-600 px-2 py-1 italic">Selecione uma comunidade para ver os canais.</p>
                </div>
            </div>
        </section>

        <section class="flex-1 bg-slate-900/30 border border-slate-800 rounded-2xl flex flex-col overflow-hidden backdrop-blur-sm">
            
            <div class="px-6 py-4 border-b border-slate-800/60 bg-slate-900/20 flex justify-between items-center shrink-0">
                <div>
                    <h1 id="active-space-title" class="font-bold text-slate-100 text-lg tracking-tight">Mural Central</h1>
                    <p id="active-space-desc" class="text-xs text-slate-500 mt-0.5">Selecione um canal para iniciar a transmissão.</p>
                </div>
                <div id="space-actions" class="flex gap-2 hidden">
                    <button id="btn-edit-server" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-md border border-slate-700 transition-all">Configurar</button>
                    <button id="btn-delete-server" class="text-xs bg-red-950/40 hover:bg-red-900/60 text-red-400 px-3 py-1.5 rounded-md border border-red-900/50 transition-all">Destruir</button>
                </div>
            </div>

            <div id="chat-feed" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                    <div class="w-16 h-16 rounded-2xl bg-slate-800/50 border border-slate-700/60 flex items-center justify-center text-slate-500 text-2xl mb-4 font-mono">:/</div>
                    <h3 class="font-medium text-slate-400 text-sm">Nenhum canal sintonizado</h3>
                    <p class="text-xs text-slate-600 max-w-xs mt-1">Escolha um canal na barra lateral para carregar o histórico de transmissões de dados.</p>
                </div>
            </div>

            <div id="chat-footer" class="p-4 bg-slate-900/40 border-t border-slate-800/60 shrink-0 hidden">
                <div class="bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 flex gap-4 shadow-inner focus-within:border-indigo-500/50 transition-all">
                    <input type="text" id="message-input" placeholder="Transmitir mensagem..." class="flex-1 bg-transparent text-slate-200 placeholder-slate-600 text-sm outline-none" autocomplete="off" />
                    <button type="button" id="btn-send-message" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold px-4 py-1.5 rounded-md transition-all active:scale-95">Enviar</button>
                </div>
            </div>

        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let activeServerId = null;
        let activeChannelId = null;
        let lastMessageCount = 0; 
        let pollingInterval = null; 

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');

        const serverList = document.getElementById('server-list');
        const channelList = document.getElementById('channel-list');
        const chatFeed = document.getElementById('chat-feed');
        const chatFooter = document.getElementById('chat-footer');
        const messageInput = document.getElementById('message-input');
        const activeSpaceTitle = document.getElementById('active-space-title');
        const activeSpaceDesc = document.getElementById('active-space-desc');
        const spaceActions = document.getElementById('space-actions');
        const btnNewChannel = document.getElementById('btn-new-channel');
        const btnSendMessage = document.getElementById('btn-send-message');

        const myUsername = 'sabu'; 

        async function fetchChatMessages() {
            if (!activeChannelId) return;

            try {
                const response = await fetch(`/api/channels/${activeChannelId}/messages`);
                const res = await response.json();
                const messages = res.data || [];

                if (Array.isArray(messages) && messages.length !== lastMessageCount) {
                    lastMessageCount = messages.length;
                    chatFeed.innerHTML = ''; 

                    if (messages.length === 0) {
                        chatFeed.innerHTML = `
                            <div class="flex-1 flex flex-col items-center justify-center text-slate-600 text-xs italic">
                                O silêncio reina por aqui... Transmita a primeira mensagem!
                            </div>`;
                        return;
                    }

                    messages.forEach(msg => {
                        const author = msg.username || msg.user_id || 'Anônimo';
                        const text = msg.message || msg.content || '';
                        const time = msg.created_at || msg.timestamp || '';
                        const avatar = String(author).substring(0, 2).toUpperCase();

                        const msgElement = document.createElement('div');
                        msgElement.className = "flex gap-4 items-start bg-slate-900/20 border border-slate-800/40 p-3 rounded-xl hover:border-slate-700/50 transition-colors";
                        msgElement.innerHTML = `
                            <div class="w-9 h-9 rounded-lg bg-indigo-950 border border-indigo-800/60 flex-shrink-0 flex items-center justify-center text-indigo-400 font-bold text-xs select-none">
                                ${avatar}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-semibold text-slate-200 text-sm hover:underline cursor-pointer">${escapeHtml(author)}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">${escapeHtml(time)}</span>
                                </div>
                                <p class="text-slate-300 text-sm mt-1 break-words leading-relaxed">${escapeHtml(text)}</p>
                            </div>
                        `;
                        chatFeed.appendChild(msgElement);
                    });

                    chatFeed.scrollTop = chatFeed.scrollHeight;
                }
            } catch (err) {}
        }

        async function sendMessage() {
            const content = messageInput.value.trim();
            if (!content || !activeChannelId) return;

            messageInput.value = ''; 

            try {
                await fetch(`/api/channels/${activeChannelId}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: content, username: myUsername })
                });
                
                fetchChatMessages();
            } catch (err) {}
        }

        if (btnSendMessage) {
            btnSendMessage.onclick = (e) => {
                e.preventDefault(); 
                sendMessage();
            };
        }

        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); 
                sendMessage();
            }
        });

        function selectChannel(channelId, channelName) {
            activeChannelId = channelId;
            lastMessageCount = 0; 

            document.querySelectorAll('#channel-list button').forEach(b => b.classList.remove('bg-slate-800', 'text-indigo-400', 'font-semibold'));
            
            chatFooter.classList.remove('hidden');
            chatFeed.innerHTML = `<div class="text-xs text-slate-500 font-mono italic animate-pulse p-4">Sintonizando frequência de #${escapeHtml(channelName)}...</div>`;
            messageInput.placeholder = `Transmitir em #${channelName}`;

            if (pollingInterval) clearInterval(pollingInterval);

            fetchChatMessages();
            pollingInterval = setInterval(fetchChatMessages, 2000);
        }

        async function loadServers() {
            try {
                const response = await fetch('/api/servers');
                const res = await response.json();
                
                if (!res.success || res.data.length === 0) {
                    serverList.innerHTML = `<p class="text-xs text-slate-600 p-2 italic">Nenhuma comunidade ativa.</p>`;
                    return;
                }

                serverList.innerHTML = '';
                res.data.forEach(server => {
                    const btn = document.createElement('button');
                    btn.className = `w-full text-left text-sm px-3 py-2 rounded-xl transition-all font-medium ${activeServerId == server.id ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200'}`;
                    btn.innerHTML = `<div class="truncate">${escapeHtml(server.fullname)}</div>`;
                    
                    btn.onclick = () => selectServer(server.id, server.fullname, server.description);
                    serverList.appendChild(btn);
                });
            } catch (err) {}
        }

        function selectServer(id, name, desc) {
            activeServerId = id;
            activeChannelId = null;
            if (pollingInterval) clearInterval(pollingInterval); 
            
            activeSpaceTitle.textContent = name;
            activeSpaceDesc.textContent = desc || 'Comunidade farn ativa.';
            
            spaceActions.classList.remove('hidden');
            btnNewChannel.classList.remove('hidden');
            chatFooter.classList.add('hidden'); 
            
            loadServers();
            loadChannels(id);
            
            chatFeed.innerHTML = `
                <div class="flex-1 flex flex-col items-center justify-center text-slate-500 text-xs font-mono">
                    Sintonizado em [${escapeHtml(name)}]. Escolha um canal de texto na barra lateral.
                </div>`;
        }

        async function loadChannels(serverId) {
            try {
                const response = await fetch(`/api/servers/${serverId}/channels`);
                const res = await response.json();

                if (!res.success || res.data.length === 0) {
                    channelList.innerHTML = `<p class="text-xs text-slate-600 p-2 italic">Nenhum canal criado.</p>`;
                    return;
                }

                channelList.innerHTML = '';
                res.data.forEach(channel => {
                    const btn = document.createElement('button');
                    btn.className = `w-full text-left text-xs px-3 py-1.5 rounded-lg transition-colors flex items-center gap-2 ${activeChannelId == channel.id ? 'bg-slate-800 text-indigo-400 font-semibold' : 'text-slate-500 hover:bg-slate-800/40 hover:text-slate-300'}`;
                    btn.innerHTML = `<span class="text-slate-600 font-mono">#</span> <span class="truncate">${escapeHtml(channel.fullname)}</span>`;
                    
                    btn.onclick = () => selectChannel(channel.id, channel.fullname);
                    channelList.appendChild(btn);
                });
            } catch (err) {}
        }

        document.getElementById('btn-new-server').onclick = async () => {
            const name = prompt("Digite o nome da sua nova comunidade:");
            if (!name) return;
            const desc = prompt("Digite uma breve descrição (opcional):");

            try {
                const response = await fetch('/api/servers', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ fullname: name, description: desc })
                });
                const res = await response.json();
                if (res.success) loadServers();
            } catch (err) {}
        };

        btnNewChannel.onclick = async () => {
            if (!activeServerId) return;
            const name = prompt("Nome do novo canal:");
            if (!name) return;

            try {
                const response = await fetch(`/api/servers/${activeServerId}/channels`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ fullname: name })
                });
                const res = await response.json();
                if (res.success) loadChannels(activeServerId);
            } catch (err) {}
        };

        document.getElementById('btn-edit-server').onclick = async () => {
            if (!activeServerId) return;
            const newName = prompt("Novo nome para a comunidade:");
            if (!newName) return;
            const newDesc = prompt("Nova descrição:");

            try {
                const response = await fetch(`/api/servers/${activeServerId}/edit`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ fullname: newName, description: newDesc })
                });
                const res = await response.json();
                if (res.success) {
                    activeSpaceTitle.textContent = newName;
                    activeSpaceDesc.textContent = newDesc || 'Comunidade farn ativa.';
                    loadServers();
                }
            } catch (err) {}
        };

        document.getElementById('btn-delete-server').onclick = async () => {
            if (!activeServerId) return;
            if (!confirm("Tem certeza que deseja DESTRUIR permanentemente esta comunidade?")) return;

            try {
                const response = await fetch(`/api/servers/${activeServerId}/delete`, { method: 'POST' });
                const res = await response.json();
                if (res.success) {
                    activeServerId = null;
                    activeChannelId = null;
                    if (pollingInterval) clearInterval(pollingInterval);
                    spaceActions.classList.add('hidden');
                    btnNewChannel.classList.add('hidden');
                    chatFooter.classList.add('hidden');
                    activeSpaceTitle.textContent = "Mural Central";
                    activeSpaceDesc.textContent = "Selecione um canal para iniciar a transmissão.";
                    channelList.innerHTML = `<p class="text-xs text-slate-600 p-2 italic">Selecione uma comunidade para ver os canais.</p>`;
                    chatFeed.innerHTML = `<div class="flex-1 flex flex-col items-center justify-center text-slate-500 text-xs font-mono">:/ Comunidade Apagada</div>`;
                    loadServers();
                }
            } catch (err) {}
        };

        loadServers();
    });
    </script>
</body>
</html>