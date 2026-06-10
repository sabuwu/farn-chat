<script>
document.addEventListener('DOMContentLoaded', () => {
    // Estado da Aplicação em Memória
    let activeServerId = null;
    let activeChannelId = null;
    let lastMessageCount = 0; // Evita que a tela pisque se não houver mensagens novas
    let pollingInterval = null; // Guarda o ponteiro do timer do chat

    // Elementos de Controle da DOM
    const serverList = document.getElementById('server-list');
    const channelList = document.getElementById('channel-list');
    const chatFeed = document.getElementById('chat-feed');
    const chatFooter = document.getElementById('chat-footer');
    const messageInput = document.getElementById('message-input');
    const activeSpaceTitle = document.getElementById('active-space-title');
    const activeSpaceDesc = document.getElementById('active-space-desc');
    const spaceActions = document.getElementById('space-actions');
    const btnNewChannel = document.getElementById('btn-new-channel');
    
    // Novo mapeamento: Botão físico se você usou o ID sugerido
    const btnSendMessage = document.getElementById('btn-send-message') || document.querySelector('#chat-footer button');

    const myUsername = 'sabu'; // Seu nick OpSec fixado para os testes
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');

    // --- 1. FUNÇÃO: BUSCAR HISTÓRICO DE MENSAGENS (READ) ---
    async function fetchChatMessages() {
        if (!activeChannelId) return;

        try {
            const response = await fetch(`/api/channels/${activeChannelId}/messages`);
            const res = await response.json();
            
            const messages = res.data || res; 

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
        } catch (err) {
            console.error('Falha na sincronização do chat:', err);
        }
    }

    // --- 2. GATILHO CENTRALIZADO: ENVIAR MENSAGEM (CREATE) ---
    async function sendMessage() {
        const content = messageInput.value.trim();
        if (!content || !activeChannelId) return;

        messageInput.value = ''; // Limpa o input imediatamente

        try {
            await fetch(`/api/channels/${activeChannelId}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: content, username: myUsername })
            });
            
            fetchChatMessages();
        } catch (err) {
            console.error('Erro ao transmitir dados:', err);
        }
    }

    // Vincula o clique do botão diretamente à função assíncrona
    if (btnSendMessage) {
        btnSendMessage.onclick = (e) => {
            e.preventDefault();
            sendMessage();
        };
    }

    // Vincula a tecla Enter diretamente no campo de texto
    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault(); // Impede o F5 nativo do navegador ou quebras de linha
            sendMessage();
        }
    });

    // --- 3. FUNÇÃO: SELECIONAR CANAL ---
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

    // --- 4. FUNÇÃO: CARREGAR COMUNIDADES (READ) ---
    async function loadServers() {
        try {
            const response = await fetch('/api/servers');
            const res = await response.json();
            
            if (!res.success || res.data.length === 0) {
                serverList.innerHTML = `<p class="text-xs text-slate-600 p-2 italic">Nenhuma comunidade activa.</p>`;
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
        } catch (err) {
            console.error("Erro ao ler comunidades:", err);
        }
    }

    // --- FUNÇÃO: SELECIONAR COMUNIDADE ---
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

    // --- FUNÇÃO: CARREGAR CANAIS (READ) ---
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
        } catch (err) {
            console.error("Erro ao ler canais:", err);
        }
    }

    // --- VINCULAÇÃO: CRIAR COMUNIDADE ---
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
            if (res.success) {
                loadServers();
            }
        } catch (err) {
            console.error(err);
        }
    };

    // --- VINCULAÇÃO: CRIAR CANAL ---
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
            if (res.success) {
                loadChannels(activeServerId);
            }
        } catch (err) {
            console.error(err);
        }
    };

    // --- VINCULAÇÃO: EDITAR COMUNIDADE ---
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
        } catch (err) {
            console.error(err);
        }
    };

    // --- VINCULAÇÃO: DELETAR COMUNIDADE ---
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
        } catch (err) {
            console.error(err);
        }
    };

    // Carga primária inicial das comunidades
    loadServers();
});
</script>