<?php 
declare(strict_types=1);
namespace App\Models\Tasks\V1;

use App\Models\TasksModel;

class GetTasks extends TasksModel{

    public function getId(int $id): array
    {
        $currentUser = $this->getAuthenticatedUser();

        //SE FOR SUPER ADMIN MOSTRA TUDO
        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('idUser', $currentUser['id']);
        }

        $this->select('id, title, description, order, status, datetime');
        $this->where('id', $id);
        $data = $this->first();

        if(!$data){
            throw new \RuntimeException('Not found');
        }

        return $data;
    }
}