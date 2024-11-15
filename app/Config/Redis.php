<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    public $default = [
        'scheme' => 'tcp',            // Protocolo de conexão (tcp é o padrão)
        'host'   => '5.161.224.69',   // Endereço do Redis
        'port'   => 6390,             // Porta correta do Redis
        'password' => null,           // Senha do Redis, se necessário
        'timeout' => 5.0,             // Timeout ajustável para evitar timeout de conexão
        'read_write_timeout' => 0,    // Timeout de leitura/escrita
        'database' => 0,              // Banco de dados padrão do Redis
    ];
}