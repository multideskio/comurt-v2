<?php

namespace App\Controllers\Api\V1;

use App\Models\Appointments\V1\CreateAppointments;
use App\Models\Appointments\V1\DeleteAppointments;
use App\Models\Appointments\V1\GetAppointments;
use App\Models\Appointments\V1\SearchAppointments;
use App\Models\Appointments\V1\UpdateAppointments;
use App\Models\AppointmentsModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;


class AppointmentsController extends BaseController
{
    use ResponseTrait;
    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    protected AppointmentsModel $modelAppointments;

    public function __construct()
    {
        parent::__construct();
        $this->modelAppointments = new AppointmentsModel();
    }



    #[OA\Get(
        path: '/api/v1/appointments',
        summary: 'Search appointments',
        description: 'Retrieves a list of appointments based on various filters.',
        tags: ['Agendamentos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 's',
                in: 'query',
                required: false,
                description: 'Search term for filtering appointments by customer name, email, or phone',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'order',
                in: 'query',
                required: false,
                description: 'Order of the results. Possible values: ASC, DESC.',
                schema: new OA\Schema(type: 'string', enum: ['ASC', 'DESC'], default: 'ASC')
            ),
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                description: 'Field to sort the results by. Possible values: id, date, name, status.',
                schema: new OA\Schema(type: 'string', enum: ['id', 'date', 'name', 'status'], default: 'id')
            ),
            new OA\Parameter(
                name: 'limite',
                in: 'query',
                required: false,
                description: 'Limit the number of results per page',
                schema: new OA\Schema(type: 'integer', default: 10)
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Page number for pagination',
                schema: new OA\Schema(type: 'integer', default: 1)
            ),
            new OA\Parameter(
                name: 'start',
                in: 'query',
                required: false,
                description: 'Start date for filtering appointments in format Y-m-d H:i',
                schema: new OA\Schema(type: 'string', format: 'datetime', example: '2024-09-01 00:00')
            ),
            new OA\Parameter(
                name: 'end',
                in: 'query',
                required: false,
                description: 'End date for filtering appointments in format Y-m-d H:i',
                schema: new OA\Schema(type: 'string', format: 'datetime', example: '2024-09-30 23:59')
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Status of the appointments to filter. Possible values: pending, completed, cancelled.',
                schema: new OA\Schema(type: 'string', enum: ['pending', 'completed', 'cancelled'])
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                description: 'Type of the appointment. Possible values: consultation, anamnesis, return.',
                schema: new OA\Schema(type: 'string', enum: ['consultation', 'anamnesis', 'return'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of appointments retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(
                            property: 'rows',
                            type: 'array',
                            description: 'List of appointments',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id_appointment', type: 'integer', description: 'ID of the appointment'),
                                    new OA\Property(property: 'appointment', type: 'string', format: 'datetime', description: 'Date and time of the appointment'),
                                    new OA\Property(property: 'status', type: 'string', description: 'Status of the appointment'),
                                    new OA\Property(property: 'id_customer', type: 'integer', description: 'ID of the customer'),
                                    new OA\Property(property: 'name_customer', type: 'string', description: 'Name of the customer'),
                                    new OA\Property(property: 'id_user', type: 'integer', description: 'ID of the user who created the appointment'),
                                    new OA\Property(property: 'name_user', type: 'string', description: 'Name of the user who created the appointment')
                                ]
                            )
                        ),
                        new OA\Property(
                            property: 'params',
                            type: 'object',
                            description: 'Parameters used for filtering',
                            properties: [
                                new OA\Property(property: 's', type: 'string', description: 'Search term used'),
                                new OA\Property(property: 'order', type: 'string', description: 'Order of results'),
                                new OA\Property(property: 'sort_by', type: 'string', description: 'Field used for sorting'),
                                new OA\Property(property: 'limite', type: 'integer', description: 'Number of results per page'),
                                new OA\Property(property: 'page', type: 'integer', description: 'Current page number'),
                                new OA\Property(property: 'start', type: 'string', format: 'datetime', description: 'Start date for filtering'),
                                new OA\Property(property: 'end', type: 'string', format: 'datetime', description: 'End date for filtering'),
                                new OA\Property(property: 'status', type: 'string', description: 'Status filter used'),
                            ]
                        ),
                        new OA\Property(
                            property: 'dateRange',
                            type: 'object',
                            description: 'Date range applied in filtering',
                            properties: [
                                new OA\Property(property: 'start', type: 'string', format: 'datetime', description: 'Start date of the range'),
                                new OA\Property(property: 'end', type: 'string', format: 'datetime', description: 'End date of the range')
                            ]
                        ),
                        new OA\Property(
                            property: 'pagination',
                            type: 'object',
                            description: 'Pagination details',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', description: 'Current page number'),
                                new OA\Property(property: 'total_pages', type: 'integer', description: 'Total number of pages'),
                                new OA\Property(property: 'total_items', type: 'integer', description: 'Total number of items'),
                                new OA\Property(property: 'items_per_page', type: 'integer', description: 'Number of items per page'),
                                new OA\Property(property: 'prev_page', type: 'integer', nullable: true, description: 'Previous page number if applicable'),
                                new OA\Property(property: 'next_page', type: 'integer', nullable: true, description: 'Next page number if applicable')
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing token'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error: Check the query parameters'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function index()
    {
        try {
            $input = $this->request->getVar();
            $listAppointments = new SearchAppointments();
            $data = $listAppointments->listAppointments($input);
            return $this->respond($data);
        } catch (\Exception $e) {
            return $this->fail($e->getMessage());
        }
    }



    #[OA\Get(
        path: '/api/v1/appointments/{id}',
        summary: 'Get an appointment',
        description: 'Retrieves details of an appointment by its ID.',
        tags: ['Agendamentos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appointment details retrieved successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id_appointment', type: 'integer', description: 'ID of the appointment'),
                        new OA\Property(property: 'date', type: 'string', format: 'datetime', description: 'Date and time of the appointment'),
                        new OA\Property(property: 'status', type: 'string', description: 'Status of the appointment'),
                        new OA\Property(property: 'id_customer', type: 'integer', description: 'ID of the customer'),
                        new OA\Property(property: 'name_customer', type: 'string', description: 'Name of the customer'),
                        new OA\Property(property: 'id_user', type: 'integer', description: 'ID of the user'),
                        new OA\Property(property: 'name_user', type: 'string', description: 'Name of the user who created the appointment')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing token'
            ),
            new OA\Response(
                response: 404,
                description: 'Appointment not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error: ID is required'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function show($id = null)
    {
        try {
            // Check if the ID was provided
            if (is_null($id)) {
                return $this->failValidationErrors('Appointment ID is required.');
            }

            $getApp = new GetAppointments();

            $data = $getApp->id($id);

            return $this->respond($data); // Returns 200 OK with appointment details
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            // Respond with 404 Not Found if the appointment does not exist
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Respond with generic error for other exceptions
            return $this->fail($e->getMessage());
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
    }

    #[OA\Post(
        path: '/api/v1/appointments',
        summary: 'Create a new appointment',
        description: 'Creates a new appointment with the provided data.',
        tags: ['Agendamentos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'id_customer',
                        type: 'integer',
                        description: 'ID of the customer'
                    ),
                    new OA\Property(
                        property: 'date',
                        type: 'string',
                        format: 'datetime',
                        description: 'Date and time of the appointment in format Y-m-d H:i',
                        example: '2024-09-15 09:30'
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        description: 'Type of the appointment. Possible values: consultation, anamnesis, return.',
                        enum: ['consultation', 'anamnesis', 'return']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Appointment created successfully',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', description: 'ID of the created appointment'),
                        new OA\Property(property: 'message', type: 'string', example: 'Schedule created successfully.')
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Conflict: Appointment already exists'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]
    public function create()
    {
        try {
            $createAppointments = new CreateAppointments();
            $input = $this->request->getJSON(true);
            $create = $createAppointments->create($input);
            return $this->respondCreated($create); // 201 Created
        } catch (\RuntimeException $e) {
            // Specific business logic errors captured
            return $this->failValidationErrors($e->getMessage()); // 422 Unprocessable Entity
        } catch (\DomainException $e) {
            // Conflict error, e.g., duplicate appointment
            return $this->failResourceExists($e->getMessage()); // 409 Conflict
        } catch (\Exception $e) {
            // Generic or unexpected errors
            log_message('error', $e->getMessage()); // Log for monitoring
            return $this->failServerError('Internal Server Error: Please try again later.'); // 500 Internal Server Error
        }
    }

    public function edit($id = null)
    {
        //

    }


    #[OA\Put(
        path: '/api/v1/appointments/{id}',
        summary: 'Update an appointment',
        description: 'Updates an existing appointment based on the provided ID.',
        tags: ['Agendamentos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'status',
                        type: 'string',
                        description: 'Status of the appointment. Possible values: pending, completed, cancelled.',
                        enum: ['pending', 'completed', 'cancelled']
                    ),
                    new OA\Property(
                        property: 'type',
                        type: 'string',
                        description: 'Type of the appointment. Possible values: consultation, anamnesis, return.',
                        enum: ['consultation', 'anamnesis', 'return']
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appointment updated successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing token'
            ),
            new OA\Response(
                response: 404,
                description: 'Appointment not found'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]

    public function update($id = null)
    {
        try {
            // Check if the ID was provided
            if (is_null($id)) {
                return $this->failValidationErrors('Appointment ID is required.');
            }

            $appUpdate = new UpdateAppointments();

            $input = $this->request->getJSON(TRUE);

            // Calls the updateRow method of the model
            $appUpdate->updateRow((int) $id, $input);

            // Returns the success response with status 200 OK
            return $this->respond(['message' => 'Appointment updated successfully.']);
        } catch (\InvalidArgumentException $e) {
            // Respond with validation error (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Respond with execution error (404 Not Found or 403 Forbidden)
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Respond with internal error (500 Internal Server Error)
            return $this->failServerError('Internal Server Error: ' . $e->getMessage());
        }
    }



    #[OA\Delete(
        path: '/api/v1/appointments/{id}',
        summary: 'Delete an appointment',
        description: 'Deletes an existing appointment based on the provided ID.',
        tags: ['Agendamentos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Appointment ID',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Appointment deleted successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid or missing token'
            ),
            new OA\Response(
                response: 404,
                description: 'Appointment not found'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal server error'
            )
        ]
    )]

    public function delete($id = null)
    {
        try {
            // Check if the ID was provided
            if (is_null($id)) {
                return $this->failValidationErrors('Appointment ID is required.');
            }

            $appDelete = new DeleteAppointments();
            // Call the delete method of the model
            $appDelete->del((int) $id);

            // Return the success response with status 200 OK
            return $this->respondDeleted(['message' => 'Appointment deleted successfully.']);
        } catch (\InvalidArgumentException $e) {
            // Respond with validation error (422 Unprocessable Entity)
            return $this->failValidationErrors($e->getMessage());
        } catch (\RuntimeException $e) {
            // Respond with execution error (404 Not Found or 403 Forbidden)
            return $this->failNotFound($e->getMessage());
        } catch (\Exception $e) {
            // Respond with internal error (500 Internal Server Error)
            return $this->failServerError('Internal Server Error: ' . $e->getMessage());
        }
    }
}
