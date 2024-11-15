<?php

namespace App\Controllers\Api\V1;

use App\Models\Anamneses\V1\ComparationAnamneses;
use App\Models\Anamneses\V1\DeleteAnamneses;
use App\Models\AnamnesesModel;
use App\Models\CustomersModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AnamnesesController extends BaseController
{
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    protected AnamnesesModel $modelAnamnese;

    public function __construct()
    {
        parent::__construct();
        $this->modelAnamnese = new AnamnesesModel();
    }

    #[OA\Delete(
        path: '/api/v1/anamneses/{id}',
        summary: 'Excluir uma anamnese',
        description: 'Exclui uma anamnese existente',
        tags: ['Anamneses'],
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
        //
        try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors(lang('errors.idIsRequired'));
            }
            // Chama o método deleteCustomer do model
            $delAnamnese = new DeleteAnamneses();
            $delAnamnese->deleteId((int) $id);
            // Retorna a resposta de sucesso com o status 200 OK
            return $this->respondDeleted(['message' => lang('Errors.resourceDeleted')]);
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


    #[OA\Get(
        path: '/anamnese/{slug}',
        summary: 'Consulta de anamnese sem login',
        description: 'Consulta aberta para compartilhamento com o cliente',
        tags: ['Anamneses'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                description: 'Slug da anamnese',
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Anamense encontrada'),
            new OA\Response(response: 404, description: 'Anamense encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function slug($slug)
    {
        try {
            // Verifica se o ID foi fornecido
            if (is_null($slug)) {
                return $this->failValidationErrors('O slug da anamanese é obrigatória.');
            }

            $anamnese = $this->modelAnamnese->where('slug', $slug)->first();

            if (!$anamnese) {
                return $this->failNotFound('Anamanese não econtrada.');
            }

            $modelCustomer = new CustomersModel();

            $customer = $modelCustomer->find($anamnese['id_customer']);

            if (!$customer) {
                return $this->failNotFound('Cliente não econtrado.');
            }

            return $this->respond([
                'customer' => $customer,
                'anamnese' => $anamnese
            ]);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/v1/anamneses',
        summary: 'Listar todas as Anamneses',
        description: 'Retorna uma lista de clientes com paginação',
        tags: ['Anamneses'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de anamneses',
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
            $data  = $this->modelAnamnese->search($input);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    #[OA\Get(
        path: '/api/v1/anamneses/{id}',
        summary: 'Obter detalhes de uma anamnese',
        description: 'Retorna os detalhes de uma anamnese específica',
        tags: ['Anamneses'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da anamnese',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalhes da Anamnese',
                content: new OA\JsonContent(type: 'object')
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 404, description: 'Anamnese não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function show($id = null)
    {
        //
        try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors('O ID do cliente é obrigatório.');
            }

            // Chama o método showCustomer do model para obter os dados do customer com anamneses
            $data = $this->modelAnamnese->showAnamnese((int) $id);

            // Retorna a resposta de sucesso com os dados do customer
            return $this->respond($data);
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

    public function new()
    {
        //
    }


    #[OA\Post(
        path: '/api/v1/anamneses',
        summary: 'Criar nova Anamnese',
        description: 'Cria uma nova anamnese para o cliente com base nos dados fornecidos.',
        tags: ['Anamneses'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para criar uma anamnese.',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'idCustomer', type: 'integer', description: 'ID do cliente'),
                    new OA\Property(property: 'idAppointment', type: 'integer', description: 'ID do agendamento'),
                    new OA\Property(property: 'mentalDesequilibrio', type: 'string', description: 'Desequilíbrio mental', enum: ['sim', 'não']),
                    new OA\Property(property: 'mentalPercentual', type: 'integer', description: 'Percentual de desequilíbrio mental', minimum: 0, maximum: 100),
                    new OA\Property(property: 'emocionalDesequilibrio', type: 'string', description: 'Desequilíbrio emocional', enum: ['sim', 'não']),
                    new OA\Property(property: 'emocionalPercentual', type: 'integer', description: 'Percentual de desequilíbrio emocional', minimum: 0, maximum: 100),
                    new OA\Property(property: 'espiritualDesequilibrio', type: 'string', description: 'Desequilíbrio espiritual', enum: ['sim', 'não']),
                    new OA\Property(property: 'espiritualPercentual', type: 'integer', description: 'Percentual de desequilíbrio espiritual', minimum: 0, maximum: 100),
                    new OA\Property(property: 'fisicoDesequilibrio', type: 'string', description: 'Desequilíbrio físico', enum: ['sim', 'não']),
                    new OA\Property(property: 'fisicoPercentual', type: 'integer', description: 'Percentual de desequilíbrio físico', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCoronarioDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra coronário', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCoronarioPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra coronário', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCoronarioAtividade', type: 'string', description: 'Atividade do chakra coronário', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraCoronarioOrgao', type: 'string', description: 'Órgão afetado pelo chakra coronário', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraFrontalDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra frontal', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraFrontalPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra frontal', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraFrontalAtividade', type: 'string', description: 'Atividade do chakra frontal', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraFrontalOrgao', type: 'string', description: 'Órgão afetado pelo chakra frontal', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraLaringeoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra laríngeo', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraLaringeoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra laríngeo', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraLaringeoAtividade', type: 'string', description: 'Atividade do chakra laríngeo', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraLaringeoOrgao', type: 'string', description: 'Órgão afetado pelo chakra laríngeo', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCardiacoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra cardíaco', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCardiacoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra cardíaco', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCardiacoAtividade', type: 'string', description: 'Atividade do chakra cardíaco', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraCardiacoOrgao', type: 'string', description: 'Órgão afetado pelo chakra cardíaco', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraPlexoSolarDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra plexo solar', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraPlexoSolarPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra plexo solar', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraPlexoSolarAtividade', type: 'string', description: 'Atividade do chakra plexo solar', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraPlexoSolarOrgao', type: 'string', description: 'Órgão afetado pelo chakra plexo solar', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraSacroDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra sacro', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraSacroPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra sacro', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraSacroAtividade', type: 'string', description: 'Atividade do chakra sacro', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraSacroOrgao', type: 'string', description: 'Órgão afetado pelo chakra sacro', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraBasicoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra básico', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraBasicoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra básico', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraBasicoAtividade', type: 'string', description: 'Atividade do chakra básico', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraBasicoOrgao', type: 'string', description: 'Órgão afetado pelo chakra básico', enum: ['sim', 'não']),
                    new OA\Property(property: 'tamanhoAura', type: 'integer', description: 'Tamanho da aura', minimum: 0),
                    new OA\Property(property: 'tamanhoAbertura', type: 'integer', description: 'Tamanho da abertura', minimum: 0),
                    new OA\Property(
                        property: 'corFalta',
                        type: 'array',
                        description: 'Cores em falta',
                        items: new OA\Items(type: 'string')
                    ),
                    new OA\Property(
                        property: 'corExcesso',
                        type: 'array',
                        description: 'Cores em excesso',
                        items: new OA\Items(type: 'string')
                    ),
                    new OA\Property(
                        property: 'corBase',
                        type: 'array',
                        description: 'Cores base',
                        items: new OA\Items(type: 'string')
                    ),
                    new OA\Property(property: 'energia', type: 'integer', description: 'Nível de energia', minimum: 0, maximum: 100),
                    new OA\Property(property: 'areasFamiliar', type: 'integer', description: 'Área familiar', minimum: 0, maximum: 100),
                    new OA\Property(property: 'areasAfetivo', type: 'integer', description: 'Área afetiva', minimum: 0, maximum: 100),
                    new OA\Property(property: 'areasProfissional', type: 'integer', description: 'Área profissional', minimum: 0, maximum: 100),
                    new OA\Property(property: 'areasFinanceiro', type: 'integer', description: 'Área financeira', minimum: 0, maximum: 100),
                    new OA\Property(property: 'areasMissao', type: 'integer', description: 'Área de missão', minimum: 0, maximum: 100)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Anamnese criada com sucesso',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', description: 'Mensagem de sucesso')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Requisição inválida'),
            new OA\Response(response: 422, description: 'Erros de validação'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function create()
    {
        try {
            // Obtenha os dados da solicitação JSON como um array associativo
            $input = $this->request->getJSON(true); // true garante que o JSON é convertido para array

            // Defina as regras de validação
            /*$rules = [
                'idCustomer' => 'required|integer',
                'idAppointment' => 'required|integer',
                'mentalDesequilibrio' => 'required|in_list[sim,não]',
                //'mentalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'emocionalDesequilibrio' => 'required|in_list[sim,não]',
                //'emocionalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'espiritualDesequilibrio' => 'required|in_list[sim,não]',
                //'espiritualPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'fisicoDesequilibrio' => 'required|in_list[sim,não]',
                //'fisicoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCoronarioDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraCoronarioPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCoronarioAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraCoronarioOrgao' => 'required|in_list[sim,não]',
                'chakraFrontalDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraFrontalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraFrontalAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraFrontalOrgao' => 'required|in_list[sim,não]',
                'chakraLaringeoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraLaringeoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraLaringeoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraLaringeoOrgao' => 'required|in_list[sim,não]',
                'chakraCardiacoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraCardiacoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCardiacoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraCardiacoOrgao' => 'required|in_list[sim,não]',
                'chakraPlexoSolarDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraPlexoSolarPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraPlexoSolarAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraPlexoSolarOrgao' => 'required|in_list[sim,não]',
                'chakraSacroDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraSacroPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraSacroAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraSacroOrgao' => 'required|in_list[sim,não]',
                'chakraBasicoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraBasicoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraBasicoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraBasicoOrgao' => 'required|in_list[sim,não]',
                'tamanhoAura' => 'required|integer|greater_than_equal_to[0]',
                //'tamanhoAbertura' => 'required|integer|greater_than_equal_to[0]',
                'corFalta' => 'required',
                'corExcesso' => 'required',
                //'energia' => 'required|integer|greater_than_equal_to[0]',
                'areasFamiliar' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasAfetivo' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasProfissional' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasFinanceiro' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasMissao' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
            ];

            // Defina as mensagens de erro personalizadas
            $messages = [
                'required' => 'O campo {field} é obrigatório.',
                'in_list' => 'O valor para o campo {field} deve ser um dos seguintes: {param}.',
                'integer' => 'O campo {field} deve ser um número inteiro.',
                'greater_than_equal_to' => 'O campo {field} deve ser maior ou igual a {param}.',
                'less_than_equal_to' => 'O campo {field} deve ser menor ou igual a {param}.',
                'checkArray' => 'O campo {field} deve ser um array.', // Mensagem personalizada para array
            ];

            // Configura a validação
            $validation = \Config\Services::validation();

            // Verifica se a validação falha
            if (!$validation->setRules($rules, $messages)->run($input)) {
                // Obtenha os erros de validação
                $errors = $validation->getErrors();
                // Retorna os erros como uma resposta de erro
                return $this->failValidationErrors([$errors]);
            }*/

            $data = $this->modelAnamnese->createAnamnese($input);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }


        // Continuação do processamento dos dados, como inserir no banco de dados, etc.
        // ...
    }

    public function edit($id = null)
    {
        //
    }


    #[OA\Put(
        path: '/api/v1/anamneses/{id}',
        summary: 'Edite uma Anamnese',
        description: 'Edite anamnese com base no ID fornecido na url.',
        tags: ['Anamneses'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID da anamnese',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Dados necessários para criar uma anamnese.',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'mentalDesequilibrio', type: 'string', description: 'Desequilíbrio mental', enum: ['sim', 'não']),
                    new OA\Property(property: 'mentalPercentual', type: 'integer', description: 'Percentual de desequilíbrio mental', minimum: 0, maximum: 100),
                    new OA\Property(property: 'emocionalDesequilibrio', type: 'string', description: 'Desequilíbrio emocional', enum: ['sim', 'não']),
                    new OA\Property(property: 'emocionalPercentual', type: 'integer', description: 'Percentual de desequilíbrio emocional', minimum: 0, maximum: 100),
                    new OA\Property(property: 'espiritualDesequilibrio', type: 'string', description: 'Desequilíbrio espiritual', enum: ['sim', 'não']),
                    new OA\Property(property: 'espiritualPercentual', type: 'integer', description: 'Percentual de desequilíbrio espiritual', minimum: 0, maximum: 100),
                    new OA\Property(property: 'fisicoDesequilibrio', type: 'string', description: 'Desequilíbrio físico', enum: ['sim', 'não']),
                    new OA\Property(property: 'fisicoPercentual', type: 'integer', description: 'Percentual de desequilíbrio físico', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCoronarioDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra coronário', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCoronarioPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra coronário', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCoronarioAtividade', type: 'string', description: 'Atividade do chakra coronário', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraCoronarioOrgao', type: 'string', description: 'Órgão afetado pelo chakra coronário', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraFrontalDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra frontal', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraFrontalPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra frontal', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraFrontalAtividade', type: 'string', description: 'Atividade do chakra frontal', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraFrontalOrgao', type: 'string', description: 'Órgão afetado pelo chakra frontal', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraLaringeoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra laríngeo', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraLaringeoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra laríngeo', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraLaringeoAtividade', type: 'string', description: 'Atividade do chakra laríngeo', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraLaringeoOrgao', type: 'string', description: 'Órgão afetado pelo chakra laríngeo', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCardiacoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra cardíaco', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraCardiacoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra cardíaco', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraCardiacoAtividade', type: 'string', description: 'Atividade do chakra cardíaco', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraCardiacoOrgao', type: 'string', description: 'Órgão afetado pelo chakra cardíaco', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraPlexoSolarDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra plexo solar', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraPlexoSolarPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra plexo solar', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraPlexoSolarAtividade', type: 'string', description: 'Atividade do chakra plexo solar', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraPlexoSolarOrgao', type: 'string', description: 'Órgão afetado pelo chakra plexo solar', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraSacroDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra sacro', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraSacroPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra sacro', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraSacroAtividade', type: 'string', description: 'Atividade do chakra sacro', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraSacroOrgao', type: 'string', description: 'Órgão afetado pelo chakra sacro', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraBasicoDesequilibrio', type: 'string', description: 'Desequilíbrio do chakra básico', enum: ['sim', 'não']),
                    new OA\Property(property: 'chakraBasicoPercentual', type: 'integer', description: 'Percentual de desequilíbrio do chakra básico', minimum: 0, maximum: 100),
                    new OA\Property(property: 'chakraBasicoAtividade', type: 'string', description: 'Atividade do chakra básico', enum: ['HIPO', 'HIPER']),
                    new OA\Property(property: 'chakraBasicoOrgao', type: 'string', description: 'Órgão afetado pelo chakra básico', enum: ['sim', 'não']),
                    new OA\Property(property: 'tamanhoAura', type: 'integer', description: 'Tamanho da aura', minimum: 0),
                    new OA\Property(property: 'tamanhoAbertura', type: 'integer', description: 'Tamanho da abertura', minimum: 0),
                    new OA\Property(
                        property: 'corFalta',
                        type: 'array',
                        description: 'Cores em falta',
                        items: new OA\Items(type: 'string')
                    ),
                    new OA\Property(
                        property: 'corExcesso',
                        type: 'array',
                        description: 'Cores em excesso',
                        items: new OA\Items(type: 'string')
                    ),
                    new OA\Property(property: 'energia', type: 'integer', description: 'Nível de energia', minimum: 0),
                    new OA\Property(property: 'areasFamiliar', type: 'string', description: 'Área familiar', enum: ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente']),
                    new OA\Property(property: 'areasAfetivo', type: 'string', description: 'Área afetiva', enum: ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente']),
                    new OA\Property(property: 'areasProfissional', type: 'string', description: 'Área profissional', enum: ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente']),
                    new OA\Property(property: 'areasFinanceiro', type: 'string', description: 'Área financeira', enum: ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente']),
                    new OA\Property(property: 'areasMissao', type: 'string', description: 'Área de missão', enum: ['pessimo', 'muito mal', 'mal', 'regular', 'bom', 'muito bom', 'excelente'])
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Anamnese criada com sucesso',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'message', type: 'string', description: 'Mensagem de sucesso')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Requisição inválida'),
            new OA\Response(response: 422, description: 'Erros de validação'),
            new OA\Response(response: 500, description: 'Erro interno do servidor')
        ]
    )]

    public function update($id = null)
    {
        //
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

            /*
                mentalPercentual
                emocionalPercentual
                espiritualPercentual
                fisicoPercentual
                chakraCoronarioPercentual
                chakraFrontalPercentual
                chakraLaringeoPercentual
                chakraCardiacoPercentual
                chakraPlexoSolarPercentual
                chakraSacroPercentual
                chakraBasicoPercentual
                tamanhoAbertura
                energia
            */

            // Defina as regras de validação
           /* $rules = [
                'mentalDesequilibrio' => 'required|in_list[sim,não]',
                //'mentalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'emocionalDesequilibrio' => 'required|in_list[sim,não]',
                //'emocionalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'espiritualDesequilibrio' => 'required|in_list[sim,não]',
                //'espiritualPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'fisicoDesequilibrio' => 'required|in_list[sim,não]',
                //'fisicoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCoronarioDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraCoronarioPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCoronarioAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraCoronarioOrgao' => 'required|in_list[sim,não]',
                'chakraFrontalDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraFrontalPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraFrontalAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraFrontalOrgao' => 'required|in_list[sim,não]',
                'chakraLaringeoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraLaringeoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraLaringeoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraLaringeoOrgao' => 'required|in_list[sim,não]',
                'chakraCardiacoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraCardiacoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraCardiacoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraCardiacoOrgao' => 'required|in_list[sim,não]',
                'chakraPlexoSolarDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraPlexoSolarPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraPlexoSolarAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraPlexoSolarOrgao' => 'required|in_list[sim,não]',
                'chakraSacroDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraSacroPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraSacroAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraSacroOrgao' => 'required|in_list[sim,não]',
                'chakraBasicoDesequilibrio' => 'required|in_list[sim,não]',
                //'chakraBasicoPercentual' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'chakraBasicoAtividade' => 'required|in_list[HIPO, HIPER]',
                'chakraBasicoOrgao' => 'required|in_list[sim,não]',
                'tamanhoAura' => 'required|integer|greater_than_equal_to[0]',
                //'tamanhoAbertura' => 'required|integer|greater_than_equal_to[0]',
                'corFalta' => 'required',
                'corExcesso' => 'required',
                //'energia' => 'required|integer|greater_than_equal_to[0]',
                'areasFamiliar' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasAfetivo' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasProfissional' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasFinanceiro' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
                'areasMissao' => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[100]',
            ];

            // Defina as mensagens de erro personalizadas
            $messages = [
                'required' => 'O campo {field} é obrigatório.',
                'in_list' => 'O valor para o campo {field} deve ser um dos seguintes: {param}.',
                'integer' => 'O campo {field} deve ser um número inteiro.',
                'greater_than_equal_to' => 'O campo {field} deve ser maior ou igual a {param}.',
                'less_than_equal_to' => 'O campo {field} deve ser menor ou igual a {param}.',
                'checkArray' => 'O campo {field} deve ser um array.', // Mensagem personalizada para array
            ];

            // Configura a validação
            $validation = \Config\Services::validation();

            // Verifica se a validação falha
            if (!$validation->setRules($rules, $messages)->run($input)) {
                // Obtenha os erros de validação
                $errors = $validation->getErrors();
                // Retorna os erros como uma resposta de erro
                return $this->failValidationErrors([$errors]);
            }*/

            $data = $this->modelAnamnese->updateAnamnese($input, $id);

            return $this->respond($data);
        } catch (\RuntimeException $e) {
            // Responde com erro de execução (400 Bad Request)
            return $this->fail($e->getMessage(), 400);
        }
    }






    #[OA\Get(
        path: '/api/v1/anamneses/comparation',
        tags: ['Anamneses'],
        security: [['bearerAuth' => []]],
        summary: 'Compara anamneses de um cliente com base em IDs fornecidos',
        description: '',
        operationId: 'compareAnamneses',
        parameters: [
            new OA\Parameter(
                name: 'baseId',
                in: 'query',
                required: true,
                description: 'ID anamnese base',
                example: '1',
                schema: new OA\Schema(
                    type: 'string'
                )
            ),
            new OA\Parameter(
                name: 'comparationId',
                in: 'query',
                required: true,
                description: 'ID anamnese para comparar',
                example: '2',
                schema: new OA\Schema(
                    type: 'string'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Comparação realizada com sucesso',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'comparisons',
                            description: 'Array de resultados de comparação',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', description: 'ID da anamnese comparada'),
                                    new OA\Property(property: 'id_customer', type: 'integer', description: 'ID do cliente associado'),
                                    new OA\Property(
                                        property: 'differences',
                                        type: 'object',
                                        description: 'Diferenças comparadas com a anamnese base',
                                        properties: [ // Removi additionalProperties, use diretamente properties
                                            new OA\Property(property: 'base_value', type: 'number', description: 'Valor na anamnese base'),
                                            new OA\Property(property: 'current_value', type: 'number', description: 'Valor atual comparado'),
                                            new OA\Property(property: 'difference', type: 'number', description: 'Diferença calculada'),
                                        ]
                                    ),
                                    new OA\Property(property: 'base_id', type: 'integer', description: 'ID da anamnese base para comparação')
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Parâmetros inválidos ou erro na requisição',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', description: 'Mensagem de erro detalhada')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Nenhuma anamnese encontrada para os IDs fornecidos',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', description: 'Mensagem de erro detalhada')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno do servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', description: 'Mensagem de erro interna')
                    ]
                )
            )
        ]
    )]



    public function comparation()
    {
        try {
            // Receber os dados via GET ou POST com getVar()
            $baseId        = $this->request->getGet('baseId');  // Capturar os IDs de qualquer requisição (GET ou POST)
            $comparationId = $this->request->getGet('comparationId');  // Capturar os IDs de qualquer requisição (GET ou POST)
            //$customer = $this->request->getVar('customer') ?? null;

            // Verificar se os IDs foram passados
            if (empty($baseId) && empty($comparationId)) {
                return $this->fail('No IDs provided');
            }

            $ids = "{$baseId},{$comparationId}";


            // Instanciar o modelo para realizar a comparação
            $anamnesesModel = new ComparationAnamneses();

            // Realizar a comparação (passando a string de IDs)
            $comparisonResults = $anamnesesModel->compare($ids);

            // Retornar os resultados
            return $this->respond([
                'comparisons' => $comparisonResults
            ]);
        } catch (\RuntimeException $e) {
            // Tratar exceções do tipo RuntimeException e retornar erro com status 400 (Bad Request)
            return $this->fail($e->getMessage(), 400);
        } catch (\DomainException $e) {
            // Tratar exceções do tipo DomainException e retornar erro com status 404 (Not Found)
            return $this->fail($e->getMessage(), 404);
        } catch (\Exception $e) {
            // Tratar qualquer outra exceção genérica e retornar erro com status 500 (Internal Server Error)
            return $this->fail($e->getMessage(), 500);
        }
    }
}
