<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLinksPlans extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('plans', [
            'link' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' =>  true,
                'after' => 'timeSubscription', // Coloca a coluna após 'languages'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('plans', 'link');

    }
}
