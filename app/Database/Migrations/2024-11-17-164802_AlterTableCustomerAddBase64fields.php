<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableCustomerAddBase64fields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customer_user', [
            'foto_perfil_64' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_documento_64' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_cnh_64' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'foto_perfil_64_ext' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'foto_documento_64_ext' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'foto_cnh_64_ext' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('customer_user', [
            'foto_perfil_64',
            'foto_documento_64',
            'foto_cnh_64',
            'foto_perfil_64_ext',
            'foto_documento_64_ext',
            'foto_cnh_64_ext',
        ]);
    }
}