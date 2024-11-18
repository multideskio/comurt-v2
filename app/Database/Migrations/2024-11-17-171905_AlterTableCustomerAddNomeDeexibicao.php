<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTableCustomerAddNomeDeexibicao extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customer_user', [
            'nome_de_exibicao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('customer_user', 'nome_de_exibicao');
    }
}