<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    public $default;

    public function __construct()
    {
        $this->default = [
            'scheme' => 'tcp',
            'host'   => getenv('REDIS_HOST'),
            'port'   => getenv('REDIS_PORT'),
            'password' => getenv('REDIS_PASSWORD') !== 'null' ? getenv('REDIS_PASSWORD') : null,
            'timeout' => getenv('REDIS_TIMEOUT'),
            'read_write_timeout' => getenv('REDIS_READ_WRITE_TIMEOUT'),
            'database' => getenv('REDIS_DATABASE'),
        ];
    }
}