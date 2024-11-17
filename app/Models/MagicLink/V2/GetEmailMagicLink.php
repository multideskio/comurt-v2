<?php

namespace App\Models\MagicLink\V2;

use App\Models\MagicLinkModel;
use DateTime;

class GetEmailMagicLink extends MagicLinkModel
{
    public function getEmailMagicLink(string $hash): string
    {
        $magicLink = $this->where('link_url', $hash)->first();

        if (!$magicLink) {
            return '';
        }

        return $magicLink['email'];
    }
}