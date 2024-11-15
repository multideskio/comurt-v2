<?php

namespace App\Models\Appointments\V1;

use App\Models\AppointmentsModel;

class UpdateAppointments extends AppointmentsModel
{
    public function updateRow($id, $params)
    {
        $currentUser = $this->getAuthenticatedUser();

        if ($currentUser['role'] !== 'SUPERADMIN') {
            $this->where('id_user', $currentUser['id']);
        }

        $count = $this->where('id', $id)->countAllResults();

        if (!$count) {
            throw new \RuntimeException("Schedule not found or you don't have permission to edit...");
        }

        $allowedSortFieldsStatus = ['pending', 'completed', 'cancelled'];
        $status = in_array($params['status'], $allowedSortFieldsStatus) ? $params['status'] : null;

        $allowedSortFieldsType = ['consultation', 'anamnesis', 'return'];
        $type = in_array($params['type'], $allowedSortFieldsType) ? $params['type'] : null;

        if (!$status) {
            throw new \InvalidArgumentException('Invalid status');
        }

        if (!$type) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $observation = $params['observation'] ?? null;

        $data = [
            'status'      => $status,
            'type'        => $type,
            'observation' => $observation
        ];

        if (!$this->update($id, $data)) {
            $errors = $this->errors();
            throw new \RuntimeException('Error when editing the schedule: ' . implode(', ', $errors));
        }
    }

    public function updateDelete($id, $idUser)
    {
        $this->update($id, ['id_deleted' => $idUser, 'status' => 'cancelled']);
    }
}
