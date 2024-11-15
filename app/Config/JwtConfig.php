<?php

// app/Config/JwtConfig.php
namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class JwtConfig extends BaseConfig
{
    public string $issuer;
    public string $audience;
    public string $jwtSecret;
    public int $tokenExpiration = 7200; // Expiração padrão de 1 hora

    public function __construct()
    {
        parent::__construct();

        // Obtém valores das variáveis de ambiente definidas no Docker
        $this->issuer    = getenv('app.baseURL') ?: 'localhost';
        $this->audience  = getenv('app.baseURL') ?: 'localhost';
        $this->jwtSecret = getenv('encryption.key'); // Chave obtida das variáveis de ambiente

        // Verifica se a chave secreta foi carregada corretamente
        if (empty($this->jwtSecret)) {
            throw new \RuntimeException('JWT secret key is not set or invalid.');
        }
    }
}
