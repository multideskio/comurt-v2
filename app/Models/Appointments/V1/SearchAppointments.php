<?php

namespace App\Models\Appointments\V1;

use App\Models\AnamnesesModel;
use App\Models\AppointmentsModel;
use App\Models\UsersModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Classe SearchAppointments
 *
 * Extende AppointmentsModel para listar compromissos com base nos parâmetros fornecidos.
 */
class SearchAppointments extends AppointmentsModel
{

    public function listAppointments(array $params): array
    {
        $currentUser = $this->getAuthenticatedUser();

        // Extrai e valida parâmetros
        $searchTerm   = $params['s'] ?? null; // Termo de busca opcional
        $currentPage  = $this->validatePageNumber($params['page'] ?? 1); // Número da página atual
        $sortBy       = $this->validateSortBy($params['sort_by'] ?? 'id'); // Campo para ordenação
        $sortOrder    = $this->validateSortOrder($params['order'] ?? 'ASC'); // Ordem de ordenação
        $status       = $this->validateStatus($params['status'] ?? null); // Status do compromisso
        $itemsPerPage = $this->validateItemsPerPage($params['limit'] ?? null); // Itens por página
        $dateRange    = $this->getDateRange($params); // Intervalo de datas para o filtro

        // Valida e captura o ID do cliente
        $idCustomer   = $this->validateIdCustomer($params['id_customer'] ?? null);

        // Valida e captura o tipo de cliente
        $typeCustomer = $this->validateTypeCustomer($params['type'] ?? null);

        // Constrói a consulta dos compromissos
        $this->buildAppointmentQuery($currentUser, $searchTerm, $sortBy, $sortOrder, $dateRange, $status, $idCustomer, $typeCustomer);

        // Pagina os resultados e formata a resposta
        return $this->paginateResults($itemsPerPage, $currentPage, $params, $dateRange);
    }

    /**
     * Valida o número da página.
     *
     * @param mixed $page Número da página.
     * @return int Número da página validado, com um valor mínimo de 1.
     */
    private function validatePageNumber($page): int
    {
        return (intval($page) > 0) ? intval($page) : 1;
    }

    /**
     * Valida o ID do cliente.
     *
     * @param mixed $id ID do cliente.
     * @return int|null Retorna o ID do cliente validado ou null se inválido.
     */
    private function validateIdCustomer($id)
    {
        return (intval($id) > 0) ? intval($id) : null;
    }

    /**
     * Valida o campo de ordenação.
     *
     * @param mixed $sortBy Campo de ordenação.
     * @return string Campo de ordenação validado.
     */
    private function validateSortBy($sortBy): string
    {
        $allowedSortFields = ['id', 'date', 'name', 'status'];
        return in_array($sortBy, $allowedSortFields) ? $sortBy : 'id';
    }

    /**
     * Valida o tipo de cliente.
     *
     * @param mixed $typeCustomer Tipo de cliente.
     * @return string|null Tipo de cliente validado ou null se inválido.
     */
    private function validateTypeCustomer($typeCustomer)
    {
        // 1 - 2 - 3 - 4
        $allowedTypes = ["myself", "family", "friend", "professional"];
        return in_array($typeCustomer, $allowedTypes) ? $typeCustomer : null;
    }

    /**
     * Valida a ordem de ordenação.
     *
     * @param mixed $order Ordem de ordenação.
     * @return string Ordem de ordenação validada (ASC ou DESC).
     */
    private function validateSortOrder($order): string
    {
        return strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    }

    /**
     * Valida o status do compromisso.
     *
     * @param mixed $status Status do compromisso.
     * @return string|null Status validado ou null se inválido.
     */
    private function validateStatus($status)
    {
        $allowedStatuses = ['pending', 'completed', 'cancelled'];
        return in_array($status, $allowedStatuses) ? $status : null;
    }

    /**
     * Obtém o intervalo de datas com base nos parâmetros fornecidos.
     *
     * @param array $params Parâmetros de data.
     * @return array Intervalo de datas com 'start' e 'end'.
     */
    private function getDateRange(array $params): array
    {
        $startDate = $this->validateDate($params['start'] ?? null) ?? date('Y-m-d') . ' 00:00:00';
        $endDate   = $this->validateDate($params['end'] ?? null) ?? date('Y-m-d', strtotime('+1 month', strtotime($startDate))) . ' 23:59:59';

        // Limita o intervalo máximo para um mês
        $maxEndDate = date('Y-m-d', strtotime('+1 month', strtotime($startDate))) . ' 23:59:59';
        if ($endDate > $maxEndDate) {
            $endDate = $maxEndDate;
        }

        //return ['start' => $startDate, 'end' => $endDate];
        return ['start' => $params['start'] ?? null, 'end' => $params['end'] ?? null];
    }

