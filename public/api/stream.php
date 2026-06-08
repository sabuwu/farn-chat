<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const feed = document.querySelector('.overflow-y-auto');
    const currentChannel = 'geral'; 
    const myUsername = 'Admin'; // Mock do seu usuário logado

    let lastMessageCount = 0; // Controle para não redesenhar a tela à toa

    // --- 1. Envio da Mensagem (POST Livre) ---
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const content = messageInput.value.trim();
        if (!content) return;

        messageInput.value = ''; // Limpa na hora para dar fluidez

        try {
            await fetch(`/api/channels/${currentChannel}/messages`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ content: content, username: myUsername })
            });
            
            // Assim que enviar, já pede pro servidor o histórico atualizado
            fetchHistory(); 
        } catch (err) {
            console.error('Erro ao enviar:', err);
        }
    });

    // --- 2. Busca do Histórico (Short Polling) ---
    async function fetchHistory() {
        try {
            const response = await fetch(`/api/channels/${currentChannel}/messages`);
            const json = await response.json();
            
            // Só atualiza a DOM se chegaram mensagens novas do banco
            if (json.data && json.data.length !== lastMessageCount) {
                lastMessageCount = json.data.length;
                feed.innerHTML = ''; // Limpa o feed antigo
                
                json.data.forEach(msg => {
                    const avatar = msg.author.username.substring(0, 2).toUpperCase();
                    
                    const msgElement = document.createElement('div');
                    msgElement.className = "flex gap-4 items-start group hover:bg-[#2E3035] -mx-4 px-4 py-1 rounded transition-colors";
                    msgElement.innerHTML = `
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex-shrink-0 flex items-center justify-center text-white font-bold mt-0.5">
                            ${avatar}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline gap-2">
                                <span class="font-medium text-[#F2F3F5] hover:underline cursor-pointer text-sm">${msg.author.username}</span>
                                <span class="text-[10px] text-[#949BA4]">${msg.timestamp}</span>
                            </div>
                            <p class="text-[#DBDEE1] text-sm leading-relaxed mt-1 break-words">${msg.content}</p>
                        </div>
                    `;
                    feed.appendChild(msgElement);
                });
                
                // Rola para a última mensagem automaticamente
                feed.scrollTop = feed.scrollHeight; 
            }
        } catch (err) {
            console.error('Falha no polling:', err);
        }
    }

    // Carrega a primeira vez e depois fica checando a cada 2 segundos
    fetchHistory();
    setInterval(fetchHistory, 2000);
});
</script>
