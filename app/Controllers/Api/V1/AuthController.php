<?php

namespace App\Controllers\Api\V1;

use App\Models\UsersModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Config\JwtConfig;
use App\Models\BlacklistModel;
use CodeIgniter\API\ResponseTrait;
use OpenApi\Attributes as OA;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Users\V1\RecoverUsers;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected UsersModel $userModel;
    protected JwtConfig $jwtConfig;

    public function __construct()
    {
        parent::__construct();
        $this->jwtConfig = new JwtConfig();
        $this->userModel = new UsersModel();
    }


    #[OA\Post(
        path: "/api/v1/login",
        summary: "Login",
        description: "Retorna um token JWT",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "usuario@exemplo.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "123456")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token JWT gerado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR...")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Credenciais inválidas"),
            new OA\Response(response: 400, description: "Dados de entrada inválidos")
        ]
    )]
    public function login()
    {
        try {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ];

            if (!$this->validate($rules)) {
                // Utiliza o método failValidationErrors() do ResponseTrait para retornar erros de validação
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $email     = $this->request->getVar('email');
            $password  = $this->request->getVar('password');

            // Valida os dados de entrada
            if (empty($email) || empty($password)) {
                log_message('warning', 'Tentativa de login sem email ou senha.');
                return $this->fail('Email and password are required', 400);
            }

            try {
                $token = $this->userModel->login($email, $password);
                // Retorna o token como resposta
                $elapsedTime = microtime(true) - APP_START;
                return $this->respond(['token' => $token, /*'load' => number_format($elapsedTime, 4) . ' seconds'*/]);
            } catch (\Exception $e) {
                // Loga o erro de autenticação
                log_message('error', 'Erro na autenticação: ' . $e->getMessage());
                return $this->failUnauthorized($e->getMessage());
            }
        } catch (\Exception $e) {

            return $this->fail($e->getMessage());
        }
    }


    #[OA\Post(
        path: "/api/v1/magiclink",
        summary: "Autenticação com Link Mágico",
        description: "Autentica o usuário utilizando um token de link mágico e retorna um token JWT.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["magiclink"],
                properties: [
                    new OA\Property(property: "magiclink", type: "string", example: "token_de_link_magico_gerado")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Token JWT gerado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJIUzI1NiIsInR...")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Token de link mágico não fornecido ou inválido"),
            new OA\Response(response: 500, description: "Erro inesperado no servidor")
        ]
    )]
    public function magiclink()
    {
        $input = $this->request->getJSON(TRUE);

        if (!isset($input['magiclink']) || empty($input['magiclink'])) {
            return $this->fail('Magic link token not provided', 400);
        }

        $token = $input['magiclink'];

        try {
            $token = $this->userModel->loginWithMagicLink($token);

            return $this->respond(
                [
                    'token' => $token,
                ],
                200
            );
        } catch (\InvalidArgumentException $e) {
            log_message('error', 'Erro no login com link mágico: ' . $e->getMessage());
            return $this->fail($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            log_message('error', 'Erro no login com link mágico: ' . $e->getMessage());
            return $this->fail($e->getMessage(), $e->getCode() ?? 500);
        } catch (\Exception $e) {
            log_message('error', 'Erro inesperado no login com link mágico: ' . $e->getMessage());
            return $this->fail('Unexpected server error.', 500);
        }
    }



    #[OA\Get(
        path: "/api/v1/logout",
        summary: "Logout do usuário",
        description: "Realiza logout e invalida o token JWT",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Logout realizado com sucesso"),
            new OA\Response(response: 401, description: "Token inválido ou ausente")
        ]
    )]

    // Método para realizar logout e adicionar o token à blacklist
    public function logout()
    {
        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION');

        if (!$authHeader) {
            return $this->fail(lang('Errors.authorizationHeaderNotFound'), 401);
        }

        try {
            $token = explode(' ', $authHeader)[1];
            $decoded = JWT::decode($token, new Key($this->jwtConfig->jwtSecret, 'HS256'));

            // Adiciona o token à blacklist
            $blacklistModel = new BlacklistModel();
            $blacklistModel->insert([
                'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', $decoded->exp),
            ]);

            return $this->respond(['message' => lang('Errors.logoutSuccessful')]);
        } catch (\Exception $e) {
            return $this->fail(lang('Errors.invalidToken') . ': ' . $e->getMessage(), 401);
        }
    }



    public function aviso()
    {
        return $this->fail(['message' => 'Not found!'], 404);
    }


    #[OA\Post(
        path: "/api/v1/recover",
        summary: "Recuperação de senha",
        description: "Permite que um usuário solicite a recuperação de senha através do seu email.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "user@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Recuperação de senha bem-sucedida",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Recovery successfully completed"),
                        new OA\Property(property: "data", type: "object", example: "{\"recovery_token\": \"xyz123abc\"}")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Email não fornecido ou inválido",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Email is required")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Usuário não encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "User not found")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Erro inesperado no servidor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Internal server error")
                    ]
                )
            )
        ]
    )]


    //recuperação de senha
    public function recover()
    {
        // Regras de validação
        $rules = [
            'email' => 'required|valid_email',
        ];

        // Verifica se as regras de validação falharam
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Obtém os parâmetros do JSON da requisição
        $params = $this->request->getJSON(true);
        $email = $params['email'] ?? null;

        // Verifica se o email está vazio
        if (empty($email)) {
            log_message('warning', 'Tentativa de recuperação sem email.');
            return $this->fail('Email is required', 400);
        }

        try {
            // Tenta recuperar o usuário com base no email
            $recoverModel = new RecoverUsers();
            $result = $recoverModel->recover($email);

            // Retorna uma resposta de sucesso
            return $this->respond(['message' => 'Recovery successfully completed', 'data' => $result], 200);
        } catch (PageNotFoundException $e) {
            // Se o usuário não for encontrado, retorna um erro 404
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Qualquer outra exceção retornará um erro genérico com código 500
            return $this->fail($e->getMessage(), 500);
        }
    }


    #[OA\Put(
        path: "/api/v1/recover",
        summary: "Atualização de senha",
        description: "Permite que o usuário atualize sua senha usando um token de recuperação.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token", "password"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "token_de_recuperacao_gerado"),
                    new OA\Property(property: "password", type: "string", example: "NovaSenhaForte123!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Senha atualizada com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Password updated successfully")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Token ou senha não fornecidos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Token and password required")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Erro inesperado no servidor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Internal server error")
                    ]
                )
            )
        ]
    )]


    public function newPass()
    {
        // Obtém os dados da requisição em formato JSON
        $input = $this->request->getJSON(true);

        // Pega o token e a senha do input
        $token    = $input['token'] ?? null;
        $password = $input['password'] ?? null;

        // Define as regras de validação
        $rules = [
            'password' => 'required|min_length[6]',
        ];

        // Verifica se o token ou a senha estão vazios
        if (empty($token) || empty($password)) {
            return $this->fail('Token and password required', 400);
        }

        // Valida a senha
        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors(), 400);
        }

        try {
            $recoverModel = new RecoverUsers();
            $data = $recoverModel->updatePass($token, $password);
            return $this->respondUpdated(['message' => $data]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
}
