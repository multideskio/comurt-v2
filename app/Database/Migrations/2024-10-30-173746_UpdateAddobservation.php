<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAddobservation extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('appointments', [
            'observation' => [
                'type' => 'TEXT',
                'after' => 'type'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('appointments', 'observation');
    }
}
