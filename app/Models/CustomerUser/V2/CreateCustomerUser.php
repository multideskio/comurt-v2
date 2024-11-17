<?php

namespace App\Models\CustomerUser\V2;

use App\Models\CustomerUserModel;

class CreateCustomerUser extends CustomerUserModel
{
    public function createCustomerUser(array $data, string $email, array $files): bool
    {
        $data_to_save['email'] = $email;
        $data_to_save['nome_de_exibicao'] = $data['nome_de_exibicao'];
        $data_to_save['localizacao'] = $data['localizacao'];
        $data_to_save['telefone'] = $data['telefone'];
        $data_to_save['area_de_atuacao'] = $data['area_de_atuacao'];
        $data_to_save['abordagens'] = $data['abordagens'];
        $data_to_save['formacao'] = $data['formacao'];
        $data_to_save['experiencia'] = $data['experiencia'];
        $data_to_save['biografia'] = $data['biografia'];
        $data_to_save['foto_perfil'] = $files['foto_perfil'][0];
        $data_to_save['foto_documento'] = $files['foto_documento'][0];
        $data_to_save['foto_cnh'] = $files['foto_cnh'][0];
        $data_to_save['foto_perfil_64'] = $files['foto_perfil'][2];
        $data_to_save['foto_documento_64'] = $files['foto_documento'][2];
        $data_to_save['foto_cnh_64'] = $files['foto_cnh'][2];
        $data_to_save['foto_perfil_64_ext'] = $files['foto_perfil'][1];
        $data_to_save['foto_documento_64_ext'] = $files['foto_documento'][1];
        $data_to_save['foto_cnh_64_ext'] = $files['foto_cnh'][1];
        $data_to_save['status'] = 0;

        $id = $this->insert($data_to_save);

        log_message('info', "Inserted customer user id: {$id}");

        return $id ? true : false;
    }
}