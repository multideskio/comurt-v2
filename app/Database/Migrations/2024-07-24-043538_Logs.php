<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Logs extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();

        $this->forge->addField([
            
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'platformId' => [
                'type' => 'int',
                'unsigned' => true
            ],
            'idUser' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 30
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255
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


        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('platformId', 'platform', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->addForeignKey('idUser', 'users', 'id', 'NO ACTION', 'NO ACTION');

        $this->forge->createTable('logs', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('logs', true);
    }
}
