<?php

namespace App\Database\Seeds;

use App\Models\PlansModel;
use App\Models\PlatformModel;
use App\Models\SubscriptionsModel;
use App\Models\UsersModel;
use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        //$encrypter = service('encrypter');
        $modelPlatform = new PlatformModel();

        /* The code snippet ` = ->insert([...]);` is inserting a new record
        into the `PlatformModel` database table. The data being inserted includes information such
        as the company name, sender email, base URL, sender name, SMTP host, SMTP user, and SMTP
        port for a platform. */
        $idPlatform = $modelPlatform->insert([
            'company'     => 'Plataforma',
            'senderEmail' => 'multidesk.io@gmail.com',
            'urlBase'     => 'https://comurt-frontend.vercel.app',
            'senderName'  => 'Terapeuta Teste',
            'smtpHost'    => 'smtp.gmail.com',
            'smtpUser'    => 'multidesk.io@gmail.com',
            'smtpPort'    => '587'
        ]);

        $modePlan = new PlansModel();

        $dataPlan[1] = [
            'platformId'       => $idPlatform,
            'namePlan'         => 'TERAPEUTA PROFISSIONAL',
            'idPlan'           => 'tp',
            'permissionUser'   => 1,
            'timeSubscription' => 0
        ];

        $dataPlan[2] = [
            'platformId'       => $idPlatform,
            'namePlan'         => 'TERAPEUTA DE SI',
            'idPlan'           => 'ts',
            'permissionUser'   => 2,
            'timeSubscription' => 0
        ];

        $idPlan = $modePlan->insertBatch($dataPlan);

        $modelUser = new UsersModel();
        helper('auxiliar');

        $dataUser = [
            [
                'platformId' => $idPlatform,
                'name'       => 'ADMIN',
                'email'      => 'adm@conect.app',
                'password'   => password_hash('123456', PASSWORD_BCRYPT),
                'phone'      => '+55 (62) 9 8115-4120',
                'checked'    => 1,
                'admin'      => true,
                'token'      => gera_token(),
                'magic_link' => generateMagicLink('adm@conect.app', 60*30)
            ],
            [
                'platformId' => $idPlatform,
                'name'       => 'Demo TP',
                'email'      => 'tp@conect.app',
                'password'   => password_hash('123456', PASSWORD_BCRYPT),
                'phone'      => '+55 (62) 9 8115-4120',
                'checked'    => 1,
                'admin'      => false,
                'token'      => gera_token(),
                'magic_link' => generateMagicLink('tp@conect.app', 60*30)
            ],
            [
                'platformId' => $idPlatform,
                'name'       => 'Demo TS',
                'email'      => 'ts@conect.app',
                'password'   => password_hash('123456', PASSWORD_BCRYPT),
                'phone'      => '+55 (62) 9 8115-4120',
                'checked'    => 1,
                'admin'      => false,
                'token'      => gera_token(),
                'magic_link' => generateMagicLink('ts@conect.app', 60*30)
            ]
        ];

        $modelUser->insertBatch($dataUser);

        $modelSubscription = new SubscriptionsModel();
        $dataSub = [
            [
                'idPlan' => 1,
                'idUser' => 2
            ],
            [
                'idPlan' => 2,
                'idUser' => 3
            ]
        ];

        echo "\n\nProcesso de inserção no banco de dados concluído... \n\n";

        $modelSubscription->insertBatch($dataSub);
    }
}
