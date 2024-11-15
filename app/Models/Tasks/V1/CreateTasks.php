<?php 
declare(strict_types=1);
namespace App\Models\Tasks\V1;

use App\Models\TasksModel;

class CreateTasks extends TasksModel{
    
    public function taskCreate(array $param){
        
        $currentUser = $this->getAuthenticatedUser();
        $status      = $this->typeStatus($param['status'] ?? 'pending') ?? "pending";
        $dateTime    = $this->validateDate($param['datetime']);

        $data = [
            'idUser'      => $currentUser['id'],
            'title'       => $param['title'] ?? null,
            'description' => $param['description'] ?? null,
            //'order',
            'status'       => $status,
            'datetime'     => $dateTime
        ];
        
        $id = $this->insert($data);
        return ['id' => $id, 'message' => 'created success'];
    
    }

    private function typeStatus(?string $status): string{
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