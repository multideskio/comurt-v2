<?php

declare(strict_types=1);

namespace App\Models\Tasks\V1;

use App\Models\TasksModel;

class SearchTasks extends TasksModel
{


    public function listTasks(array $params): array
    {
        $currentUser = $this->getAuthenticatedUser();

        // Extrai e valida parâmetros
        $searchTerm   = $params['s'] ?? null; // Termo de busca opcional
        $currentPage  = $this->validatePageNumber($params['page'] ?? 1); // Número da página atual
        $sortBy       = $this->validateSortBy($params['sort_by'] ?? 'id'); // Campo para ordenação
        $sortOrder    = $this->validateSortOrder($params['order'] ?? 'ASC'); // Ordem de ordenação
        $status       = $this->validateStatus($params['status'] ?? null); // Status do compromisso
        $itemsPerPage = $this->validateItemsPerPage($params['limite'] ?? null); // Itens por página

        // Constrói a consulta dos compromissos
        $this->buildTasksQuery($currentUser, $searchTerm, $sortBy, $sortOrder, $status);

        return $this->paginateResults($itemsPerPage, $currentPage, $params);

    }


    private function paginateResults(?int $itemsPerPage, ?int $currentPage, array $params): array{

        $totalItems = $this->countAllResults(false); // Conta o total de itens sem resetar a consulta
        $data = $this->paginate($itemsPerPage, '', $currentPage); // Pagina os resultados
        
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

    private function buildTasksQuery($currentUser, $searchTerm, $sortBy, $sortOrder, $status){
        
        $this->orderBy($sortBy, $sortOrder);
        $this->select('id, title, description, order, status, datetime, order');

        // Filtra compromissos por usuário, se não for SUPERADMIN
        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('idUser', $currentUser['id']);
        }

        if ($status){
            $this->where('status', $status);
        }

        // Adiciona termos de busca
        if ($searchTerm) {
            $this->groupStart()
                ->like('title', $searchTerm)
                ->orLike('description', $searchTerm)
                ->orLike('datetime', $searchTerm)
                ->groupEnd();
            }
    }


    private function validateItemsPerPage($value)
    {
        $itemsPerPage = (isset($value) && is_numeric($value)) ? intval($value) : 15;
        return min(max($itemsPerPage, 1), 500);
    }


    private function validateStatus($status)
    {
        $allowedStatuses = ['pending', 'completed'];
        return in_array($status, $allowedStatuses) ? $status : null;
    }

    private function validateSortOrder($order): string
    {
        return strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
    }

    private function validateSortBy($sortBy): string
    {
        $allowedSortFields = ['id', 'order', 'title'];
        return in_array($sortBy, $allowedSortFields) ? $sortBy : 'order';
    }

    private function validatePageNumber($page): int
    {
        return (intval($page) > 0) ? intval($page) : 1;
    }
}
