<?php

namespace App\Controllers\Api\V1;

use App\Config\JwtConfig;
use App\Models\Appointments\V1\SearchAppointments;
use App\Models\Users\V1\MeUsers;
use App\Models\Users\V1\ProfileUpdate;
use App\Models\UsersModel;
use OpenApi\Attributes as OA;

class UsersController extends BaseController
{

    protected UsersModel $userModel;
    protected JwtConfig $jwtConfig;

    public function __construct()
    {
        parent::__construct();
        $this->jwtConfig = new JwtConfig();
        $this->userModel = new UsersModel();
    }

    #[OA\Get(
        path: "/api/v1/user/me",
        summary: "Obter informações do usuário autenticado",
        description: "Retorna as informações do usuário autenticado usando JWT",
        tags: ["Usuários"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Informações do usuário",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "id", type: "integer", example: 1),
                        new OA\Property(property: "name", type: "string", example: "John Doe"),
                        new OA\Property(property: "email", type: "string", example: "john.doe@example.com"),
                        new OA\Property(property: "photo", type: "string", example: "https://example.com/photo.jpg"),
                        new OA\Property(property: "role", type: "string", example: "PROFISSIONAL"),
                        new OA\Property(property: "plan", type: "string", example: "Premium"),
                        new OA\Property(property: "lang", type: "string", example: "en"),
                        new OA\Property(property: "languages", type: "array", items: new OA\Items(type: "string"), example: "en, es"),
                        new OA\Property(property: "description", type: "string", example: "Experienced professional in healthcare."),
                        new OA\Property(property: "education", type: "string", example: "PhD in Radiology"),
                        new OA\Property(property: "department", type: "string", example: "Radiology"),
                        new OA\Property(property: "social_networks", type: "object", example: "https://linkedin.com/in/johndoe, https://instagram.com/in/johndoe"),
                        new OA\Property(property: "company", type: "string", example: "Medical Inc."),
                        new OA\Property(property: "birthdate", type: "string", format: "date", example: "1985-08-15"),
                        new OA\Property(property: "show_personal_chart_dashboard", type: "boolean", example: true),
                        new OA\Property(property: "show_family_chart_dashboard", type: "boolean", example: false),
                        new OA\Property(property: "show_friends_chart_dashboard", type: "boolean", example: true),
                        new OA\Property(property: "show_appointments_chart_dashboard", type: "boolean", example: true),
                        new OA\Property(property: "show_basic_info_dashboard", type: "boolean", example: true),
                        new OA\Property(property: "receive_updates_email", type: "boolean", example: true),
                        new OA\Property(property: "receive_updates_sms", type: "boolean", example: false),
                        new OA\Property(property: "receive_updates_whatsapp", type: "boolean", example: true),
                        new OA\Property(property: "receive_scheduling_reminders", type: "boolean", example: true),
                        new OA\Property(property: "receive_cancellation_reminders", type: "boolean", example: true),
                        new OA\Property(property: "statistics", type: "object", example: "{\"total_appointments\": 50, \"cancelled_appointments\": 5}")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Não autorizado")
        ]
    )]
    

    public function me()
    {
        try {
            $me = new MeUsers;
            return $this->respond($me->me());
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }

    /*#[OA\Get(
        path: "/api/v1/users",
        summary: "Lista de usuários - Acesso admin - última atualização 13/09/2024 04:57",
        description: "Retorna todos os usuários e o plano contratado por ele",
        tags: ["Usuários"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                description: 'Campo para ordenação dos resultados',
                schema: new OA\Schema(type: 'string', enum: ['id', 'name', 'email'], default: 'id')
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
                description: 'Termo de busca para filtrar os usuários',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'limite',
                in: 'query',
                required: false,
                description: 'Número de itens por página',
                schema: new OA\Schema(type: 'integer', default: 15, maximum: 100)
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
                description: "Lista de usuários",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "rows", type: "array", items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 101),
                                new OA\Property(property: "name", type: "string", example: "John Doe"),
                                new OA\Property(property: "email", type: "string", example: "john.doe@example.com"),
                                new OA\Property(property: "phone", type: "string", example: "+55 (11) 9 8765-4321"),
                                new OA\Property(property: "platformId", type: "integer", example: 2),
                                new OA\Property(property: "admin", type: "boolean", example: false),
                                new OA\Property(property: "created", type: "string", format: "date-time", example: "2024-08-15T10:30:00Z", nullable: true),
                                new OA\Property(property: "update", type: "string", format: "date-time", example: "2024-09-12T14:45:00Z", nullable: true),
                                new OA\Property(property: "subscription_id", type: "integer", example: 5, nullable: true),
                                new OA\Property(property: "subscription_create", type: "string", format: "date-time", example: "2024-09-09T16:12:03Z", nullable: true),
                                new OA\Property(property: "plan_name", type: "string", example: "Premium Plan", nullable: true),
                                new OA\Property(property: "plan_id", type: "string", example: "abcd1234efgh5678ijkl9101", nullable: true),
                                new OA\Property(property: "plan_permissionUser", type: "integer", example: 3, nullable: true),
                                new OA\Property(property: "plan_timeSubscription", type: "integer", example: 12, nullable: true)
                            ]

                        )),
                        new OA\Property(
                            property: "pagination",
                            type: "object",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "total_pages", type: "integer", example: 1),
                                new OA\Property(property: "total_items", type: "integer", example: 3),
                                new OA\Property(property: "items_per_page", type: "integer", example: 15),
                                new OA\Property(property: "prev_page", type: "integer", example: null, nullable: true),
                                new OA\Property(property: "next_page", type: "integer", example: null, nullable: true)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido ou ausente'),
            new OA\Response(response: 403, description: 'Usuário sem permissão'),
        ]
    )]*/

    public function index()
    {
        //
        try {
            $input = $this->request->getVar();
            $data = $this->userModel->listUsers($input);
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
    public function show($id = null)
    {
        //
        return $this->respondNoContent();
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
        return $this->respondNoContent();
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        //
        return $this->respondNoContent();
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
        return $this->respondNoContent();
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
        return $this->respondNoContent();
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
        return $this->respondNoContent();
    }


    #[OA\Put(
        path: "/api/v1/user/me",
        summary: "Atualizar informações do usuário autenticado",
        description: "Permite que o usuário autenticado atualize suas informações de perfil.",
        tags: ["Usuários"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "photo", "phone", "lang", "languages", "description", "education", "department", "company", "birthdate"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "TERAPEUTA TP"),
                    new OA\Property(property: "photo", type: "string", example: "https://vinha-dev.s3.us-east-1.amazonaws.com/admin_geral/1/66c4784ed3f9f.png"),
                    new OA\Property(property: "phone", type: "string", example: "+55 62 9 9999 9999"),
                    new OA\Property(property: "lang", type: "string", example: "pt-BR"),
                    new OA\Property(property: "languages", type: "string", example: "En, Es, pt-BR"),
                    new OA\Property(property: "description", type: "string", example: "Coloque o texto que quiser"),
                    new OA\Property(property: "education", type: "string", example: "FACULDADE"),
                    new OA\Property(property: "department", type: "string", example: "Radiology"),
                    new OA\Property(property: "social_networks", type: "string", example: "social_networks"),
                    new OA\Property(property: "company", type: "string", example: "company"),
                    new OA\Property(property: "birthdate", type: "string", format: "date", example: "1990-01-01"),
                    new OA\Property(property: "show_personal_chart_dashboard", type: "boolean", example: true),
                    new OA\Property(property: "show_family_chart_dashboard", type: "boolean", example: true),
                    new OA\Property(property: "show_friends_chart_dashboard", type: "boolean", example: true),
                    new OA\Property(property: "show_appointments_chart_dashboard", type: "boolean", example: true),
                    new OA\Property(property: "show_basic_info_dashboard", type: "boolean", example: true),
                    new OA\Property(property: "receive_updates_email", type: "boolean", example: true),
                    new OA\Property(property: "receive_updates_sms", type: "boolean", example: true),
                    new OA\Property(property: "receive_updates_whatsapp", type: "boolean", example: true),
                    new OA\Property(property: "receive_scheduling_reminders", type: "boolean", example: true),
                    new OA\Property(property: "receive_cancellation_reminders", type: "boolean", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Perfil atualizado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Perfil atualizado com sucesso"),
                        new OA\Property(property: "data", type: "object", example: "{\"id\": 1, \"name\": \"TERAPEUTA TP\", \"email\": \"user@example.com\"}")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Erro de validação",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "error", type: "string", example: "Nome e foto são obrigatórios")
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
    
    public function updateMe()
    {
        try {
            $input = $this->request->getJSON(true);
            $updateMe = new ProfileUpdate;
            $data = $updateMe->updateProfile($input);
            return $this->respondUpdated($data);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }


    

    public function statistic(){

        $searchApp = new SearchAppointments();
        
        $input = $this->request->getGet();

        if(empty($input['type'])){

        }
        $data = $searchApp->statisticsWithComparison(2, $input['start'], $input['end']);
        return $this->respond($data);
    }
}
