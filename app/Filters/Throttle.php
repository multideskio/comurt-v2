<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Throttle implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        // Defina a quantidade de requisições permitidas e o intervalo de tempo baseado nos argumentos
        $maxRequests = $arguments[0] ?? 60;
        $timePeriod = $arguments[1] ?? MINUTE;

        // Converter '::1' (IPv6 localhost) para '127.0.0.1' (IPv4 localhost)
        $ipAddress = ($request->getIPAddress() === '::1') ? '127.0.0.1' : $request->getIPAddress();

        // Converter tempo para segundos
        switch ($timePeriod) {
            case 'second':
                $timePeriodInSeconds = 1;
                break;
            case 'minute':
                $timePeriodInSeconds = 60;
                break;
            case 'hour':
                $timePeriodInSeconds = 3600;
                break;
            case 'day':
                $timePeriodInSeconds = 86400;
                break;
            default:
                $timePeriodInSeconds = (int) $timePeriod;
                break;
        }

        // Limitar as requisições
        if ($throttler->check($ipAddress, $maxRequests, $timePeriodInSeconds) === false) {
            $response = [
                'status'  => 429,
                'error'   => 'too_many_requests',
                'message' => 'Você excedeu o limite de requisições permitido. Tente novamente mais tarde.',
            ];

            return Services::response()
                ->setStatusCode(429)
                ->setJSON($response); // Retorna o JSON com o código 429
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nenhuma ação necessária após a resposta
    }
}
