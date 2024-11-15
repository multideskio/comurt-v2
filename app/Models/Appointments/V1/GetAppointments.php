<?php

namespace App\Models\Appointments\V1;

use App\Models\AppointmentsModel;
use App\Models\UsersModel;

/**
 * Classe listAppointments
 *
 * Extende AppointmentsModel para listar compromissos com base nos parâmetros fornecidos.
 */
class  GetAppointments extends AppointmentsModel
{
    /**
     * Lista compromissos com base nos parâmetros fornecidos.
     *
     * @param array $params Parâmetros para filtrar, ordenar e paginar os compromissos.
     * @return array Resultados paginados dos compromissos.
     */
    public function id(int $id): array
    {
        $currentUser = $this->getAuthenticatedUser();

        // Filtra compromissos por usuário, se não for SUPERADMIN
        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('appointments.id_user', $currentUser['id']);
        }

        $this->select("appointments.id As id_appointment, appointments.observation, appointments.date As date, appointments.status As status")
            ->select("customers.id As id_customer, customers.name As name_customer, customers.type As type_customer, customers.email AS email_customer")
            ->select("users.id As id_user, users.name As name_user")
            ->join("users", "appointments.id_user = users.id")
            ->join("customers", "appointments.id_customer = customers.id", "left")
            ->where('appointments.id', $id)
            ->where('id_user', $currentUser['id']);

        $data = $this->first();

        // Se nenhum dado for encontrado, lança uma exceção com 404 Not Found
        if (!$data) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Agendamento não foi encontrado');
        }

        return $data;
    }
}
