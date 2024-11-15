<?php

namespace App\Controllers\Api\V1;

use App\Libraries\WebhookLibraries;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use OpenApi\Attributes as OA;

class WebhookController extends BaseController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    protected WebhookLibraries $webhookLibraries;

    public function __construct()
    {
        parent::__construct();
        $this->webhookLibraries = new WebhookLibraries();
    }

    #[OA\Post(
        path: '/api/v1/webhook/greem',
        description: "Processa o webhook da GREEM para criar ou atualizar um usuário com base nas informações da transação recebida.\n</br>O produto deverá ter sido cadastrado previamente informando o ID do produto da GREEM",
        summary: 'Cria um usuário com base nas informações do webhook recebido da GREEM',
        requestBody: new OA\RequestBody(
            description: 'Dados do webhook da GREEM.',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'currentStatus',
                        description: 'Status atual da transação',
                        type: 'string',
                        example: 'paid'
                    ),
                    new OA\Property(
                        property: 'client',
                        description: 'Informações do cliente',
                        properties: [
                            new OA\Property(property: 'email', description: 'Email do cliente', type: 'string'),
                            new OA\Property(property: 'name', description: 'Nome do cliente', type: 'string'),
                            new OA\Property(property: 'cellphone', description: 'Telefone do cliente', type: 'string')
                        ],
                        type: 'object'
                    ),
                    new OA\Property(
                        property: 'product',
                        description: 'Informações do produto',
                        properties: [
                            new OA\Property(property: 'id', description: 'ID do produto', type: 'integer'),
                            new OA\Property(property: 'name', description: 'Nome do produto', type: 'integer')
                        ],
                        type: 'object'
                    )
                ],
                type: 'object'
            )
        ),

        tags: ['Webhooks'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Transação processada com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', description: 'Mensagem de sucesso', type: 'string')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Requisição inválida'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 403, description: 'Sem permissão para executar'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function greem(): ResponseInterface
    {
        try {
            // Ações da class WebhookLibraries
            $webhook = $this->webhookLibraries->processTransaction($this->request);
            return $this->respond($webhook, $webhook['code']);
        } catch (Exception $e) {
            return $this->failForbidden($e->getMessage());
        }
    }
}
