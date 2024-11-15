<?php
declare(strict_types=1);

namespace App\Models\Customers\V1;

use App\Models\CustomersModel;
use App\Models\UsersModel;

class SearchCustomer extends CustomersModel
{
    public function search(array $params): array
    {
        $response = [];
        $currentUser = $this->getAuthenticatedUser();

        // Parâmetros de entrada
        $searchTerm   = $params['s'] ?? false;
        $currentPage  = $this->validatePageNumber($params['page'] ?? 1);
        $sortBy       = $this->validateSortBy($params['sort_by'] ?? 'ASC');
        $sortOrder    = $this->validateSortOrder($params['order'] ?? 'id');
        $genero       = $this->validateGenerous($params['generous'] ?? null);
        $type         = $this->validateType($params['type'] ?? null);
        $itemsPerPage = $this->validateItemsPerPage($params['limite'] ?? null);
        $this->buildAppointmentQuery($currentUser, $sortBy, $sortOrder, $searchTerm, $genero, $type);
        $response = $this->paginateResults($itemsPerPage, $currentPage, esc($params));
        return $response;
    }

    private function getAuthenticatedUser(): array
    {
        $userModel = new UsersModel();
        $currentUser = $userModel->me();
        if (!isset($currentUser['id'])) {
            log_message('info', __LINE__ . ' Unauthenticated user.');
            throw new \RuntimeException('Unauthenticated user.');
        }
        return $currentUser;
    }

    private function validatePageNumber($page): int
    {
        return (intval($page) > 0) ? intval($page) : 1;
    }

    private function validateSortBy($sortBy): string
    {
        $allowedSortFields = ['id', 'name', 'email', 'phone', 'doc', 'type'];
        return in_array($sortBy, $allowedSortFields) ? $sortBy : 'id';
    }

    private function validateSortOrder($order): string
    {
        return strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    }

    private function validateGenerous($type){
        $allowedSortFields = ['male', 'female', 'unspecified', 'non-binary', 'gender fluid', 'agender', 'other'];
        return in_array($type, $allowedSortFields) ? $type : null;
    }

    private function validateType($type){
        $allowedSortFields = ["family", "friend", "professional"];
        return in_array($type, $allowedSortFields) ? $type : null;
    }

    private function buildAppointmentQuery($currentUser, $sortBy, $sortOrder, $searchTerm, $genero, $type): void
    {
        // Construir a query principal
        $this->select('customers.*, COUNT(anamneses.id) as anamneses_count')
            ->join('anamneses', 'anamneses.id_customer = customers.id', 'left')
            ->where('customers.idUser', $currentUser['id'])
            ->groupBy('customers.id')
            ->orderBy('customers.' . $sortBy, $sortOrder);
        
        if($genero){
            $this->where('generous', $genero);
        }

        if($type){
            $this->where('type', $type);
        }

        // Aplicar filtro de busca se o termo for fornecido
        //id, name, email, phone, doc, type
        if ($searchTerm) {
            $this->groupStart()
                ->like('customers.name', $searchTerm)
                ->orLike('customers.id', $searchTerm)
                ->orLike('customers.email', $searchTerm)
                ->orLike('customers.birthDate', $searchTerm)
                ->orLike('customers.phone', $searchTerm)
                ->orLike('customers.doc', $searchTerm)
                ->groupEnd();
        }

    }

    private function paginateResults($itemsPerPage, $currentPage, array $params): array
    {
        $totalItems = $this->countAllResults(false);
        $data = $this->paginate($itemsPerPage, '', $currentPage);

        return [
            'rows'  => $data,
            'params' => $params,
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

    private function validateItemsPerPage($value)
    {
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;
        return min(max($itemsPerPage, 1), 500);
    }
}
