<?php

namespace App\Controllers\Api\V1;

use App\Models\SupportModel;
use OpenApi\Attributes as OA;
use App\Models\Supports\V1\CreateSupports;
use CodeIgniter\HTTP\ResponseInterface;

class SupportController extends BaseController
{
    protected SupportModel $modelSupport;
    public function __construct()
    {
        parent::__construct();
        $this->modelSupport = new CreateSupports();
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        //
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */


    #[OA\Post(
        path: '/api/v1/support',
        description: 'Este endpoint cria um novo chamado de suporte para um cliente. O ID do cliente é identificado internamente.',
        summary: 'Criar novo chamado de suporte',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'name',
                        description: 'Nome do cliente',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'subject',
                        description: 'Assunto do chamado de suporte',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'type',
                        description: 'Tipo do suporte (e.g., técnico, financeiro)',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'message',
                        description: 'Mensagem detalhada do suporte',
                        type: 'string'
                    ),
                    new OA\Property(
                        property: 'channel',
                        description: 'Canal de origem do suporte (e.g., form, webhook)',
                        type: 'string',
                        default: 'form'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Suporte'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Chamado de suporte criado com sucesso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', description: 'ID do chamado criado', type: 'integer'),
                        new OA\Property(property: 'protocol', description: 'Protocolo do chamado', type: 'string')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflito: Chamado de suporte já existe'
            ),
            new OA\Response(
                response: 422,
                description: 'Erro de validação'
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno do servidor'
            )
        ]
    )]

    public function create(): ResponseInterface
    {
        $input = $this->request->getJSON(true);

        // Verifica se a entrada é válida antes de prosseguir
        if (!$input || empty($input['name']) || empty($input['subject']) || empty($input['type']) || empty($input['message'])) {
            return $this->fail('Os campos name, subject, type e message são obrigatórios.', 422);
        }

        try {
            $response = $this->modelSupport->createSupportSystem($input);
            return $this->respondCreated($response); // Usando 'respondCreated' para retornar 201
        } catch (\InvalidArgumentException $e) {
            // Captura exceções específicas de validação
            return $this->fail($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            // Captura exceções de runtime específicas
            return $this->fail($e->getMessage(), 500);
        } catch (\Exception $e) {
            // Captura exceções gerais não tratadas
            return $this->fail($e->getMessage(), 500);
        }
    }


    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {
        //
    }

    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        //
    }

    public function webhookCrispFirstChat(): ResponseInterface
    {
        $input = $this->request->getVar(TRUE);
        return $this->respond($input);
    }
}
