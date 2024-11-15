<?php

declare(strict_types=1);

namespace App\Models\Users\V1;

use App\Models\UsersModel;

class ProfileUpdate extends UsersModel
{


    public function updateProfile(array $params)
    {
        $decoded = $this->decodeDataTokenUser();
        $userId  = $decoded->data->id;
        $data = [
            'id' => $userId,
            'name' => $params['name'],
            'phone' => $params['phone'],
            'photo' => $params['photo'],
            'description' => $params['description'],
            'education' => $params['education'],
            'languages' => $params['languages'],
            'default_lang' => $params['lang'],
            'department' => $params['department'],
            'social_networks' => $params['social_networks'],
            'company' => $params['company'],
            'birthdate' => $params['birthdate'],
            'show_personal_chart_dashboard' => $params['show_personal_chart_dashboard'],
            'show_family_chart_dashboard' => $params['show_family_chart_dashboard'],
            'show_friends_chart_dashboard' => $params['show_friends_chart_dashboard'],
            'show_appointments_chart_dashboard' => $params['show_appointments_chart_dashboard'],
            'show_basic_info_dashboard' => $params['show_basic_info_dashboard'],
            'receive_updates_email' => $params['receive_updates_email'],
            'receive_updates_sms' => $params['receive_updates_sms'],
            'receive_updates_whatsapp' => $params['receive_updates_whatsapp'],
            'receive_scheduling_reminders' => $params['receive_scheduling_reminders'],
            'receive_cancellation_reminders' => $params['receive_cancellation_reminders']
        ];
        $this->save($data);
        return ['id' => $userId, 'message' => lang("Errors.resourceUpdated")];
    }
}
