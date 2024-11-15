<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateAddColorBase extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('anamneses', [
            'color_base' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'after' => 'color_excess'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('anamneses', 'color_base');
    }
}
