<?php

namespace App\Models\MagicLink\V2;

use App\Models\MagicLinkModel;

class CreateMagicLink extends MagicLinkModel
{

    public function createMagicLink(string $email, string $hash): string
    {

        $expiration = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $data = [
            'email' => $email,
            'link_url' => $hash,
            'used' => 0,
            'expiration' => $expiration
        ];

        $id = $this->insert($data);

        log_message('info', "Inserted id: {$id}");

        return true;
    }
}