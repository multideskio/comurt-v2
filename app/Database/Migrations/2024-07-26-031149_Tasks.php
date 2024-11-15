<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Tasks extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'idUser' => [
                'type'       => 'BIGINT',
                'unsigned'   => true
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'order' => [
                'type' => 'int',
                'contraint' => 2
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'completed'],
                'default'    => 'pending',
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


        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idUser', 'users', 'id', 'NO ACTION', 'NO ACTION');

        $this->forge->createTable('tasks', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('tasks', true);
    }
}
