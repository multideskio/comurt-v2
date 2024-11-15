<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdAgendamento extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('anamneses', [
            'id_appointments' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'after' => 'id_customer'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('anamneses', 'id_appointments');
    }
}
