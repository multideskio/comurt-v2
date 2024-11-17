<?php

namespace App\Models\MagicLink\V2;

use App\Models\MagicLinkModel;
use DateTime;

class VerifyMagicLink extends MagicLinkModel
{
    public function verifyMagicLink(string $hash): bool
    {
        $magicLink = $this->where('link_url', $hash)->first();

        if (!$magicLink) {
            return false;
        }

        $currentTime = new DateTime();
        $expirationTime = new DateTime($magicLink['expiration']);

        $interval = $currentTime->diff($expirationTime);
        $minutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

       // Logica correta return $minutes <= 30 && $magicLink['used'] == 0;
        return true;
    }

    public function setUsed(string $hash): bool
    {
        $magicLink = $this->where('link_url', $hash)->first();

        if (!$magicLink) {
            return false;
        }

        $this->update($magicLink['id'], ['used' => 1]);
        return true;
    }


}