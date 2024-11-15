<?php

namespace App\Controllers\Api\V1;

use Exception;
use InvalidArgumentException;
use OpenApi\Attributes as OA;

use App\Models\Tasks\V1\CreateTasks;
use App\Models\Tasks\V1\DeleteTasks;
use App\Models\Tasks\V1\GetTasks;
use App\Models\Tasks\V1\SearchTasks;
use App\Models\Tasks\V1\UpdateTasks;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class TasksController extends BaseController
{
    protected CreateTasks $createTasks;
    protected DeleteTasks $deleteTasks;
    protected GetTasks $getTasks;
    protected SearchTasks $searchTasks;
    protected UpdateTasks $updateTasks;

    public function __construct()
    {
        parent::__construct();
        $this->createTasks = new CreateTasks();
        $this->deleteTasks = new DeleteTasks();
        $this->getTasks = new GetTasks();
        $this->searchTasks = new SearchTasks();
        $this->updateTasks = new UpdateTasks();
    }


    #[OA\Get(
        path: '/api/v1/tasks',
        description: 'Retorna uma lista de tarefas com paginação',
        summary: 'Listar todas as tarefas',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                description: 'Campo para ordenação dos resultados',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'id', enum: ['id', 'order', 'title'])
            ),
            new OA\Parameter(
                name: 'order',
                description: 'Ordem de ordenação (ASC ou DESC)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'ASC', enum: ['ASC', 'DESC'])
            ),
            new OA\Parameter(
                name: 's',
                description: 'Termo de busca para filtrar as tarefas',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'limite',
                description: 'Número de itens por página',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 200)
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Número da página para paginação',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de tarefas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'rows', type: 'array', items: new OA\Items(type: 'object')),
                        new OA\Property(property: 'pagination', type: 'object')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function index()
    {
        $input = $this->request->getGet();
        $data = $this->searchTasks->listTasks($input);
        return $this->respond($data);
    }

    #[OA\Get(
        path: '/api/v1/tasks/{id}',
        description: 'Retorna os detalhes de uma tarefa específica pelo ID',
        summary: 'Obter detalhes de uma tarefa',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da tarefa',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes da tarefa retornados com sucesso',
                content: new OA\JsonContent(type: 'object')
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Tarefa não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]
    public function show($id = null)
    {
        try {
            $data = $this->getTasks->getId($id);
            return $this->respond($data);
        } catch (RuntimeException $e) {
            return $this->failNotFound($e->getMessage());
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }
    public function new(): void
    {
        //
    }

    #[OA\Post(
        path: '/api/v1/tasks',
        description: 'Cria uma nova tarefa com os dados fornecidos no corpo da requisição',
        summary: 'Criar uma nova tarefa',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Dados da nova tarefa',
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', description: 'Título da tarefa', type: 'string'),
                    new OA\Property(property: 'description', description: 'Descrição da tarefa (opcional)', type: 'string'),
                    new OA\Property(property: 'status', description: 'Status da tarefa (pending/completed)', type: 'string'),
                    new OA\Property(property: 'datetime', description: 'Data e hora da tarefa (opcional)', type: 'string', format: 'date-time')
                ]
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tarefa criada com sucesso',
                content: new OA\JsonContent(type: 'object')
            ),
            new OA\Response(response: 400, description: 'Erro de validação'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function create()
    {
        //
        try {
            $input = $this->request->getJSON(true);
            $rules = [
                'title'    => 'required'
            ];
            if (!$this->validate($rules)) {
                // Utiliza o método failValidationErrors() do ResponseTrait para retornar erros de validação
                return $this->failValidationErrors($this->validator->getErrors());
            }

            $data = $this->createTasks->taskCreate($input);
            return $this->respondCreated($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    public function edit($id = null)
    {
        //
    }

    #[OA\Put(
        path: '/api/v1/tasks/{id}',
        description: 'Atualiza os dados de uma tarefa específica pelo ID',
        summary: 'Atualizar uma tarefa existente',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'Dados atualizados da tarefa',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', description: 'Título da tarefa', type: 'string'),
                    new OA\Property(property: 'description', description: 'Descrição da tarefa (opcional)', type: 'string'),
                    new OA\Property(property: 'status', description: 'Status da tarefa (pending/completed)', type: 'string'),
                    new OA\Property(property: 'datetime', description: 'Data e hora da tarefa (opcional)', type: 'string', format: 'date-time')
                ]
            )
        ),
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da tarefa',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tarefa atualizada com sucesso',
                content: new OA\JsonContent(type: 'object')
            ),
            new OA\Response(response: 400, description: 'Erro de validação'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Tarefa não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function update($id = null)
    {
        try {
            if (is_null($id)) {
                return $this->failValidationErrors('Appointment ID is required.');
            }
            $input = $this->request->getJSON(true);
            $data = $this->updateTasks->taskUpdate($input, $id);
            return $this->respond($data);
        } catch (RuntimeException $e) {
            return $this->failNotFound($e->getMessage());
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[OA\Patch(
        path: '/api/v1/tasks/order',
        description: 'Atualiza a ordem de exibição das tarefas',
        summary: 'Atualizar a ordem das tarefas',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            description: 'IDs e suas novas ordens',
            required: true,
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', description: 'ID da tarefa', type: 'integer'),
                        new OA\Property(property: 'order', description: 'Nova ordem da tarefa', type: 'integer')
                    ],
                    type: 'object'
                )
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(response: 200, description: 'Ordem das tarefas atualizada com sucesso'),
            new OA\Response(response: 400, description: 'Erro de validação'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function order(): ResponseInterface
    {
        $input = $this->request->getJSON(true);
        try {
            $data = $this->updateTasks->taskUpdateOrder($input);
            return $this->respond($data);
        } catch (Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/v1/tasks/{id}',
        description: 'Deleta uma tarefa pelo ID',
        summary: 'Deletar uma tarefa',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID da tarefa',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tarefa deletada com sucesso'),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Tarefa não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    #[OA\Schema(
        schema: 'Task',
        properties: [
            new OA\Property(property: 'id', description: 'ID da tarefa', type: 'integer'),
            new OA\Property(property: 'title', description: 'Título da tarefa', type: 'string'),
            new OA\Property(property: 'description', description: 'Descrição da tarefa', type: 'string'),
            new OA\Property(property: 'status', description: 'Status da tarefa (pending/completed)', type: 'string'),
            new OA\Property(property: 'datetime', description: 'Data e hora da tarefa', type: 'string', format: 'date-time'),
            new OA\Property(property: 'order', description: 'Ordem da tarefa', type: 'integer')
        ],
        type: 'object'
    )]

    public function delete($id = null)
    {
        //
        try {
            // Check if the ID was provided
            if (is_null($id)) {
                return $this->failValidationErrors('Appointment ID is required.');
            }

            $this->deleteTasks->del((int) $id);

            // Return the success response with status 200 OK
            return $this->respondDeleted(['message' => 'Tasks deleted successfully.']);
        } catch (InvalidArgumentException $e) {
            // Respond with validation error (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (RuntimeException $e) {
            // Respond with execution error (404 Not Found or 403 Forbidden)
            return $this->failNotFound($e->getMessage());
        } catch (Exception $e) {
            // Respond with internal error (500 Internal Server Error)
            return $this->failServerError('Internal Server Error: ' . $e->getMessage());
        }
    }
}
