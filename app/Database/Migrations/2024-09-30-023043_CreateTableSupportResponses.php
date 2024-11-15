<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableSupportResponses extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'support_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'responded_by' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'comment'    => 'Usuário que respondeu o chamado.',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);

        // Definindo a chave primária e as chaves estrangeiras
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('support_id', 'supports', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('responded_by', 'users', 'id', 'SET NULL', 'CASCADE');

        // Criando a tabela
        $this->forge->createTable('support_responses', true);
    }

    public function down()
    {
        // Removendo a tabela
        $this->forge->dropTable('support_responses', true);
    }
}
