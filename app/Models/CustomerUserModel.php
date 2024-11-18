<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerUserModel extends Model
{
    protected $table = 'customer_user';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nome_de_exibicao',
        'email',
        'localizacao',
        'telefone',
        'foto_perfil',
        'area_de_atuacao',
        'abordagens',
        'formacao',
        'experiencia',
        'biografia',
        'foto_documento',
        'foto_cnh',
        'status',
        'foto_perfil_64',
        'foto_documento_64',
        'foto_cnh_64',
        'foto_perfil_64_ext',
        'foto_documento_64_ext',
        'foto_cnh_64_ext'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}