<?php

namespace App\Models;

use App\Config\JwtConfig;
use App\Models\Appointments\V1\SearchAppointments;
use CodeIgniter\Model;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['platformId', 'name', 'email', 'phone', 'password', 'photo', 'description', 'token', 'magic_link', 'checked', 'admin', 'education', 'languages', 'department', 'social_networks', 'company', 'birthdate', 'show_personal_chart_dashboard', 'show_family_chart_dashboard', 'show_friends_chart_dashboard', 'show_appointments_chart_dashboard', 'show_basic_info_dashboard', 'receive_updates_email', 'receive_updates_sms', 'receive_updates_whatsapp', 'receive_scheduling_reminders', 'receive_cancellation_reminders', 'default_lang'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['beforeData'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['beforeData'];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected $jwtConfig;

    public function __construct()
    {
        parent::__construct();
        $this->jwtConfig = new JwtConfig;
    }

    protected function beforeData(array $data): array
    {

        helper('auxiliar');
        if (array_key_exists("password", $data["data"])) {
            $data["data"]["password"]   = password_hash($data["data"]["password"], PASSWORD_BCRYPT);
        }

        if (array_key_exists("email", $data["data"])) {
            $data["data"]['magic_link'] = generateMagicLink($data["data"]["email"], 60 * 30);
        }

        $data["data"]['token']      = gera_token();

        return $data;
    }

    public function listUsers(array $params): array
    {
        //define array
        $response = [];

        //busca dados do usuário logado
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        //não está logado gera erro.
        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Unauthenticated user.');
        }

        //id do usuário logado
        $currentUserId = $currentUser['id'];

        // Parâmetros de entrada
        $searchTerm = $params['s'] ?? false;
        $currentPage = (isset($params['page']) && intval($params['page']) > 0) ? intval($params['page']) : 1;
        $sortBy = $params['sort_by'] ?? 'id';
        $sortOrder = strtoupper($params['order'] ?? 'ASC');
        $itemsPerPage = $this->validateItemsPerPage($params['limite'] ?? null);


        // Construir a query principal
        $this->groupBy('users.id')->orderBy('users.' . $sortBy, $sortOrder);

        // Aplicar filtro de busca se o termo for fornecido
        if ($searchTerm) {
            $this->groupStart()
                ->like('users.name', $searchTerm)
                ->orLike('users.id', $searchTerm)
                ->orLike('users.email', $searchTerm)
                ->orLike('users.phone', $searchTerm)
                ->groupEnd();
        }

        // Contar resultados totais para a paginação
        $totalItems = $this->countAllResults(false); // 'false' mantém a query para a paginação

        // Paginação dos resultados
        $users = $this->paginate($itemsPerPage, '', $currentPage);

        // Calcular links de navegação para paginação
        $totalPages = ceil($totalItems / $itemsPerPage);
        $prevPage = ($currentPage > 1) ? $currentPage - 1 : null;
        $nextPage = ($currentPage < $totalPages) ? $currentPage + 1 : null;

        $data = [];

        $modelSubscription = new SubscriptionsModel();
        $modelPlan = new PlansModel();

        foreach ($users as $user) {
            unset($user['password'], $user['token']);

            $subscription = $modelSubscription->where('idUser', $user['id'])->first();
            $plan = $subscription ? $modelPlan->where('id', $subscription['idPlan'])->first() : null;

            // Achatar a estrutura de retorno
            $data[] = [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email'],
                "phone" => $user['phone'],
                "platformId" => $user['platformId'],
                "admin" => $user['admin'],
                "created" => $user['created_at'],
                "update" => $user['updated_at'],
                "subscription_id" => $subscription['id'] ?? null,
                "subscription_create" => $subscription['created_at'] ?? null,
                "plan_name" => $plan['namePlan'] ?? null,
                "plan_id" => $plan['idPlan'] ?? null,
                "plan_permissionUser" => $plan['permissionUser'] ?? null,
                "plan_timeSubscription" => $plan['timeSubscription'] ?? null,
            ];
        }

        // Montar o array de dados a ser retornado
        $response = [
            'rows'  => $data, // Resultados paginados com contagem de anamneses
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage,
                'prev_page' => $prevPage,
                'next_page' => $nextPage,
            ],
            //'num'   => $resultMessage
        ];


        return $response;
    }

    public function login($email, $password)
    {
        try {
            // Verifica o login (e-mail e senha)
            log_message('info', 'Tentativa de login para o email: ' . $email);
            $user = $this->verifyLogin($email, $password);

            if (!$user) {
                throw new \RuntimeException('Invalid credentials');
            }

            session()->set('user_language', $user['default_lang']);

            if ($user['admin'] == 0) {
                // Verifica se o usuário possui uma inscrição ativa
                log_message('info', 'Verificando inscrição ativa para o usuário ID: ' . $user['id']);
                $subscription = $this->verifySubscription($user['id']);
                if (!$subscription) {
                    throw new \RuntimeException('User without active registration');
                }

                // Verifica se o usuário possui um plano ativo
                log_message('info', 'Verificando plano ativo para o plano ID: ' . $subscription['idPlan']);
                $plan = $this->verifyPlan($subscription['idPlan']);
                if (!$plan) {
                    throw new \RuntimeException('User plan not found');
                }

                // Determina permissão com base no plano
                $permission = $this->determinePermission($plan['permissionUser']);
                if (!$permission) {
                    throw new \RuntimeException('User without access permission');
                }
            } else {
                $permission = 'SUPERADMIN';
            }

            // Gera o payload do token JWT com base nos dados do plano
            $payload = $this->generateJwtPayload($user, $permission);

            // Gera o token JWT
            log_message('info', 'Gerando token JWT para o usuário ID: ' . $user['id']);
            $token = JWT::encode($payload, $this->jwtConfig->jwtSecret, 'HS256');

            // Loga o sucesso da autenticação
            log_message('info', 'Autenticação bem-sucedida para o usuário ID: ' . $user['id']);

            // Gerencia informações adicionais do cliente
            $this->manageCustomer($user);

            return $token;
        } catch (\Exception $e) {
            log_message('error', 'Erro durante o login: ' . $e->getMessage());
            throw $e; // Ou retorne um erro adequado
        }
    }

    // Métodos auxiliares para melhorar a clareza e modularização
    protected function determinePermission($permissionUser)
    {
        switch ($permissionUser) {
            case 1:
                log_message('info', 'PROFISSIONAL');
                return "PROFISSIONAL";
            case 2:
                log_message('info', 'TERAPEUTA_SI');
                return "TERAPEUTA_SI";
            case 3:
                log_message('info', 'SUPERADMIN');
                return "SUPERADMIN";
            default:
                return null;
        }
    }

    protected function generateJwtPayload($user, $permission)
    {
        return [
            'iss' => $this->jwtConfig->issuer,
            'aud' => $this->jwtConfig->audience,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + $this->jwtConfig->tokenExpiration,
            'role' => $permission,
            'data' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ];
    }


    /**
     * Verifica o login do usuário.
     *
     * @param string $email E-mail do usuário.
     * @param string $pass Senha do usuário.
     * @return array Dados do usuário.
     * @throws Exception Se o e-mail ou a senha forem inválidos.
     */
    private function verifyLogin($email, $pass)
    {
        $rowLogin = $this->where('email', $email)->first();
        if (!$rowLogin) {
            log_message('info', 'E-mail não encontrado');
            throw new Exception('E-mail não encontrado');
        }

        if (!password_verify($pass, $rowLogin['password'])) {
            log_message('info', 'Senha inválida');
            throw new Exception('Senha inválida');
        }

        return $rowLogin;
    }

    /**
     * Verifica se o usuário possui uma inscrição ativa.
     *
     * @param int $userId ID do usuário.
     * @throws Exception Se o usuário não tiver uma inscrição ativa.
     */
    private function verifySubscription($userId)
    {
        $modelSubscription = new SubscriptionsModel();
        $rowSubscription = $modelSubscription->where('idUser', $userId)->first();
        if (!$rowSubscription) {
            log_message('info', 'Você não tem uma inscrição ativa.');
            throw new Exception('You do not have an active subscription.');
        }
        log_message('info', 'Inscrição ativa : ' . json_encode($rowSubscription));
        return $rowSubscription;
    }

    /**
     * Verifica se o usuário possui um plano ativo.
     *
     * @param int $userId ID do usuário.
     * @return array Dados do plano.
     * @throws Exception Se o usuário não tiver um plano ativo.
     */
    private function verifyPlan($userId)
    {
        $modelPlan         = new PlansModel();
        $rowPlan           = $modelPlan->where('id', $userId)->first();
        if (!$rowPlan) {
            log_message('info', 'O plano não está ativo.');
            throw new Exception('The plan is not active.');
        }
        log_message('info', 'Inscrição ativa : ' . json_encode($rowPlan));
        return $rowPlan;
    }

    private function manageCustomer($rowLogin)
    {
        $modelCustomers = new CustomersModel();
        $rowCustomers = $modelCustomers->where(['idUser' => $rowLogin['id'], 'email' => $rowLogin['email']])->first();
        if ($rowCustomers) {
            return $rowCustomers['id'];
        } else {
            $dataCustomers = [
                'idUser' => $rowLogin['id'],
                'name'   => $rowLogin['name'],
                'email'  => $rowLogin['email'],
                'photo'  => $rowLogin['photo'],
                'phone'  => $rowLogin['phone'] ?? '',
            ];
            $idCustomers = $modelCustomers->insert($dataCustomers);
            $modelTime = new TimeLinesModel();
            $modelTime->insert(
                [
                    'idUser' => $rowLogin['id'],
                    'idCustomer' => $idCustomers,
                    'type' => 'create_customer'
                ]
            );
            return $idCustomers;
        }
    }

    // Retorna dados do usuário logado
    public function me()
    {
        try {
            $decoded = $this->decodeDataTokenUser();

            $userId  = $decoded->data->id;

            // Acessa a role diretamente de $decoded
            $role = $decoded->role ?? lang('Errors.roleNotSpecified');

            // Busca no banco de dados se não estiver no cache
            $user = $this->find($userId);
            if (!$user) {
                throw new \RuntimeException(lang('Errors.notFound'));
            }

            $serchApp = new SearchAppointments();

            $statistics = $serchApp->statistics($userId);

            return [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'role'  => $role,
                'lang'  => $user['default_lang'],
                'languages'  => $user['languages'],
                'description' => $user['description'],
                'education' => $user['education'],
                'department' => $user['department'],
                'social_networks' => $user['social_networks'],
                'company' => $user['company'],
                'birthdate' => $user['birthdate'],
                'show_personal_chart_dashboard' => $user['show_personal_chart_dashboard'],
                'show_family_chart_dashboard' => $user['show_family_chart_dashboard'],
                'show_friends_chart_dashboard' => $user['show_friends_chart_dashboard'],
                'show_appointments_chart_dashboard' => $user['show_appointments_chart_dashboard'],
                'show_basic_info_dashboard' => $user['show_basic_info_dashboard'],
                'receive_updates_email' => $user['receive_updates_email'],
                'receive_updates_sms' => $user['receive_updates_sms'],
                'receive_updates_whatsapp' => $user['receive_updates_whatsapp'],
                'receive_scheduling_reminders' => $user['receive_scheduling_reminders'],
                'receive_cancellation_reminders' => $user['receive_cancellation_reminders'],
                'statistics' => [
                    'appointments' => $statistics['appointments'],
                    'anamneses' => $statistics['anamneses'],
                    'cancelled' => $statistics['cancelled']
                ]
            ];
        } catch (\RuntimeException $e) {
            log_message('error', 'Erro ao obter dados do usuário: ' . $e->getMessage());
            throw new \RuntimeException($e->getMessage(), 400);
        } catch (\Exception $e) {
            log_message('error', 'Erro inesperado ao obter dados do usuário: ' . $e->getMessage());
            throw new \RuntimeException(lang('Errors.serverError'), 500);
        }
    }

    // Método separado para decodificação do JWT
    public function decodeToken($token)
    {
        try {
            // Decodifica o token JWT usando a chave secreta e valida a assinatura
            $decoded = JWT::decode($token, new Key($this->jwtConfig->jwtSecret, 'HS256'));
            return $decoded; // Retorna o objeto decodificado
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid or expired token: ' . $e->getMessage(), 401);
        }
    }

    private function validateItemsPerPage($value)
    {
        // Verifica se o valor está definido, se é numérico, e tenta converter para inteiro
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;

        // Se a conversão falhar, retorna 15
        if (!$itemsPerPage) {
            $itemsPerPage = 15;
        }

        // Verifica o limite máximo de 200
        if ($itemsPerPage > 200) {
            $itemsPerPage = 200;
        }

        return $itemsPerPage;
    }


    private function validateMagicLinkToken($token)
    {
        $rowLogin = $this->where('magic_link', $token)->first();

        if (!$rowLogin) {
            log_message('info', 'Token de link mágico não encontrado: ' . $token);
            throw new \RuntimeException('Invalid or non-existent magic link.', 404);
        }

        /*if (isset($rowLogin['magic_link_expiration']) && strtotime($rowLogin['magic_link_expiration']) < time()) {
            log_message('info', 'Token de link mágico expirado para o usuário ID: ' . $rowLogin['id']);
            throw new \RuntimeException('Magic link has expired.', 410);
        }*/

        log_message('info', 'Link mágico validado com sucesso para o usuário ID: ' . $rowLogin['id']);

        return $rowLogin;
    }

    public function loginWithMagicLink($token)
    {
        try {
            log_message('info', 'Tentativa de login com link mágico usando o token: ' . $token);

            $user = $this->validateMagicLinkToken($token);

            if (!$user) {
                throw new \RuntimeException('Invalid or expired magic link.', 404);
            }

            if ($user['admin'] == 0) {
                log_message('info', 'Verificando inscrição ativa para o usuário ID: ' . $user['id']);
                $subscription = $this->verifySubscription($user['id']);
                if (!$subscription) {
                    throw new \RuntimeException('User has no active subscription.', 403);
                }

                log_message('info', 'Verificando plano ativo para o plano ID: ' . $subscription['idPlan']);
                $plan = $this->verifyPlan($subscription['idPlan']);
                if (!$plan) {
                    throw new \RuntimeException('User plan not found.', 404);
                }

                $permission = $this->determinePermission($plan['permissionUser']);
                if (!$permission) {
                    throw new \RuntimeException('User does not have access permission.', 403);
                }
            } else {
                $permission = 'SUPERADMIN';
            }

            $payload = $this->generateJwtPayload($user, $permission);

            log_message('info', 'Gerando token JWT para o usuário ID: ' . $user['id']);
            $tokenJwt = JWT::encode($payload, $this->jwtConfig->jwtSecret, 'HS256');

            log_message('info', 'Autenticação bem-sucedida para o usuário ID: ' . $user['id']);

            $this->manageCustomer($user);

            return $tokenJwt;
        } catch (\Exception $e) {
            log_message('error', 'Erro durante o login com link mágico: ' . $e->getMessage());
            throw $e;
        }
    }


    protected function getAuthenticatedUser()
    {
        $currentUser = $this->decodeDataTokenUser()->data;
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        return $currentUser;
    }





    public function decodeDataTokenUser()
    {
        // Pega os dados de cabeçalho
        $request = service('request');
        $authHeader = $request->getServer('HTTP_AUTHORIZATION');

        // Obtém o token JWT do cabeçalho da requisição
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new \RuntimeException(lang('Errors.tokenInvalid'));
        }

        $token = $matches[1];

        // Decodifica o token JWT usando a chave secreta configurada
        try {
            // Decodifica o token JWT usando a chave secreta e valida a assinatura
            $decoded = JWT::decode($token, new Key($this->jwtConfig->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid or expired token: ' . $e->getMessage(), 401);
        }

        $userData = $decoded->data ?? null; // Extrai o campo 'data' que contém as informações do usuário

        if (!$userData || !isset($userData->id)) {
            throw new \RuntimeException(lang('Errors.userNotAuthenticated'));
        }

        return $decoded;
    }
}
