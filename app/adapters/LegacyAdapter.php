<?php

namespace App\Adapters;

class LegacyAdapter
{
    /**
     * Busca o histórico do sistema antigo e padroniza para o formato novo.
     */
    public static function getOldChannelHistory(string $channelId): array
    {
        try {
            // Simulando uma query legada feia: 
            // $stmt = $legacyDb->query("SELECT * FROM tb_velha_msg WHERE sala = :id");
            
            $oldData = [
                (object)[
                    'id_msg' => 991, 
                    'txt' => "Mensagem do sistema antigo", 
                    'data_envio' => "2024-01-10 10:00:00", 
                    'usr' => "Admin_velho"
                ]
            ];

            // O Mapeamento (Adapter)
            return array_map(function($msg) {
                return [
                    'id' => 'leg_' . $msg->id_msg,
                    'content' => $msg->txt,
                    'timestamp' => $msg->data_envio,
                    'author' => [
                        'username' => $msg->usr,
                        'isLegacy' => true
                    ]
                ];
            }, $oldData);

        } catch (\Exception $e) {
            error_log("Erro no LegacyAdapter: " . $e->getMessage());
            return []; // Retorna vazio para não derrubar o chat novo
        }
    }
}