    /**
     * Constrói a consulta para listar compromissos.
     *
     * @param array $currentUser Usuário atual autenticado.
     *        Inclui informações sobre o usuário logado.
     * @param string|null $searchTerm Termo de busca opcional.
     *        Usado para pesquisar compromissos por nome, ID, e-mail ou telefone.
     * @param string $sortBy Campo de ordenação.
     *        Campo usado para ordenar os resultados (ex: 'id', 'date').
     * @param string $sortOrder Ordem de ordenação.
     *        Direção da ordenação ('ASC' ou 'DESC').
     * @param array $dateRange Intervalo de datas.
     *        Inclui as chaves 'start' e 'end' para o filtro de datas.
     * @param string|null $status Status do compromisso.
     *        Filtra compromissos pelo status especificado (ex: 'pending').
     * @param int|null $idCustomer ID do cliente.
     *        Filtra compromissos pelo ID do cliente, se especificado.
     * @param string|null $typeCustomer Tipo de cliente.
     *        Filtra compromissos pelo tipo de cliente (ex: 'myself', 'family').
     */
    private function buildAppointmentQuery($currentUser, $searchTerm, $sortBy, $sortOrder, $dateRange, $status, $idCustomer, $typeCustomer): void
    {

        // Filtra compromissos por usuário, se não for SUPERADMIN
        
        $this->where('appointments.id_user', $currentUser['id']);
        

        $this->select("appointments.id As id_appointment, appointments.observation, appointments.date As date_appointment, appointments.status As status_appointment, appointments.type As type_appointment")
            ->select("customers.id As id_customer, customers.name As name_customer, customers.type As type_customer, customers.email AS email_customer")
            ->select("users.id As id_user, users.name As name_user")
            ->join("users", "appointments.id_user = users.id")
            ->join("customers", "appointments.id_customer = customers.id", "left")
            ->orderBy('appointments.' . $sortBy, $sortOrder);

        if ($dateRange['start']) {
            $this->where('date >=', $dateRange['start']);
        }

        if ($dateRange['end']) {
            $this->where('date <=', $dateRange['end']);
        }

        // Filtra por status, se especificado
        if ($status) {
            $this->where('appointments.status', $status);
        }

        // Filtra pelo id de cliente
        if ($idCustomer) {
            $this->where('customers.id', $idCustomer);
        }

        // Filtra pelo tipo de cliente
        if ($typeCustomer) {
            $this->where('customers.type', $typeCustomer);
        }

        // Adiciona termos de busca
        if ($searchTerm) {
            $this->groupStart()
                ->like('customers.name', $searchTerm)
                ->orLike('appointments.id', $searchTerm)
                ->orLike('customers.email', $searchTerm)
                ->orLike('customers.phone', $searchTerm)
                ->groupEnd();
        }
    }

    /**
     * Pagina os resultados dos compromissos.
     *
     * @param int $itemsPerPage Número de itens por página.
     * @param int $currentPage Página atual.
     * @param array $params Parâmetros adicionais para a paginação.
     * @param array $dateRange Intervalo de datas para filtro.
     * @return array Resultados paginados com informações de paginação.
     */
    private function paginateResults($itemsPerPage, $currentPage, array $params, array $dateRange): array
    {
        $totalItems = $this->countAllResults(false); // Conta o total de itens sem resetar a consulta
        $data = $this->paginate($itemsPerPage, '', $currentPage); // Pagina os resultados

        return [
            'rows'  => $data,
            'params' => $params,
            'dateRange' => $dateRange,
            'pagination' => [
                'current_page'   => $currentPage,
                'total_pages'    => ceil($totalItems / $itemsPerPage),
                'total_items'    => $totalItems,
                'items_per_page' => $itemsPerPage,
                'prev_page'      => ($currentPage > 1) ? $currentPage - 1 : null,
                'next_page'      => ($currentPage < ceil($totalItems / $itemsPerPage)) ? $currentPage + 1 : null,
            ],
        ];
    }

    /**
     * Valida o número de itens por página.
     *
     * @param mixed $value Número de itens por página.
     * @return int Número validado de itens por página, limitado entre 1 e 500.
     */
    private function validateItemsPerPage($value)
    {
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;
        return min(max($itemsPerPage, 1), 500);
    }

