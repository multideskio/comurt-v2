<?php

namespace App\Controllers\Api\V2;

use App\Controllers\BaseController;
use App\Libraries\EmailsLibraries;
use App\Models\MagicLink\V2\GetEmailMagicLink;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Redis as RedisConfig;
use Predis\Client as RedisClient;
use Ramsey\Uuid\Uuid;

class Chat extends BaseController
{
    use ResponseTrait;

    protected $redis;

    public function __construct()
    {
        $redisConfig = new RedisConfig();
        $this->redis = new RedisClient($redisConfig->default);
    }

    public function chatUser($hash): \CodeIgniter\HTTP\ResponseInterface
    {
        $magicLinkModel = new GetEmailMagicLink();
        $email = $magicLinkModel->getEmailMagicLink($hash);

        if ($email == '') {
            return $this->fail('Not Allow', 403);
        }

        $data = $this->request->getJSON(true);
        $uuid = Uuid::uuid4()->toString();

        if (!isset($data['email_profissional'])) {
            return $this->fail('email_profissional is required', 400);
        }

        $redisKey = 'chat_' . $data['email_profissional'] . '_' . $email;
        $existingData = $this->redis->get($redisKey);

        if ($existingData) {
            return $this->respond(['uuid' => json_decode($existingData)->id, 'message' => 'Chat professional already exists']);
        }

        $emailData = [
            'email_profissional' => $data['email_profissional'],
            'email_aluno' => $email,
            'id' => $uuid
        ];

        $this->redis->set('chat_' . $emailData['email_profissional'] . '_' . $emailData['email_aluno'], json_encode($emailData));

        return $this->respond(['uuid' => $uuid, 'message' => 'Chat user created successfully']);
    }

    public function chatProfessional(): \CodeIgniter\HTTP\ResponseInterface
    {
        $data = $this->request->getJSON(true);
        $uuid = Uuid::uuid4()->toString();

        if (!isset($data['email_profissional']) || !isset($data['email_aluno'])) {
            return $this->fail('Both email_profissional and email_aluno fields are required', 400);
        }

        $redisKey = 'chat_' . $data['email_profissional'] . '_' . $data['email_aluno'];
        $existingData = $this->redis->get($redisKey);

        if ($existingData) {
            return $this->respond(['uuid' => json_decode($existingData)->id, 'message' => 'Chat professional already exists']);
        }

        $emailData = [
            'email_profissional' => $data['email_profissional'],
            'email_aluno' => $data['email_aluno'],
            'id' => $uuid
        ];

        $this->redis->set('chat_' . $emailData['email_profissional'] . '_' . $emailData['email_aluno'], json_encode($emailData));

        return $this->respond(['uuid' => $uuid, 'message' => 'Chat professional created successfully']);
    }
}