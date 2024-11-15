<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Defina os cabeçalhos CORS uma única vez aqui
        header('Access-Control-Allow-Origin: *'); // Substitua '*' por um domínio específico para produção
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');

        // Lidar com requisições OPTIONS
        if ($request->getMethod() === 'options') {
            // Responde imediatamente para requisições OPTIONS
            return service('response')
                ->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Garantir que não estamos duplicando o cabeçalho aqui
        if (!$response->hasHeader('Access-Control-Allow-Origin')) {
            $response->setHeader('Access-Control-Allow-Origin', '*'); // Ajuste conforme necessário
        }
    }
}
