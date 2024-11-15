<?php

declare(strict_types=1);

namespace App\Models\Users\V1;

use App\Models\Appointments\V1\SearchAppointments;
use App\Models\PlansModel;
use App\Models\SubscriptionsModel;
use App\Models\UsersModel;

class MeUsers extends UsersModel
{
    

    // Retorna dados do usuário logado
    public function me()
    {
        try {
            $decoded = $this->decodeDataTokenUser();
            
            $userId  = $decoded->data->id;
            
            // Acessa a role diretamente de $decoded
            $role = $decoded->role ?? lang('Errors.roleNotSpecified');

            // Busca no banco de dados se não estiver no cache
            $user = $this->find($userId);
            if (!$user) {
                throw new \RuntimeException(lang('Errors.notFound'));
            }
            
            $serchApp = new SearchAppointments();
            
            $statistics = $serchApp->statisticsWithComparison($userId);

            return [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'photo' => $user['photo'],
                'role'  => $role,
                'plan'  => $this->namePlanUser($userId)['namePlan'] ?? null,
                'lang'  => $user['default_lang'],
                'languages'  => $user['languages'],
                'description' => $user['description'],
                'education' => $user['education'],
                'department' => $user['department'],
                'social_networks' => $user['social_networks'],
                'company' => $user['company'],
                'birthdate' => $user['birthdate'],
                'show_personal_chart_dashboard' => $user['show_personal_chart_dashboard'],
                'show_family_chart_dashboard' => $user['show_family_chart_dashboard'],
                'show_friends_chart_dashboard' => $user['show_friends_chart_dashboard'],
                'show_appointments_chart_dashboard' => $user['show_appointments_chart_dashboard'],
                'show_basic_info_dashboard' => $user['show_basic_info_dashboard'],
                'receive_updates_email' => $user['receive_updates_email'],
                'receive_updates_sms' => $user['receive_updates_sms'],
                'receive_updates_whatsapp' => $user['receive_updates_whatsapp'],
                'receive_scheduling_reminders' => $user['receive_scheduling_reminders'],
                'receive_cancellation_reminders' => $user['receive_cancellation_reminders'],
                'statistics' => $statistics
            ];
        } catch (\RuntimeException $e) {
            log_message('error', 'Erro ao obter dados do usuário: ' . $e->getMessage());
            throw new \RuntimeException($e->getMessage(), 400);
        } catch (\Exception $e) {
            log_message('error', 'Erro inesperado ao obter dados do usuário: ' . $e->getMessage());
            throw new \RuntimeException(lang('Errors.serverError'), 500);
        }
    }

    private function namePlanUser($idUser){
        $modelSubscription = new SubscriptionsModel();
        $subscription = $modelSubscription->select('idPlan')->where('idUser', $idUser)->first();
        if($subscription){
            $modelPlano = new PlansModel();
            return $modelPlano->find($subscription['idPlan']); 
        }else{
            return false;
        }
    }
}
