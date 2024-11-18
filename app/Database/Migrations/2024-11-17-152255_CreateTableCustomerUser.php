<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableCustomerUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'localizacao' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'telefone' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'foto_perfil' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'area_de_atuacao' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'abordagens' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'formacao' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'experiencia' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'biografia' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'foto_documento' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'foto_cnh' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'status' => [
                'type' => 'INT',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('customer_user', true);
    }

    public function down()
    {
        $this->forge->dropTable('customer_user', true);
    }
}