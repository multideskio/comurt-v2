<?php

declare(strict_types=1);

namespace App\Models\Tasks\V1;
use App\Models\TasksModel;

class UpdateTasks extends TasksModel
{
    public function taskUpdate(array $param, $id)
    {
        $currentUser = $this->getAuthenticatedUser();
        if (!$this->where('id', $id)->where('idUser', $currentUser['id'])->first()) {
            throw new \RuntimeException('The user does not have permission to update this task');
        }
        $data['id'] = $id;
        
        if ($param['title'] ?? null) {
            $data['title'] = $param['title'];
        }

        if ($param['description'] ?? null) {
            $data['description'] = $param['description'];
        }
        if ($param['status']) {
            $data['status'] = $this->typeStatus($param['status'] ?? 'pending') ?? "pending";;
        }

        if ($param['datetime'] ?? null) {
            $data['datetime'] = $this->validateDate($param['datetime']);
        }
        $this->save($data);
        return ['message' => 'Update success'];
    }

    //Ordena lista de tarefas
    public function taskUpdateOrder(array $param){
        //Altera linha por linha
        foreach($param as $row){
            $this->update($row['id'], ['order' => $row['order']]);
        }
        return ['message' => 'Order update success'];
    }

    private function typeStatus(?string $status): string
    {
        $allowedSortFields = ['pending', 'completed'];
        return in_array($status, $allowedSortFields) ? $status : "pending";
    }


    private function validateDate(?string $date)
    {
        if ($date) {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $date);
            if ($dateTime && $dateTime->format('Y-m-d H:i') === $date) {
                $now = new \DateTime();
                //if ($dateTime >= $now) {
                return $dateTime->format('Y-m-d H:i');
                //}
            }
        }
        return null;
    }
}
