<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentsModel extends Model
{
    protected $table            = 'appointments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_user',
        'id_customer',
        'date',
        'status',
        'type',
        'observation',
        'id_deleted'
    ];

    protected bool $allowEmptyInserts = true;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    /**
     * Obtém o usuário autenticado.
     *
     * @return array Dados do usuário autenticado.
     * @throws \RuntimeException Se o usuário não estiver autenticado.
     */
    public function getAuthenticatedUser(): array
    {

        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        return $currentUser;
    }
}
