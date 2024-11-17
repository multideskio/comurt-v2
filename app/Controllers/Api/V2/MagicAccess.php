<?php

namespace App\Controllers\Api\V2;

use App\Controllers\BaseController;
use App\Libraries\EmailsLibraries;
use App\Models\MagicLink\V2\VerifyMagicLink;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\MagicLink\V2\CreateMagicLink;

class MagicAccess extends BaseController
{
    use ResponseTrait;

    public function create(): \CodeIgniter\HTTP\ResponseInterface
    {
        helper('auxiliar');
        $email = $this->request->getVar('email');

        if ($email) {
            $magicLink = generateMagicLink($email);
            $emailSender =  new EmailsLibraries();

            $resultSendEmail = $emailSender->send($email, "acesso", "Clique no link para acessar: {$magicLink}");

            if(!$resultSendEmail){
                return $this->fail('Failed to send email', 500);
            }

            $magicLinkModel = new CreateMagicLink();
            $magicLinkModel->createMagicLink($email, $magicLink);

            log_message('info', "Received email: {$email}");
            log_message('info', "Generated magic link: {$magicLink}");

        } else {

            return $this->fail('Email is required', 400);
        }


        return $this->respond(['message' => 'Magic link created successfully']);
    }

    public function checkMagicLink($hash)
    {
        $magicLinkModel = new VerifyMagicLink();
        $isValid = $magicLinkModel->verifyMagicLink($hash);

        if (!$isValid) {
            return $this->fail('Link expired or doesnt exist or already used', 400);
        }

        $wasKill = $magicLinkModel->setUsed($hash);

        if(!$wasKill){
            return $this->fail('Failed to kill magic link', 400);
        }


        return $this->respond(['message' => 'Magic link is valid']);


    }
}
