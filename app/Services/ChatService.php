<?php

declare(strict_types=1);

namespace App\Services;

use App\Adapters\LegacyAdapter;
use PDO;

class ChatService
{
    /**
     * Busca o histórico unificado (Legado + Novo) de um canal de forma paginada.
     * * @param string $channelId ID ou slug do canal atual.
     * @param int $page Página atual para controle do scroll infinito.
     * @param PDO $db Instância de conexão com o banco de dados novo (pode ser injetada ou instanciada via config).
     * @return array Array de mensagens padronizadas.
     */
    public static function getMessagesForChannel(string $channelId, int $page = 1, ?PDO $db = null): array
    {
        // Se não passarmos o PDO por argumento, podemos buscar do arquivo de configuração global
        $db ??= require dirname(__DIR__, 2) . '/config/database.php';
        
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // 1. Busca mensagens reais do banco novo (Ordenadas da mais recente para a mais antiga para paginação)
        $stmt = $db->prepare('
            SELECT id, content, timestamp, user_id as username 
            FROM messages 
            WHERE channel_id = :channel_id 
            ORDER BY timestamp DESC 
            LIMIT :limit OFFSET :offset
        ');
        
        // PDO::PARAM_INT é obrigatório aqui porque o Nginx/PHP sob modo estrito pode escapar o LIMIT como string e quebrar a sintaxe SQL
        $stmt->bindValue(':channel_id', $channelId, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $rawNewMessages = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Padroniza o array vindo do banco de dados novo para bater com o contrato da UI
        $newMessages = array_map(function(array $msg) {
            return [
                'id' => (string)$msg['id'],
                'content' => $msg['content'],
                'timestamp' => $msg['timestamp'],
                'author' => [
                    'username' => $msg['username'],
                    'isLegacy' => false
                ]
            ];
        }, $rawNewMessages);

        $legacyMessages = [];
        
        // 2. Condicional de Fallback: Se estamos na página 1 (topo do scroll recente) 
        // ou se o banco novo ainda tem poucas mensagens, buscamos o histórico profundo no _legacy
        if ($page === 1 || count($newMessages) < $limit) {
            $legacyMessages = LegacyAdapter::getOldChannelHistory($channelId);
        }

        // 3. Mescla o passado com o presente
        $allMessages = array_merge($legacyMessages, $newMessages);

        // 4. Ordenação Cronológica Estrita: Para renderizar no feed do chat, 
        // as mensagens antigas precisam vir em primeiro lugar (topo) e as novas abaixo.
        usort($allMessages, function(array $a, array $b) {
            return strtotime($a['timestamp']) <=> strtotime($b['timestamp']);
        });

        return $allMessages;
    }
}
