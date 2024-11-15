<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\JwtConfig;
use App\Models\BlacklistModel;

class JwtAuth implements FilterInterface
{
    protected $jwtConfig;
    protected $blacklistModel;

    public function __construct()
    {
        $this->jwtConfig = new JwtConfig();
        $this->blacklistModel = new BlacklistModel();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session(); // Inicia a sessão para armazenar dados
        $header = $request->getServer('HTTP_AUTHORIZATION');
        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->unauthorizedResponse(lang('Errors.tokenInvalid')); // Mensagem traduzida
        }

        $token = $matches[1];

        if ($this->isTokenBlacklisted($token)) {
            return $this->unauthorizedResponse(lang('Errors.tokenInvalid')); // Token na blacklist
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtConfig->jwtSecret, 'HS256'));

            $role = $decoded->role ?? null;
            if (!$role || !in_array($role, $arguments)) {
                return $this->forbiddenResponse(lang('Errors.forbidden')); // Acesso negado
            }

            $uid = $decoded->data->id ?? null;
            if (!$uid) {
                log_message('error', 'UID não encontrado no token decodificado.');
                return $this->unauthorizedResponse(lang('Errors.tokenInvalid')); // UID não encontrado
            }

            $currentTime = time();
            $timeRemaining = $decoded->exp - $currentTime;

            // Armazena o tempo restante do token original na sessão
            $session->set('token_time_remaining', $timeRemaining);

            // Renova o token se estiver próximo de expirar
            if ($timeRemaining < 600) {
                $newToken = $this->renewToken($decoded);
                $session->set('new_token', $newToken); // Armazena o novo token na sessão
            }
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->unauthorizedResponse(lang('Errors.tokenInvalid')); // Assinatura inválida
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->unauthorizedResponse(lang('Errors.tokenExpired')); // Token expirado
        } catch (\Exception $e) {
            return $this->unauthorizedResponse(lang('Errors.tokenInvalid') . ': ' . $e->getMessage()); // Token inválido
        }

        // Permite que o fluxo continue
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $session = session(); // Acessa a sessão

        // Verifica se o token original ainda tem tempo restante armazenado
        if ($session->has('token_time_remaining')) {
            $timeRemaining = $session->get('token_time_remaining');
            // Define o cabeçalho com o tempo restante do token original
            $response->setHeader('X-Token-Time-Remaining', $timeRemaining);
        }

        // Verifica se o token foi renovado na sessão
        if ($session->has('new_token')) {
            $newToken = $session->get('new_token');
            // Define o cabeçalho com o novo token renovado
            $response->setHeader('X-Renewed-Token', $newToken);

            // Limpa o token da sessão para evitar reuso desnecessário
            $session->remove('new_token');
        } else {
            // Indica que o token não foi renovado
            $response->setHeader('X-Renewed-Token', 'not-renewed');
        }

        return $response;
    }

    private function renewToken($decoded)
    {
        // Gera um novo payload com um novo tempo de expiração
        $newPayload = [
            'iss' => $this->jwtConfig->issuer, // Emissor do token
            'aud' => $this->jwtConfig->audience, // Destinatário do token
            'iat' => time(), // Tempo atual (issued at)
            'nbf' => time(), // O token é válido a partir deste momento (not before)
            'exp' => time() + $this->jwtConfig->tokenExpiration, // Novo tempo de expiração
            'role' => $decoded->role, // Mantém o papel/role do usuário
            'data' => [
                'id' => $decoded->data->id, // ID do usuário do token original
                'email' => $decoded->data->email, // Email do usuário do token original
                'name' => $decoded->data->name // Nome do usuário do token original
            ]
        ];

        // Retorna o novo token com o payload atualizado
        return JWT::encode($newPayload, $this->jwtConfig->jwtSecret, 'HS256');
    }

    private function unauthorizedResponse($message)
    {
        return \Config\Services::response()
            ->setStatusCode(401)
            ->setJSON(['message' => $message]);
    }

    private function forbiddenResponse($message)
    {
        return \Config\Services::response()
            ->setStatusCode(403)
            ->setJSON(['message' => $message]);
    }

    private function isTokenBlacklisted($token)
    {
        log_message('info', 'Verificando token.');

        // Verifica se o token está na blacklist
        $blacklistedToken = $this->blacklistModel->where('token', $token)->first();

        return $blacklistedToken !== null;
    }
}
