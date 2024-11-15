<?php

namespace App\Database\Seeds;

use App\Models\TemplatesEmailModel;
use CodeIgniter\Database\Seeder;

class EmailsSeeder extends Seeder
{
    public function run()
    {
        //
        $created_user = file_get_contents(APPPATH . 'Views/emails/subscription.php');



        $data[] = [
            'platformId' => 1,
            'type' => 'created_user',
            'template' => $created_user,
        ];


        $modelEmail = new TemplatesEmailModel();

        $modelEmail->insertBatch($data);

        echo "Emails criados";
    }
}
