<?php

namespace App\Models\CustomerUser\V2;

use App\Models\CustomerUserModel;

class GetCustomerUser extends CustomerUserModel
{
    public function getUserByEmail($email)
    {
        return $this->select([
            'nome_de_exibicao',
            'localizacao',
            'telefone',
            'area_de_atuacao',
            'abordagens',
            'formacao',
            'experiencia',
            'biografia',
            'foto_perfil_64',
            'foto_perfil_64_ext',
            'status'
        ])->where('email', $email)->first();
    }
}