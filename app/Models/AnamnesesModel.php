<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class AnamnesesModel extends Model
{
    protected $table            = 'anamneses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_user',
        'id_customer',
        'id_appointments',
        'slug',
        'mental_imbalance',
        'mental_percentage',
        'emotional_imbalance',
        'emotional_percentage',
        'spiritual_imbalance',
        'spiritual_percentage',
        'physical_imbalance',
        'physical_percentage',
        'coronary_chakra_imbalance',
        'coronary_chakra_percentage',
        'coronary_chakra_activity',
        'coronary_chakra_affects_organ',
        'frontal_chakra_imbalance',
        'frontal_chakra_percentage',
        'frontal_chakra_activity',
        'frontal_chakra_affects_organ',
        'laryngeal_chakra_imbalance',
        'laryngeal_chakra_percentage',
        'laryngeal_chakra_activity',
        'laryngeal_chakra_affects_organ',
        'cardiac_chakra_imbalance',
        'cardiac_chakra_percentage',
        'cardiac_chakra_activity',
        'cardiac_chakra_affects_organ',
        'solar_plexus_chakra_imbalance',
        'solar_plexus_chakra_percentage',
        'solar_plexus_chakra_activity',
        'solar_plexus_chakra_affects_organ',
        'sacral_chakra_imbalance',
        'sacral_chakra_percentage',
        'sacral_chakra_activity',
        'sacral_chakra_affects_organ',
        'base_chakra_imbalance',
        'base_chakra_percentage',
        'base_chakra_activity',
        'base_chakra_affects_organ',
        'aura_size',
        'aura_size_comments',
        'opening_size',
        'opening_size_comments',
        'color_lack',
        'color_excess',
        'color_base',
        'health_energy',
        'energy_comments',
        'family_area',
        'affective_area',
        'professional_area',
        'financial_area',
        'mission_area',
        'aura_protection'
    ];

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

    public function search(array $params): array
    {
        $response = [];

        $currentUser = $this->getAuthenticatedUser();
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        $currentUserId = $currentUser->id;

        // Parâmetros de entrada
        $searchTerm   = $params['s'] ?? false;
        $currentPage  = (isset($params['page']) && intval($params['page']) > 0) ? intval($params['page']) : 1;
        $sortBy       = $params['sort_by'] ?? 'id';
        $sortOrder    = strtoupper($params['order'] ?? 'ASC');
        $itemsPerPage = $this->validateItemsPerPage($params['limite'] ?? null);


        // Construir a query principal
        $this->select('customers.name, customers.photo, customers.email, customers.phone, customers.doc, customers.generous, customers.birthDate, customers.type')
            ->select('anamneses.*')
            ->join('customers', 'anamneses.id_customer = customers.id', 'left')
            ->where('customers.idUser', $currentUserId)
            ->groupBy('anamneses.id')
            ->orderBy('anamneses.' . $sortBy, $sortOrder);

        // Aplicar filtro de busca se o termo for fornecido
        if ($searchTerm) {
            $this->groupStart()
                ->like('customers.name', $searchTerm)
                ->orLike('customers.email', $searchTerm)
                ->orLike('anamneses.id', $searchTerm)
                ->orLike('anamneses.created_at', $searchTerm)
                ->orLike('anamneses.updated_at', $searchTerm)
                ->groupEnd();
        }

        // Contar resultados totais para a paginação
        $totalItems = $this->countAllResults(false); // 'false' mantém a query para a paginação

        // Paginação dos resultados
        $anamneses = $this->paginate($itemsPerPage, '', $currentPage);

        // Preparar mensagem de contagem de resultados
        /*$itemsOnPage = count($anamneses);
        if ($searchTerm) {
            $resultMessage = $itemsOnPage === 1 ? "1 resultado encontrado." : "{$itemsOnPage} resultados encontrados.";
        } else {
            $startItem = ($currentPage - 1) * $itemsPerPage + 1;
            $endItem = min($currentPage * $itemsPerPage, $totalItems);
            $resultMessage = "Exibindo resultados {$startItem} a {$endItem} de {$totalItems}.";
        }*/

        // Calcular links de navegação para paginação
        $totalPages = ceil($totalItems / $itemsPerPage);
        $prevPage = ($currentPage > 1) ? $currentPage - 1 : null;
        $nextPage = ($currentPage < $totalPages) ? $currentPage + 1 : null;

        // Montar o array de dados a ser retornado
        $response = [
            'rows'  => $anamneses, // Resultados paginados com contagem de anamneses
            'pagination' => [
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
                'items_per_page' => $itemsPerPage,
                'prev_page' => $prevPage,
                'next_page' => $nextPage,
            ],
            //'num'   => $resultMessage
        ];

        return $response;
    }

    private function validateItemsPerPage($value): int
    {
        // Verifica se o valor está definido, se é numérico, e tenta converter para inteiro
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;

        // Se a conversão falhar, retorna 15
        if (!$itemsPerPage) {
            $itemsPerPage = 15;
        }

        // Verifica o limite máximo de 200
        if ($itemsPerPage > 500) {
            $itemsPerPage = 500;
        }

        return $itemsPerPage;
    }

    public function createAnamnese(array $params): array
    {
        // Obter o usuário atual
        $userCutomer = new CustomersModel();
        $userAppointment = new AppointmentsModel();

        $currentUser = $this->getAuthenticatedUser();
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        $currentUserId = $currentUser->id;

        // Verifica se o customer pertence ao usuário atual
        $customer = $userCutomer->where('id', $params['idCustomer'])->where('idUser', $currentUserId)->first();

        if (!$customer) {
            throw new \RuntimeException('Customer não encontrado ou você não tem permissão para criar a anamnese.');
        }

        //Verfifica se existe o agendamento e se é do usuário
        $appointment = $userAppointment->where('id', $params['idAppointment'])->where('id_user', $currentUserId)->first();

        if (!$appointment) {
            throw new \RuntimeException('Agendamento não encontrado ou você não tem permissão para criar a anamnese.');
        }

        //return $customer;

        helper('auxiliar');
        $slug = generateSlug();

        // Prepara os dados para inserção
        $data = [
            'id_user' => intval($currentUserId),
            'id_customer' => $params['idCustomer'],
            'id_appointments' => $params['idAppointment'],
            'slug' => $slug,
            'mental_imbalance' => $params['mentalDesequilibrio'] ?? null,
            'mental_percentage' => $params['mentalPercentual'] ?? 0,
            'emotional_imbalance' => $params['emocionalDesequilibrio'] ?? null,
            'emotional_percentage' => $params['emocionalPercentual'] ?? 0,
            'spiritual_imbalance' => $params['espiritualDesequilibrio'] ?? null,
            'spiritual_percentage' => $params['espiritualPercentual'] ?? 0,
            'physical_imbalance' => $params['fisicoDesequilibrio'] ?? null,
            'physical_percentage' => $params['fisicoPercentual'] ?? 0,

            'coronary_chakra_imbalance' => $params['chakraCoronarioDesequilibrio'] ?? null,
            'coronary_chakra_percentage' => $params['chakraCoronarioPercentual'] ?? 0,
            'coronary_chakra_activity' => $params['chakraCoronarioAtividade'] ?? null,
            'coronary_chakra_affects_organ' => $params['chakraCoronarioOrgao'] ?? null,
            'frontal_chakra_imbalance' => $params['chakraFrontalDesequilibrio'] ?? null,
            'frontal_chakra_percentage' => $params['chakraFrontalPercentual'] ?? 0,
            'frontal_chakra_activity' => $params['chakraFrontalAtividade'] ?? null,
            'frontal_chakra_affects_organ' => $params['chakraFrontalOrgao'] ?? null,
            'laryngeal_chakra_imbalance' => $params['chakraLaringeoDesequilibrio'] ?? null,
            'laryngeal_chakra_percentage' => $params['chakraLaringeoPercentual'] ?? 0,
            'laryngeal_chakra_activity' => $params['chakraLaringeoAtividade'] ?? null,
            'laryngeal_chakra_affects_organ' => $params['chakraLaringeoOrgao'] ?? null,
            'cardiac_chakra_imbalance' => $params['chakraCardiacoDesequilibrio'] ?? null,
            'cardiac_chakra_percentage' => $params['chakraCardiacoPercentual'] ?? 0,
            'cardiac_chakra_activity' => $params['chakraCardiacoAtividade'] ?? null,
            'cardiac_chakra_affects_organ' => $params['chakraCardiacoOrgao'] ?? null,
            'solar_plexus_chakra_imbalance' => $params['chakraPlexoSolarDesequilibrio'] ?? null,
            'solar_plexus_chakra_percentage' => $params['chakraPlexoSolarPercentual'] ?? 0,
            'solar_plexus_chakra_activity' => $params['chakraPlexoSolarAtividade'] ?? null,
            'solar_plexus_chakra_affects_organ' => $params['chakraPlexoSolarOrgao'] ?? null,
            'sacral_chakra_imbalance' => $params['chakraSacroDesequilibrio'] ?? null,
            'sacral_chakra_percentage' => $params['chakraSacroPercentual'] ?? 0,
            'sacral_chakra_activity' => $params['chakraSacroAtividade'] ?? null,
            'sacral_chakra_affects_organ' => $params['chakraSacroOrgao'] ?? null,
            'base_chakra_imbalance' => $params['chakraBasicoDesequilibrio'] ?? null,
            'base_chakra_percentage' => $params['chakraBasicoPercentual'] ?? 0,
            'base_chakra_activity' => $params['chakraBasicoAtividade'] ?? null,
            'base_chakra_affects_organ' => $params['chakraBasicoOrgao'] ?? null,
            'aura_size' => $params['tamanhoAura'] ?? 0,
            'aura_protection' => $params['auraProtection'] ?? 0,
            'aura_size_comments' => $params['tamanhoAuraComments'] ?? '',
            'opening_size' => $params['tamanhoAbertura'] ?? 0,
            'opening_size_comments' => $params['tamanhoAberturaComments'] ?? '',
            'color_lack' => implode(', ', $params['corFalta']) ?? null,
            'color_excess' => implode(', ', $params['corExcesso']) ?? null,
            'color_base' => implode(', ', $params['corBase']) ?? null,
            'health_energy' => $params['energia'] ?? null,
            'energy_comments' => $params['energiaComments'] ?? '',
            'family_area' => $params['areasFamiliar'] ?? 0,
            'affective_area' => $params['areasAfetivo'] ?? 0,
            'professional_area' => $params['areasProfissional'] ?? 0,
            'financial_area' => $params['areasFinanceiro'] ?? 0,
            'mission_area' => $params['areasMissao'] ?? 0
        ];

        // Aqui você pode inserir os dados no banco de dados
        $id =  $this->insert($data);

        $modelTime = new TimeLinesModel();

        $modelTime->insert(
            [
                'idUser'     => intval($currentUserId),
                'idCustomer' => $params['idCustomer'],
                'url'        => base_url("anamnese/{$slug}"),
                'type'       => 'create_anamnese'
            ]
        );

        return ['id' => $id, 'url' => site_url("anamnese/{$slug}")];
    }

    public function updateAnamnese(array $params, int $id): array
    {
        // Obter o usuário atual
        $userModel = new UsersModel();
        $currentUser = $userModel->me();

        if (!isset($currentUser['id'])) {
            throw new \RuntimeException('Usuário não autenticado.');
        }

        $currentUserId = $currentUser['id'];

        // Verificar se a anamnese pertence ao usuário atual
        $anamneseExists = $this->where([
            'id' => $id,
            'id_user' => $currentUserId
        ])->countAllResults() > 0;

        if (!$anamneseExists) {
            throw new \RuntimeException('Anamnese não encontrada ou você não tem permissão para editá-la.');
        }

        $data = [
            'id' => $id, // Certifique-se de que este ID é o identificador correto da tabela
            'mental_imbalance' => $params['mentalDesequilibrio'],
            'mental_percentage' => $params['mentalPercentual'],
            'emotional_imbalance' => $params['emocionalDesequilibrio'],
            'emotional_percentage' => $params['emocionalPercentual'],
            'spiritual_imbalance' => $params['espiritualDesequilibrio'],
            'spiritual_percentage' => $params['espiritualPercentual'],
            'physical_imbalance' => $params['fisicoDesequilibrio'],
            'physical_percentage' => $params['fisicoPercentual'],
            'coronary_chakra_imbalance' => $params['chakraCoronarioDesequilibrio'],
            'coronary_chakra_percentage' => $params['chakraCoronarioPercentual'],
            'coronary_chakra_activity' => $params['chakraCoronarioAtividade'],
            'coronary_chakra_affects_organ' => $params['chakraCoronarioOrgao'],
            'frontal_chakra_imbalance' => $params['chakraFrontalDesequilibrio'],
            'frontal_chakra_percentage' => $params['chakraFrontalPercentual'],
            'frontal_chakra_activity' => $params['chakraFrontalAtividade'],
            'frontal_chakra_affects_organ' => $params['chakraFrontalOrgao'],
            'laryngeal_chakra_imbalance' => $params['chakraLaringeoDesequilibrio'],
            'laryngeal_chakra_percentage' => $params['chakraLaringeoPercentual'],
            'laryngeal_chakra_activity' => $params['chakraLaringeoAtividade'],
            'laryngeal_chakra_affects_organ' => $params['chakraLaringeoOrgao'],
            'cardiac_chakra_imbalance' => $params['chakraCardiacoDesequilibrio'],
            'cardiac_chakra_percentage' => $params['chakraCardiacoPercentual'],
            'cardiac_chakra_activity' => $params['chakraCardiacoAtividade'],
            'cardiac_chakra_affects_organ' => $params['chakraCardiacoOrgao'],
            'solar_plexus_chakra_imbalance' => $params['chakraPlexoSolarDesequilibrio'],
            'solar_plexus_chakra_percentage' => $params['chakraPlexoSolarPercentual'],
            'solar_plexus_chakra_activity' => $params['chakraPlexoSolarAtividade'],
            'solar_plexus_chakra_affects_organ' => $params['chakraPlexoSolarOrgao'],
            'sacral_chakra_imbalance' => $params['chakraSacroDesequilibrio'],
            'sacral_chakra_percentage' => $params['chakraSacroPercentual'],
            'sacral_chakra_activity' => $params['chakraSacroAtividade'],
            'sacral_chakra_affects_organ' => $params['chakraSacroOrgao'],
            'base_chakra_imbalance' => $params['chakraBasicoDesequilibrio'],
            'base_chakra_percentage' => $params['chakraBasicoPercentual'],
            'base_chakra_activity' => $params['chakraBasicoAtividade'],
            'base_chakra_affects_organ' => $params['chakraBasicoOrgao'],
            'aura_size' => $params['tamanhoAura'] ?? 0,
            'aura_protection' => $params['auraProtection'] ?? 0,
            'aura_size_comments' => $params['tamanhoAuraComments'] ?? '',
            'opening_size' => $params['tamanhoAbertura'] ?? 0,
            'opening_size_comments' => $params['tamanhoAberturaComments'] ?? '',
            'color_lack' => implode(', ', $params['corFalta']),
            'color_excess' => implode(', ', $params['corExcesso']) ?? '',
            'color_base' => implode(', ', $params['corBase']) ?? null,
            'health_energy' => $params['energia'],
            'energy_comments' => $params['energiaComments'] ?? '',
            'family_area' => $params['areasFamiliar'] ?? 0,
            'affective_area' => $params['areasAfetivo'] ?? 0,
            'professional_area' => $params['areasProfissional'] ?? 0,
            'financial_area' => $params['areasFinanceiro'] ?? 0,
            'mission_area' => $params['areasMissao'] ?? 0
        ];

        // Tentar salvar os dados e verificar se houve erros
        if (!$this->save($data)) {
            throw new \RuntimeException('Erro ao atualizar a anamnese: ' . implode(', ', $this->errors()));
        }

        return ['message' => 'Anamnese atualizada com sucesso.'];
    }

    public function showAnamnese(int $id): array
    {
        // Obter o usuário atual
        $currentUser = $this->getAuthenticatedUser();
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        $currentUserId = $currentUser->id;

        // Realizar a busca do customer com JOIN para incluir anamneses
        $this->select('customers.name, customers.photo, customers.email, customers.phone, customers.doc, customers.generous, customers.birthDate')
            ->select('anamneses.*')
            ->join('customers', 'anamneses.id_customer = customers.id', 'left')
            ->where('anamneses.id', $id)
            ->where('customers.idUser', $currentUserId)
            ->groupBy('anamneses.id');

        $anamnese = $this->first();

        // Verifica se o customer foi encontrado
        if (!$anamnese) {
            throw new Exception('Customer não encontrado ou você não tem permissão para visualizá-lo.');
        }

        // Retornar os dados do customer com as anamneses
        /**$data = [
            'id'        => $anamnese['id'],
            'name'      => $anamnese['name'],
            'photo'     => $anamnese['photo'],
            'email'     => $anamnese['email'],
            'phone'     => $anamnese['phone'],
            'doc'       => $anamnese['doc'],
            'generous'  => $anamnese['generous'],
            'birthDate' => $anamnese['birthDate'],
            'anamnese'  => $anamnese
        ];*/

        $data = [
            $anamnese
        ];

        return $data;
    }

    public function deleteAnamnese(int $id): void
    {
        // Obter o usuário atual
        $currentUser = $this->getAuthenticatedUser();
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        $currentUserId = $currentUser->id;

        // Verificar se o customer pertence ao usuário atual
        $anamneses = $this->where('id', $id)
            ->where('id_user', $currentUserId)
            ->first();
        // Verifica se o customer foi encontrado
        if (!$anamneses) {
            throw new \RuntimeException('Anamnese não encontrada ou você não tem permissão para excluí-lo.');
        }

        // Exclui o registro do customer
        if (!$this->delete($id)) {
            // Captura erros da instância correta do Model, se houver
            $errors = $this->errors();
            throw new Exception('Erro ao excluir o cliente: ' . implode(', ', $errors));
        }
    }


    protected function getAuthenticatedUser()
    {
        $userModel = new UsersModel();
        $currentUser = $userModel->decodeDataTokenUser()->data;
        if (!isset($currentUser->id)) {
            throw new \RuntimeException('Usuário não autenticado.');
        }
        return $currentUser;
    }
}
