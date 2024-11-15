<?php

namespace App\Controllers\Api\V1;

use App\Models\Customers\V1\CreateCustomer;
use App\Models\Customers\V1\SearchCustomer;
use App\Models\CustomersModel;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;

class CustomerController extends BaseController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    protected CustomersModel $modelCustomer;

    public function __construct()
    {
        parent::__construct();
        $this->modelCustomer = new CustomersModel();
    }

    #[OA\Get(
        path: '/api/v1/customers',
        summary: 'Listar todos os clientes',
        description: 'Retorna uma lista de clientes com paginação',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                description: 'Campo para ordenação dos resultados',
                schema: new OA\Schema(type: 'string', enum: ['id', 'update'], default: 'id')
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                required: false,
                description: 'Ordem de ordenação (ASC ou DESC)',
                schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC'], default: 'ASC')
            ),
            new OA\Parameter(
                name: 's',
                in: 'query',
                required: false,
                description: 'Termo de busca para filtrar os clientes',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'limite',
                in: 'query',
                required: false,
                description: 'Número de itens por página',
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 200)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Número da página para paginação',
                schema: new OA\Schema(type: 'integer', default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de clientes',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'pagination', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    
    public function index()
    {
        //
        try {
            $input = $this->request->getVar();
            $cusSearch = new SearchCustomer();
            $data = $cusSearch->search($input);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */

     #[OA\Get(
        path: '/api/v1/customers/{id}',
        summary: 'Obter detalhes de um cliente',
        description: 'Retorna os detalhes de um cliente específico',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID do cliente',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes do cliente',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                        new OA\Property(property: 'photo', type: 'string', nullable: true, example: null),
                        new OA\Property(property: 'email', type: 'string', example: 'johndoe@example.com'),
                        new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                        new OA\Property(property: 'doc', type: 'string', example: '123456789'),
                        new OA\Property(property: 'generous', type: 'string', example: 'M'),
                        new OA\Property(property: 'birthDate', type: 'string', format: 'date', example: '1990-01-01'),
                        new OA\Property(property: 'anamneses_count', type: 'integer', example: 0),
                        new OA\Property(property: 'anamneses', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'timelines', type: 'array', items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Cliente não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    
    public function show($id = null)
    {
        try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors('O ID do cliente é obrigatório.');
            }

            // Chama o método showCustomer do model para obter os dados do customer com anamneses
            $customer = $this->modelCustomer->showCustomer((int) $id);

            // Retorna a resposta de sucesso com os dados do customer
            return $this->respond($customer);
        } catch (\InvalidArgumentException $e) {
            // Responde com erro de validação (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Responde com erro de execução (404 Not Found ou 403 Forbidden)
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Responde com erro interno (500 Internal Server Error)
            return $this->failServerError('Erro interno do servidor: ' . $e->getMessage());
        }
    }


    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
        return $this->respond(['new' => '']);
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    #[OA\Post(
        path: '/api/v1/customers',
        summary: 'Criar um novo cliente',
        description: 'Cria um novo cliente com os dados fornecidos',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'photo', type: 'string', nullable: true),
                    new OA\Property(property: 'birthDate', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'doc', type: 'string', nullable: true),
                    new OA\Property(property: 'generous', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente criado com sucesso'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 403, description: 'Usuário sem permissão'),
            new OA\Response(response: 422, description: 'Erro de validação'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function create()
    {
        try {
            // Obtém os dados de entrada usando getVar para capturar o JSON do corpo da requisição
            $input = $this->request->getJSON(true);

            // Validação básica de campos obrigatórios
            if (empty($input['name']) || empty($input['email'])) {
                return $this->failValidationErrors('The name and email fields are required.');
            }

            // Converte o objeto de entrada para array para passar para o model
            $inputArray = [
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'] ?? null,
                'photo' => $input['photo'] ?? null,
                'date' => $input['birthDate'] ?? null,  // Adicionando campos opcionais
                'doc' => $input['doc'] ?? null,
                'genero' => $input['generous'] ?? null
            ];

            $cusCreate = new CreateCustomer();
            // Chama o método create do model com os dados de entrada
            $create = $cusCreate->createCustomer($inputArray);

            // Retorna a resposta de sucesso com o status 201 Created
            return $this->respondCreated($create);
        } catch (\DomainException $e) {
            // Conflict error, e.g., duplicate appointment
            return $this->failResourceExists($e->getMessage()); // 409 Conflict
        } catch (\InvalidArgumentException $e) {
            // Responde com erro de validação (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Responde com erro de execução (400 Bad Request)
            return $this->fail($e->getMessage(), 400);
        } catch (\Exception $e) {
            // Responde com erro interno (500 Internal Server Error)
            return $this->failServerError('Internal Server Error: ' . $e->getMessage());
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
        return $this->respond(['edit' => $id]);
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    #[OA\Put(
        path: '/api/v1/customers/{id}',
        summary: 'Atualizar um cliente',
        description: 'Atualiza os dados de um cliente existente',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID do cliente',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string'),
                    new OA\Property(property: 'photo', type: 'string', nullable: true),
                    new OA\Property(property: 'birthDate', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'doc', type: 'string', nullable: true),
                    new OA\Property(property: 'generous', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Cliente atualizado com sucesso'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Cliente não encontrado'),
            new OA\Response(response: 422, description: 'Erro de validação'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function update($id = null)
    {
        try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors('O ID do cliente é obrigatório.');
            }

            // Obtém os dados da requisição (assume-se que os dados estão em JSON)
            $input = $this->request->getVar();

            // Converte o input para um array, se necessário
            if (is_object($input)) {
                $input = (array) $input;
            }

            // Chama o método de atualização do model
            $update = $this->modelCustomer->updateCustomer($input, $id);

            // Retorna a resposta de sucesso com o status 200 OK
            return $this->respond($update);
        } catch (\InvalidArgumentException $e) {
            // Responde com erro de validação (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Responde com erro de execução (400 Bad Request)
            return $this->fail($e->getMessage(), 400);
        } catch (\Exception $e) {
            // Responde com erro interno (500 Internal Server Error)
            return $this->failServerError('Erro interno do servidor: ' . $e->getMessage());
        }
    }


    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    #[OA\Delete(
        path: '/api/v1/customers/{id}',
        summary: 'Excluir um cliente',
        description: 'Exclui um cliente existente',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID do cliente',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cliente deletado com sucesso'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Cliente não encontrado'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function delete($id = null)
    {
        try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors('O ID do cliente é obrigatório.');
            }
            // Chama o método deleteCustomer do model
            $this->modelCustomer->deleteCustomer((int) $id);
            // Retorna a resposta de sucesso com o status 200 OK
            return $this->respondDeleted(['message' => 'Customer deletado com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            // Responde com erro de validação (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Responde com erro de execução (404 Not Found ou 403 Forbidden)
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Responde com erro interno (500 Internal Server Error)
            return $this->failServerError('Erro interno do servidor: ' . $e->getMessage());
        }
    }


    #[OA\Schema(
        schema: 'Customer',
        type: 'object',
        properties: [
            new OA\Property(property: 'id', type: 'integer', description: 'ID do cliente', example: 1),
            new OA\Property(property: 'name', type: 'string', description: 'Nome do cliente', example: 'John Doe'),
            new OA\Property(property: 'photo', type: 'string', description: 'URL da foto do cliente', nullable: true, example: null),
            new OA\Property(property: 'email', type: 'string', description: 'E-mail do cliente', example: 'johndoe@example.com'),
            new OA\Property(property: 'phone', type: 'string', description: 'Telefone do cliente', example: '+1234567890'),
            new OA\Property(property: 'doc', type: 'string', description: 'Documento de identificação', example: '123456789'),
            new OA\Property(property: 'generous', type: 'string', description: 'Gênero do cliente', example: 'M'),
            new OA\Property(property: 'birthDate', type: 'string', format: 'date', description: 'Data de nascimento', example: '1990-01-01'),
            new OA\Property(property: 'anamneses_count', type: 'integer', description: 'Número de anamneses associadas', example: 0),
            new OA\Property(property: 'anamneses', type: 'array', items: new OA\Items(type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer', description: 'ID da anamnese'),
                new OA\Property(property: 'description', type: 'string', description: 'Descrição da anamnese')
            ])),
            new OA\Property(property: 'timelines', type: 'array', items: new OA\Items(type: 'object', properties: [
                new OA\Property(property: 'id', type: 'integer', description: 'ID da timeline'),
                new OA\Property(property: 'event', type: 'string', description: 'Descrição do evento da timeline')
            ]))
        ]
    )]
    
    public function shema(){

    }
}
