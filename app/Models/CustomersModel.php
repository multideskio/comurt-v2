<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomersModel extends Model
{
    protected $table            = 'customers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['idUser', 'name', 'photo', 'phone', 'email', 'phone', 'birthDate', 'doc', 'generous', 'type'];

    protected bool $allowEmptyInserts = false;
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




    private function validateItemsPerPage($value)
    {
        // Verifica se o valor está definido, se é numérico, e tenta converter para inteiro
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;

        // Se a conversão falhar, retorna 15
        if (!$itemsPerPage) {
            $itemsPerPage = 15;
        }

        // Verifica o limite máximo de 200
        if ($itemsPerPage > 200) {
            $itemsPerPage = 200;
        }

        return $itemsPerPage;
    }




    public function updateCustomer(array $params, $id): array
    {
        // Obter o usuário atual
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        $currentUserId = $currentUser['id'];

        // Verifica se o customer pertence ao usuário atual
        $customer = $this->where('id', $id)->where('idUser', $currentUserId)->first();

        if (!$customer) {
            throw new \RuntimeException('Customer não encontrado ou você não tem permissão para editá-lo.');
        }

        // Validação básica dos parâmetros
        if (empty($params['email']) || empty($params['name']) || empty($params['phone'])) {
            throw new \InvalidArgumentException('Campos obrigatórios não preenchidos.');
        }

        // Verifica se o endereço de e-mail já existe para outro customer do mesmo usuário
        $row = $this->where('email', $params['email'])
            ->where('idUser', $currentUserId)
            ->where('id !=', $id) // Garante que não estamos comparando com o mesmo registro
            ->countAllResults();

        if ($row > 0) {
            throw new \RuntimeException('Esse e-mail já está cadastrado. Verifique na sua tabela de clientes.');
        }

        // Dados para atualização
        $data = [
            'name' => $params['name'],
            'email' => $params['email'],
            'phone' => $params['phone'],
            'photo' => $params['photo'] ?? null,
            'birthDate' => $params['birthDate'] ?? null, // Fallback para campos opcionais
            'doc' => $params['doc'] ?? null,
            'generous' => $params['generous'] ?? null
        ];

        // Atualiza o registro no banco de dados
        if (!$this->update($id, $data)) {
            // Captura erros da instância correta do Model
            $errors = $this->errors();
            throw new \RuntimeException('Erro ao atualizar o cliente: ' . implode(', ', $errors));
        }

        return ['id' => $id, 'message' => 'Customer updated'];
    }



    public function showCustomer(int $id): array
    {
        // Obter o usuário atual
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        // Verificar se o usuário está autenticado
        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        if($currentUser['role'] !== 'SUPERADMIN'){
            $this->where('customers.idUser', $currentUser['id']);
        }

        $currentUserId = $currentUser['id'];

        // Realizar a busca do customer com JOIN para incluir anamneses
        $this->select('customers.*, COUNT(anamneses.id) as anamneses_count')
            ->join('anamneses', 'anamneses.id_customer = customers.id', 'left')
            ->where('customers.id', $id)
            ->groupBy('customers.id');

        $customer = $this->first();

        // Verifica se o customer foi encontrado
        if (!$customer) {
            throw new \RuntimeException('Customer não encontrado ou você não tem permissão para visualizá-lo.');
        }

        // Buscar detalhes das anamneses associadas ao customer
        $modelAnamnese = new AnamnesesModel();
        $anamneses = $modelAnamnese
            ->where('id_customer', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $modelTimeLine = new TimeLinesModel();
        $timeline = $modelTimeLine
            ->where('idCustomer', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $modelApp = new AppointmentsModel();
        $appointments = $modelApp
            ->where('id_customer', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Retornar os dados do customer com as anamneses
        return [
            'id' => $customer['id'],
            'name' => $customer['name'],
            'photo' => $customer['photo'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'doc' => $customer['doc'],
            'generous' => $customer['generous'],
            'birthDate' => $customer['birthDate'],
            'anamneses_count' => $customer['anamneses_count'],
            'appointments' => $appointments,
            'anamneses' => $anamneses, // Lista de anamneses associadas
            'timelines' => $timeline
        ];
    }


    public function deleteCustomer(int $id): void
    {
        // Obter o usuário atual
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        // Verificar se o usuário está autenticado
        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        $currentUserId = $currentUser['id'];

        // Verificar se o customer pertence ao usuário atual
        $customer = $this->where('id', $id)
            ->where('idUser', $currentUserId)
            ->first();

        // Verifica se o customer foi encontrado
        if (!$customer) {
            throw new \RuntimeException('Customer não encontrado ou você não tem permissão para excluí-lo.');
        }

        // Exclui o registro do customer
        if (!$this->delete($id)) {
            // Captura erros da instância correta do Model, se houver
            $errors = $this->errors();
            throw new \RuntimeException('Erro ao excluir o cliente: ' . implode(', ', $errors));
        }
    }
}
