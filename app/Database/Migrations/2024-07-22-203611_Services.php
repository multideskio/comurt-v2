<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Services extends Migration
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
            'idUser' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],
            'id_custumer' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],
            'date' => [
                'type' => 'DATETIME',
                'null' => false
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 35,
                'null' => false
            ],
            'observation' => [
                'type' => 'text',
                'null' => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);


        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('idUser', 'users', 'id', 'NO ACTION', 'NO ACTION');
        $this->forge->addForeignKey('id_custumer', 'custumers', 'id', 'NO ACTION', 'NO ACTION');

        $this->forge->createTable('services', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('services', true);

    }
}
