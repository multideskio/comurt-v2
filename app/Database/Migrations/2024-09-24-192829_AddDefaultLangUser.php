<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultLangUser extends Migration
{
    public function up()
    {
        //
        $this->forge->addColumn('users', [
            'default_lang' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'default' => 'pt-BR',
                'after' => 'languages', // Coloca a coluna após 'languages'
            ],
        ]);
    }

    public function down()
    {
        //
        $this->forge->dropColumn('users', 'default_lang');
    }
}
