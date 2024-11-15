<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Cors extends BaseConfig
{
    public array $default = [
        'allowedOrigins' => ['*'], // Permitir todas as origens (somente para desenvolvimento)
        'allowedOriginsPatterns' => [],
        'supportsCredentials' => false,
        'allowedHeaders' => ['*'],
        'allowedMethods' => ['*'],
        'maxAge' => 3600,
    ];

    public array $api = [
        'allowedOrigins' => ['*'], // Domínios específicos para produção
        'allowedOriginsPatterns' => [],
        'supportsCredentials' => true, // Permite envio de cookies e headers de autenticação
        'allowedHeaders' => [
            'Content-Type',
            'Authorization',
            'X-Requested-With',
            'Accept',
            'Origin',
        ],
        'allowedMethods' => [
            'GET',
            'POST',
            'PATCH',
            'PUT',
            'DELETE',
            'OPTIONS',
        ],
        'maxAge' => 7200,
    ];
}
