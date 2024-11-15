<?php 
declare(strict_types=1);
namespace App\Models\Tasks\V1;

use App\Models\TasksModel;

class DeleteTasks extends TasksModel{


    public function del(int $id){

        $currentUser = $this->getAuthenticatedUser();

        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('idUser', $currentUser['id']);
        }

        $num = $this->where('id', $id)->countAllResults();

        if ($num) {
            if (!$this->delete($id)) {
                // Captura erros da instância correta do Model, se houver
                $errors = $this->errors();
                throw new \RuntimeException('Error deleting client: ' . implode(', ', $errors));
            }
        } else {
            throw new \RuntimeException('Schedule not found or you do not have permission to delete...');
        }
    }
}