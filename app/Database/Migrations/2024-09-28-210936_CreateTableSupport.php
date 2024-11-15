<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableSupport extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_user' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => false,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 90,
                'null'       => false,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => false,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'comment'    => 'Tipo de suporte? / Categoria / Setor',
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'protocol' => [
                'type'       => 'VARCHAR',
                'constraint' => 90,
                'null'       => false,
            ],
            'channel' => [
                'type'       => 'VARCHAR',
                'constraint' => 25,
                'null'       => false,
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
            ],
        ]);

        // Definindo a chave primária e a chave estrangeira
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');

        // Criando a tabela
        $this->forge->createTable('supports', true);
    }

    public function down()
    {
        // Removendo a tabela
        $this->forge->dropTable('supports', true);
    }
}
