<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;
//use Predis\Client as PredisClient;
//use Config\Redis as RedisConfig;

#[OA\OpenApi(
    info: new OA\Info(
        version: "1.0.0",
        description: "`API para demonstrar endpoints básicos do sistema`<br><ul><li>Essa documentação está sendo desenvolvida gradualmente, todos os endpoints estão passando por uma revisão.</li><li>Os endpoints que precisam estar com a autorização estão com um cadeado indicando o uso.</li><li>Para gerar o token, utilize o endpoint login.</li><li>Endpoints marcados como acesso ADMIN.</li></ul>",
        title: "Therapeutic Radiesthesia API",
        contact: new OA\Contact(
            name: 'Paulo Henrique',
            url: "https://api.conect.app",
            email: "webmaster@multidesk.io"
        ),
        /*license: new OA\License(
            name: 'API EM DESENVOLVIMENTO'
        )*/
    ),
    servers: [
        new OA\Server(
            url: "https://api.multidesk.io",
            description: "Servidor online"
        ),
        new OA\Server(
            url: "http://localhost:8000",
            description: "Servidor local"
        )
    ],
    tags: [
        new OA\Tag(name: "Status", description: ""),
        new OA\Tag(name: "Autenticação", description: "Operações relacionadas à autenticação de usuários"),
        new OA\Tag(name: "Usuários", description: "Gerenciamento de usuários"),
        new OA\Tag(name: "Agendamentos", description: "Agendamento de atendimento"),
        new OA\Tag(name: "Clientes", description: "Gerenciamento de clientes"),
        new OA\Tag(name: "Tasks", description: "Tarefas para serem realizadas cadastradas pelo proprio usuário do sistema"),
        new OA\Tag(name: "Anamneses", description: "Gerenciamento de Anamneses"),
        new OA\Tag(name: "Suporte", description: "Gerenciamento de suporte"),
        new OA\Tag(name: "Webhooks", description: "Gerenciamento de Webhooks"),
        
        // Outras tags podem ser adicionadas aqui
    ]
)]

#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    bearerFormat: "JWT",
    scheme: "bearer"
)]

class BaseController extends ResourceController
{
    use ResponseTrait;

    // Esta classe pode ser utilizada para definir anotações gerais e ser estendida pelos controladores específicos.
    protected $request;
    protected \CodeIgniter\Validation\ValidationInterface $validation;

    //protected $predis;

    public function __construct()
    {
        //$redisConfig = new RedisConfig();
        //$this->predis = new PredisClient($redisConfig->default);
        $this->validation = \Config\Services::validation();
        $this->request = service('request');
        helper('auxiliar');
    }
}
