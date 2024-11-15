<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIdAuraProtected extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('anamneses', [
            'aura_protection' => [
                'type' => 'int',
                'unsigned' => true,
                'default' => 0,
                'after' => 'aura_size'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('anamneses', 'aura_protection');
    }
}
