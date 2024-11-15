<?php

namespace App\Controllers\Api\V1;

use OpenApi\Attributes as OA;
use App\Libraries\ReportsLibraries;
use App\Models\TimeLinesModel;
use CodeIgniter\HTTP\ResponseInterface;

class TimelinesController extends BaseController
{
    protected TimeLinesModel $modelTimeLine;
    public function __construct()
    {
        parent::__construct();
        $this->modelTimeLine = new TimeLinesModel();
    }
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
        /*try {
            // Verifica se o ID foi fornecido
            if (is_null($id)) {
                return $this->failValidationErrors('O ID do cliente é obrigatório.');
            }

            // Chama o método showCustomer do model para obter os dados do customer com anamneses
            $data = $this->modelTimeLine->showTimeLineCustomer((int) $id);

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
        }*/
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
    public function create()
    {
        //
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


    #[OA\Get(
        path: '/api/v1/dashboard/appointments',
        summary: 'Relatórios de compromissos, cancelamentos, anamneses e retornos.',
        description: 'Este endpoint retorna relatórios detalhados de compromissos, cancelamentos, anamneses e retornos do usuário autenticado. O relatório pode ser gerado por ano, mês, semana ou dia, dependendo dos parâmetros passados.',
        tags: ['Usuários'],
        security: [['bearerAuth' => []]], // Necessita de autenticação via Bearer Token
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                description: 'O tipo de relatório. Pode ser "annual", "monthly", "weekly", "daily" ou "compareWithLastWeek". compareWithLastWeek não respeita o campo start e end, pega a semana passada e a semana atual.',
                required: true,
                schema: new OA\Schema(type: 'string', enum: ['annual', 'monthly', 'weekly', 'daily', 'compareWithLastWeek'])
            ),
            new OA\Parameter(
                name: 'start',
                in: 'query',
                description: 'A data de início no formato YYYY-MM-DD. Exigido para relatórios que não sejam "compareWithLastWeek".',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'end',
                in: 'query',
                description: 'A data de fim no formato YYYY-MM-DD. Exigido para relatórios que não sejam "compareWithLastWeek".',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Relatório gerado com sucesso',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'date', type: 'string', description: 'Data ou intervalo de tempo no relatório (varia de acordo com o tipo de relatório)'),
                            new OA\Property(property: 'appointments', type: 'integer', description: 'Número de compromissos'),
                            new OA\Property(property: 'cancelled', type: 'integer', description: 'Número de compromissos cancelados'),
                            new OA\Property(property: 'anamneses', type: 'integer', description: 'Número de anamneses'),
                            new OA\Property(property: 'return', type: 'integer', description: 'Número de retornos')
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Erro de validação'
            ),
            new OA\Response(
                response: 401,
                description: 'Token inválido ou ausente'
            ),
            new OA\Response(
                response: 500,
                description: 'Erro interno do servidor'
            )
        ]
    )]


    public function reportJson()
    {
        try {
            $reportMes = new ReportsLibraries();
            $input = $this->request->getGet();

            // Validar os parâmetros fornecidos
            if (empty($input['type'])) {
                return $this->fail('O parâmetro "type" é obrigatório.', 422);
            }

            // Verificar se start e end são necessários para o tipo de relatório
            if ($input['type'] !== 'compareWithLastWeek' && (empty($input['start']) || empty($input['end']))) {
                return $this->fail('Os parâmetros "start" e "end" são obrigatórios para este tipo de relatório.', 422);
            }

            return $this->respond($reportMes->resultReports($input));
        } catch (\Exception $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