    /**
     * Valida a data no formato 'Y-m-d'.
     *
     * @param string|null $date Data a ser validada.
     * @return string|null Data validada no formato 'Y-m-d H:i:s' ou null se inválida.
     */
    private function validateDate(?string $date): ?string
    {
        if ($date) {
            $dateTime = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateTime && $dateTime->format('Y-m-d') === $date) {
                return $dateTime->format('Y-m-d') . ' 00:00:00';
            }
        }
        return null;
    }

    public function statistics($userId): array
    {
        $data = [];
        // Acessa a role diretamente de $decoded
        $role = $decoded->role ?? lang('Config.roleNotSpecified');
        $modelAnamneses = new AnamnesesModel();
        $data['appointments'] = $this->where('id_user', $userId)->countAllResults();
        $data['anamneses'] = $modelAnamneses->where('id_user', $userId)->countAllResults();
        $data['cancelled'] = $this->where('id_user', $userId)->where('status', 'cancelled')->countAllResults();
        return $data;
    }

    public function statisticsWithComparison($userId, $startDate = null, $endDate = null): array
    {
        // Se não forem fornecidas as datas, usar os últimos 7 dias como padrão
        $hoje = date('Y-m-d');
        $umaSemanaAtras = date('Y-m-d', strtotime('-7 days'));

        // Se o usuário não fornecer as datas, usar o padrão de últimos 7 dias
        $startDate = $startDate ?? $umaSemanaAtras;
        $endDate = $endDate ?? $hoje;

        // Para comparação, pegar o mesmo intervalo de tempo anterior
        $periodoAtualDias = (new \DateTime($endDate))->diff(new \DateTime($startDate))->days;
        $previousStartDate = date('Y-m-d', strtotime("-{$periodoAtualDias} days", strtotime($startDate)));
        $previousEndDate = date('Y-m-d', strtotime("-{$periodoAtualDias} days", strtotime($endDate)));

        // Modelo de anamneses
        $modelAnamneses = new AnamnesesModel();

        // Consultas de "appointments" em uma única query
        $appointments = $this->select('
        COUNT(*) AS total_appointments,
        SUM(CASE WHEN created_at >= "' . $startDate . '" AND created_at <= "' . $endDate . '" THEN 1 ELSE 0 END) AS current_period_appointments,
        SUM(CASE WHEN created_at >= "' . $previousStartDate . '" AND created_at <= "' . $previousEndDate . '" THEN 1 ELSE 0 END) AS previous_period_appointments,
        SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) AS total_cancelled_appointments,
        SUM(CASE WHEN created_at >= "' . $startDate . '" AND created_at <= "' . $endDate . '" AND status = "cancelled" THEN 1 ELSE 0 END) AS current_period_cancelled_appointments,
        SUM(CASE WHEN created_at >= "' . $previousStartDate . '" AND created_at <= "' . $previousEndDate . '" AND status = "cancelled" THEN 1 ELSE 0 END) AS previous_period_cancelled_appointments
    ')
            ->where('id_user', $userId)
            ->get()
            ->getRowArray();

        // Consultas de "anamneses" em uma única query
        $anamneses = $modelAnamneses->select('
        COUNT(*) AS total_anamneses,
        SUM(CASE WHEN created_at >= "' . $startDate . '" AND created_at <= "' . $endDate . '" THEN 1 ELSE 0 END) AS current_period_anamneses,
        SUM(CASE WHEN created_at >= "' . $previousStartDate . '" AND created_at <= "' . $previousEndDate . '" THEN 1 ELSE 0 END) AS previous_period_anamneses
    ')
            ->where('id_user', $userId)
            ->get()
            ->getRowArray();

        // Calcular a diferença percentual de appointments e cancelled
        $appointmentsPeriodComparison = $this->calculatePercentageDifference(
            $appointments['previous_period_appointments'],
            $appointments['current_period_appointments']
        );

        $cancelledPeriodComparison = $this->calculatePercentageDifference(
            $appointments['previous_period_cancelled_appointments'],
            $appointments['current_period_cancelled_appointments']
        );

        // Calcular a diferença percentual de anamneses
        $anamnesesPeriodComparison = $this->calculatePercentageDifference(
            $anamneses['previous_period_anamneses'],
            $anamneses['current_period_anamneses']
        );

        // Dados finais a serem retornados
        return [
            'appointments' => $appointments['total_appointments'],
            'anamneses' => $anamneses['total_anamneses'],
            'cancelled' => $appointments['total_cancelled_appointments'],
            'appointments_period_comparison' => $appointmentsPeriodComparison,
            'anamneses_period_comparison' => $anamnesesPeriodComparison,
            'cancelled_period_comparison' => $cancelledPeriodComparison
        ];
    }

    /**
     * Método para calcular a diferença percentual
     * Agora, permite extrapolar 100% para métricas mais precisas.
     */
    private function calculatePercentageDifference($lastPeriod, $currentPeriod): float
    {
        // Se o período anterior não tiver dados, retorna o crescimento completo
        if ($lastPeriod == 0) {
            return $currentPeriod > 0 ? ($currentPeriod * 100) : 0;
        }

        // Calcula a diferença percentual normalmente
        return (($currentPeriod - $lastPeriod) / $lastPeriod) * 100;
    }
}
