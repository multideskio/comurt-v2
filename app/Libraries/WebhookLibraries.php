<?php

namespace App\Libraries;

use App\Models\LogsModel;
use App\Models\PlansModel;
use App\Models\PlatformModel;
use App\Models\SubscriptionsModel;
use App\Models\UsersModel;
use Exception;
use InvalidArgumentException;

class WebhookLibraries
{
    protected $modelUser;
    protected $modelPlan;
    protected $modelSubscriptions;
    protected $modelPlatform;
    protected $dataPlatform;

    public function __construct()
    {
        helper('auxiliar');

        $this->modelUser = new UsersModel();
        $this->modelPlan = new PlansModel();

        $this->modelPlatform = new PlatformModel();
        $this->dataPlatform = $this->modelPlatform->first();

        $this->modelSubscriptions = new SubscriptionsModel();

        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::__construct] WebhookLibraries initialized successfully.');
    }

    /**
     * Processa a transação baseada no status atual.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Requisição contendo os dados do webhook.
     * @return array Resultado da execução.
     * @throws InvalidArgumentException Se o status for desconhecido ou dados inválidos.
     */
    public function processTransaction($request): array
    {
        $request = service('request');
        $currentStatus = $request->getJsonVar('currentStatus');

        log_message('info', '[LINE ' . __LINE__ . "] [WebhookLibraries::processTransaction] Processing transaction with status: {$currentStatus}");

        try {
            // Validação básica do status
            if (empty($currentStatus)) {
                throw new InvalidArgumentException("[LINE " . __LINE__ . "] [WebhookLibraries::processTransaction] Missing or invalid status");
            }

            $user = $this->getUserData($request);

            switch ($currentStatus) {
                case 'paid':
                    //$user = $this->getUserData($request);
                    return $this->processPaidTransaction($request, $user);
                case 'refunded':
                    //$user = $this->getUserData($request);
                    return $this->processRefundedTransaction($request, $user);
                case 'chargedback':
                    //$user = $this->getUserData($request);
                    return $this->processChargebackTransaction($request, $user);
                default:
                    throw new InvalidArgumentException("[LINE " . __LINE__ . "] [WebhookLibraries::processTransaction] Unknown status: {$currentStatus}");
            }
        } catch (InvalidArgumentException $e) {
            // Captura exceções específicas relacionadas a argumentos inválidos
            log_message('error', '[LINE ' . __LINE__ . '] [WebhookLibraries::processTransaction] Invalid argument: ' . $e->getMessage());
            return ['error' => 'Invalid status provided', 'message' => $e->getMessage()];
        } catch (Exception $e) {
            // Captura qualquer outro erro
            log_message('error', '[LINE ' . __LINE__ . '] [WebhookLibraries::processTransaction] ' . $e->getMessage());
            return ['error' => 'An unexpected error occurred', 'message' => $e->getMessage()];
        }
    }

    /**
     * Processa a transação paga.
     * @param $request
     * @param array $user
     * @return array
     * @throws InvalidArgumentException Se o ID do produto ou plano não forem encontrados.
     */
    protected function processPaidTransaction($request, array $user): array
    {
        $configDataEmail = [
            'name' => $user['name'],
            'email' => $user['email'],
            'baseUrl' => $this->dataPlatform['urlBase'],
            'token' => $user['token'],
            'magicLink' => $user['magic_link'],
            'company' =>  $this->dataPlatform['company'],
        ];

        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Processing paid transaction.');

        $modelLogs = new LogsModel();
        $modelPlan = new PlansModel();
        // Validação básica de dados de produto
        $idProduto = $request->getJsonVar('product.id');
        if (empty($idProduto)) {
            throw new InvalidArgumentException('[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Invalid or missing product ID');
        }
        $rowPlan = $modelPlan->where('idPlan', $idProduto)->first();
        if (empty($rowPlan)) {
            log_message('error', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] O plano não foi encontrado');
            throw new InvalidArgumentException('[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction]  Plan not found for paid transaction.');
        }
        // Busca todas as inscrições do usuário
        $searchUpdate = $this->modelSubscriptions->where(['idUser' => $user['id']])->findAll();
        $email = new EmailsLibraries();
        // Verifica se o usuário tem algum plano e deleta
        if (count($searchUpdate)) {
            //deletando inscrições
            foreach ($searchUpdate as $row) {
                $this->modelSubscriptions->delete($row['id']);
            }
            //cria uma nova inscrição
            $this->modelSubscriptions->insert([
                'idPlan' => $rowPlan['id'],
                'idUser' => $user['id']
            ]);
            //dados para inserir na tabela de logs
            $modelLogs->insert([
                'platformId' => 1,
                'idUser' => $user['id'],
                'type' => 'subscription_updated',
                'description' => 'Inscrição atualizada'
            ]);
            
            //envia email de atualização de conta
            $email->send($user['email'], 'Sua conta foi atualizada', view('emails/update-subscription', $configDataEmail));
            /*log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Email sent to user: ' . $user['email']);
            log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Subscription updated for user: ' . $user['id']);*/
            $resp = ['message' => 'Inscrição atualizada', 'code' => 201];
        } else {
            $this->modelSubscriptions->insert([
                'idPlan' => $rowPlan['id'],
                'idUser' => $user['id']
            ]);
            $modelLogs->insert([
                'platformId' => 1,
                'idUser' => $user['id'],
                'type' => 'subscription_created',
                'description' => 'Inscrição criada'
            ]);
            $resp = ['message' => 'Inscrição criada', 'code' => 201];

            $email->send($user['email'], 'Seu acesso chegou', view('emails/subscription', $configDataEmail));

            /*log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Email sent to user: ' . $user['email']);
            log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processPaidTransaction] Subscription created for user: ' . $user['id']);*/
        }

        return $resp;
    }

    /**
     * Processa a transação reembolsada.
     * @param $request
     * @param array $user
     * @return array
     */
    protected function processRefundedTransaction($request, array $user): array
    {
        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processRefundedTransaction] Processing refunded transaction for user: ' . $user['id']);

        $searchUpdate = $this->modelSubscriptions->select('id')->where(['idUser' => $user['id']])->findAll();
        foreach ($searchUpdate as $row) {
            $this->modelSubscriptions->delete($row['id']);
        }

        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processRefundedTransaction] Subscription cancelled for refunded transaction.');
        return ['status' => 'Inscrição cancelada', 'code' => 200];
    }

    /**
     * Processa transações com extorno (chargeback).
     * @param $request
     * @param array $user
     * @return array
     */
    protected function processChargebackTransaction($request, array $user): array
    {
        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processChargebackTransaction] Processing chargeback transaction for user: ' . $user['id']);

        $searchUpdate = $this->modelSubscriptions->select('id')->where(['idUser' => $user['id']])->findAll();
        foreach ($searchUpdate as $row) {
            $this->modelSubscriptions->delete($row['id']);
        }

        log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::processChargebackTransaction] Subscription cancelled due to chargeback.');
        return ['status' => 'Inscrição cancelada por extorno', 'code' => 200];
    }

    /**
     * Obtém os dados do usuário baseado no request.
     * @param $request
     * @return array
     * @throws Exception Se houver erro na criação de um novo usuário.
     */
    protected function getUserData($request): array
    {
        $request = service('request');
        $email = $request->getJsonVar('client.email');

        log_message('info', '[LINE ' . __LINE__ . "] [WebhookLibraries::getUserData] Fetching user data for email: {$email}");

        $rowUser = $this->modelUser->where('email', $email)->first();

        if ($rowUser) {
            log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::getUserData] User found: ' . $rowUser['id']);
            return $rowUser;
        } else {
            // Se o usuário não for encontrado, cria um novo
            $data = [
                'platformId' => 1,
                'name'     => $request->getJsonVar('client.name'),
                'email'    => $email,
                'password' => 'mudar@123',
                'token'    => gera_token()
            ];

            $this->modelUser->insert($data);
            $newUser = $this->modelUser->where('email', $email)->first();

            if ($newUser) {
                $modelLogs = new LogsModel();
                $modelLogs->insert([
                    'platformId' => 1,
                    'idUser' => $newUser['id'],
                    'type' => 'user_created',
                    'description' => 'Criou uma conta através de uma assinatura.'
                ]);

                log_message('info', '[LINE ' . __LINE__ . '] [WebhookLibraries::getUserData] New user created: ' . $newUser['id']);
                return $newUser;
            } else {
                log_message('error', '[LINE ' . __LINE__ . '] [WebhookLibraries::getUserData] Error creating new user for email: ' . $email);
                throw new Exception('[LINE ' . __LINE__ . '] [WebhookLibraries::getUserData] Erro ao criar novo usuário');
            }
        }
    }
}
