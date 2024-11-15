<?php

namespace App\Models\Appointments\V1;

use App\Models\AppointmentsModel;



class DeleteAppointments extends AppointmentsModel
{

    public function del($id)
    {
        $currentUser = $this->getAuthenticatedUser();
        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('id_user', $currentUser['id']);
        }
        $num = $this->where('id', $id)->countAllResults();

        if ($num) {
            $appUpdate = new UpdateAppointments();
            $appUpdate->updateDelete($id, $currentUser['id']);
            
            if (!$this->delete($id)) {
                // Captura erros da instância correta do Model, se houver
                $errors = $this->errors();
                throw new \RuntimeException('Erro ao excluir o cliente: ' . implode(', ', $errors));
            }
            
        } else {
            throw new \RuntimeException('Agendamento não encontrado ou você não tem permissão para excluir...');
        }
    }
}
