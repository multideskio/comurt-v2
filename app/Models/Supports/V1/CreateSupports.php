<?php
declare(strict_types=1);

namespace App\Models\Supports\V1;

use App\Libraries\EmailsLibraries;
use App\Models\PlatformModel;
use App\Models\SupportModel;

/**
 * CHANNEL
 * site = form
 * crisp = crisp
 */
class CreateSupports extends SupportModel
{

    public function createSupportSystem(array $params): array{
        helper('auxiliar');
        
        $user     = $this->getAuthenticatedUser();
        $protocol = create_protocol(intval($user['id']));

        $data = [
            'id_user' => $user['id'],
            'email' => $user['email'],
            'name' => $params['name'],
            'subject' => $params['subject'],
            'type' => $params['type'],
            'message' => $params['message'],
            'protocol' => $protocol,
            'channel' => $params['channel'] ?? "form"
        ];

        $id = $this->insert($data);

        $this->sendEmail($params['name'], $user['email'], 'support_create', "Suporte: {$protocol}", $protocol, $params['message']);


        return ['id' => $id, 'protocol' => $protocol];
    }

    public function createSupportWebhook(array $params){
        helper('auxiliar');
        
        $user     = $this->getAuthenticatedUser();
        $protocol = create_protocol(intval($user['id']));

        $data = [
            'id_user' => $user['id'],
            'email' => $user['email'],
            'name' => $params['name'],
            'subject' => $params['subject'],
            'type' => $params['type'],
            'message' => $params['message'],
            'protocol' => $protocol,
            'channel' => "form"
        ];

        $id = $this->insert($data);


        return ['id' => $id, 'protocol' => $protocol];
    }

    public function respondSupport(array $params){
        helper('auxiliar');
        
        $user     = $this->getAuthenticatedUser();
        $protocol = create_protocol($user['id']);

        $data = [
            'id_user' => $user['id'],
            'message' => $params
        ];

        $id = $this->insert($data);


        return ['id' => $id, 'protocol' => $protocol];
    }

    private function sendEmail($name, $email, $template, $subject, $protocol, $msg): void
    {
        $platForm          = $this->platForm();

        $data['name']      = $name;
        $data['email']     = $email;
        $data['protocol']  = $protocol;
        $data['baseUrl']   = $platForm['urlBase'];
        $data['company']   = $platForm['company'];

        $liEmail = new EmailsLibraries;
        $html    = view("emails/{$template}", $data);
        
        $liEmail->send($email, $subject, $html);
        
        $dataAdmin = "
        Protocolo: {$protocol} <br>
        Nome: {$name} <br>
        Email: {$email} <br>
        Mensagem: {$msg} <br>
        ";

        $liEmail->send($platForm['emailSupport'], "Pedido de suporte: {$protocol}", $dataAdmin);
    }

    private function platForm(): array
    {
        $modelPlatform = new PlatformModel();
        $data = $modelPlatform->first();

        if (!$data) {
            throw new \Exception("Error fetching platform data");
        }
        return $data;
    }
}