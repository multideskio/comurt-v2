<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Config/Routes.php
$routes->options('(:any)', function () {
    return service('response')
        ->setStatusCode(200) // Garante que o status é 200 OK
        //->setHeader('Access-Control-Allow-Origin', 'http://localhost:8000')  // Ajuste para a origem específica necessária
        //->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
        //->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
    ;
});

$routes->get('/', 'Home::index');
$routes->get('teste', 'Home::teste');
$routes->get('docs', 'DocsController::index');

$routes->get('suporte', '');

//Anamnese aberta
$routes->get('anamnese/(:any)', 'Api\V1\AnamnesesController::slug/$1');

/*$routes->get('teste', function () {
    echo password_hash("123456", PASSWORD_BCRYPT);
});*/

// Rotas para usuários logados
$routes->group('api/v1', ['filter' => 'jwt:PROFISSIONAL,TERAPEUTA_SI,SUPERADMIN'], function ($routes) {
    
    $routes->get('anamneses/comparation', 'Api\V1\AnamnesesController::comparation');
    $routes->resource('anamneses', ['controller' => 'Api\V1\AnamnesesController']);
    $routes->resource('plans', ['controller' => 'Api\V1\PlansController']);
    $routes->put('tasks/order', 'Api\V1\TasksController::order');
    $routes->resource('tasks',  ['controller' => 'Api\V1\TasksController']);

    $routes->resource('support',  ['controller' => 'Api\V1\SupportController']);
    
    //A busca pelo cliente está aberta para todos os usuários buscarem de acordo com seu próprio ID
    $routes->get('customers', 'Api\V1\CustomerController::index');
    
    //dados do usuário /api/v1/user/statistics
    $routes->get('user/statistics', 'Api\V1\UsersController::statistic');
    $routes->get('user/me', 'Api\V1\UsersController::me');
    $routes->put('user/me', 'Api\V1\UsersController::updateMe');
    
    //Rotas de agendamentos
    $routes->resource('appointments', ['controller' => 'Api\V1\AppointmentsController']);

    $routes->get('dashboard/appointments', 'Api\V1\TimelinesController::reportJson');

    $routes->resource('timeline', ['controller' => 'Api\V1\TimelinesController']);

    //Rotas de clientes
    $routes->resource('customers', ['controller' => 'Api\V1\CustomerController']);
});

// Rotas apenas SUPERADMIN
$routes->group('api/v1', ['filter' => 'jwt:SUPERADMIN'], function ($routes) {
    //rota protegida para criação de usuários, porém precisa da lógica para adicionar um usuário a um plano
    $routes->resource('users', ['controller' => 'Api\V1\UsersController']);
});


//Rotas liberadas apenas para os profissionais e superadmins
$routes->group('api/v1', ['filter' => 'jwt:PROFISSIONAL,SUPERADMIN'], function ($routes) {
    
   
});


//Rotas com throttle - ['filter' => 'throttle:10,hour']['filter' => 'throttle:1,hour'] - ['filter' => 'throttle:100,hour']
$routes->group('api/v1', function ($routes) {
    $routes->post('login', 'Api\V1\AuthController::login');
    $routes->post('magiclink', 'Api\V1\AuthController::magiclink');
});


$routes->group('api/v1', function ($routes) {
    // Options
    $routes->options('login', 'Api\V1\AuthController::login');
    //retorna qualquer coisa na rota get de login
    $routes->get('login', 'Api\V1\AuthController::aviso');
    //recupera senha
    $routes->post('recover', 'Api\V1\AuthController::recover');
    //cadastra nova senha
    $routes->put('recover', 'Api\V1\AuthController::newPass');
    //sai do sistema
    $routes->get('logout', 'Api\V1\AuthController::logout');
    //login com o google
    $routes->get('google', 'Api\V1\AuthController::googleLogin');
    //callback para login com o google
    $routes->get('auth/google/callback', 'Api\V1\AuthController::googleCallback');
    //webhook greem
    $routes->post('webhook/greem', 'Api\V1\WebhookController::greem');

    //
    $routes->post('crisp/first-chat', 'Api\V1\SupportController::webhookCrispFirstChat');
});
