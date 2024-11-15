<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Plans extends Migration
{
    public function up()
    {
        //
        $db = db_connect();
        $db->disableForeignKeyChecks();
        $this->forge->addField([
            'id' => [
                'type' => 'int',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'platformId' => [
                'type' => 'int',
                'unsigned' => true,
            ],
            'namePlan' => [
                'type' => 'VARCHAR',
                'constraint' => 55
            ],
            'idPlan' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'tokenWebhook' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'permissionUser' => [
                'type' => 'INT',
                'constraint' => 1,
                'COMMENT' => '1 = TP, 2 = TS'
            ],
            'timeSubscription' => [
                'type' => 'int',
                'constraint' => 3
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
        $this->forge->createTable('plans', true);
        $db->enableForeignKeyChecks();
    }

    public function down()
    {
        //
        $this->forge->dropTable('plans', true);
    }
}
